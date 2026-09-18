<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ゲストは書籍一覧を閲覧できる(): void
    {
        $response = $this->get(route('books.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function ゲストは書籍詳細を閲覧できる(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('books.show', $book));

        $response->assertStatus(200);
    }

    /** @test */
    public function ゲストが書籍登録画面にアクセスするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function ゲストが書籍編集画面にアクセスするとログイン画面へリダイレクトされる(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('books.edit', $book));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function ログインユーザーは書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genre = \App\Models\Genre::factory()->create();

        $this->actingAs($user);

        $data = [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2026-01-01',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.jpg',
            'genre_ids' => [$genre->id],
        ];

        $response = $this->post(route('books.store'), $data);

        $response->assertRedirect();

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
        ]);
    }

    /** @test */
    public function 書籍登録で必須項目が未入力の場合はバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('books.store'), []);

        $response->assertSessionHasErrors([
            'title',
            'author',
            'isbn',
            'published_date',
            'genre_ids',
        ]);
    }

    /** @test */
    public function ISBNは13桁でなければ登録できない(): void
    {
        $user = User::factory()->create();
        $genre = \App\Models\Genre::factory()->create();

        $this->actingAs($user);

        $data = [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '123',
            'published_date' => '2026-01-01',
            'description' => '説明',
            'image_url' => null,
            'genre_ids' => [$genre->id],
        ];

        $response = $this->post(route('books.store'), $data);

        $response->assertSessionHasErrors('isbn');
    }

    /** @test */
    public function ISBNが重複している場合は登録できない(): void
    {
        $user = User::factory()->create();
        $genre = \App\Models\Genre::factory()->create();

        Book::factory()->create([
            'isbn' => '9784101010014',
        ]);

        $this->actingAs($user);

        $data = [
            'title' => '別の書籍',
            'author' => '別の著者',
            'isbn' => '9784101010014',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
            'genre_ids' => [$genre->id],
        ];

        $response = $this->post(route('books.store'), $data);

        $response->assertSessionHasErrors('isbn');
    }

    /** @test */
    public function 書籍の登録者は書籍を編集できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('books.edit', $book));

        $response->assertStatus(200);
    }

    /** @test */
    public function 書籍の登録者は書籍を更新できる(): void
    {
        $user = User::factory()->create();
        $genre = \App\Models\Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '9784101010014',
        ]);

        $this->actingAs($user);

        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '9784101010014',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
            'genre_ids' => [$genre->id],
        ];

        $response = $this->put(
            route('books.update', $book),
            $data
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
        ]);
    }

    /** @test */
    public function 書籍の登録者は書籍を削除できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->delete(
            route('books.destroy', $book)
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    /** @test */
    public function 書籍削除時に関連データも適切に処理される(): void
    {
        $user = User::factory()->create();
        $reviewUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $genre = \App\Models\Genre::factory()->create();

        // 書籍とジャンルを紐づける
        $book->genres()->attach($genre->id);

        // レビューを作成
        $review = \App\Models\Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $reviewUser->id,
        ]);

        // お気に入りを作成
        $book->favoritedByUsers()->attach($reviewUser->id);

        // 書籍を削除
        $this->actingAs($user);

        $response = $this->delete(
            route('books.destroy', $book)
        );

        $response->assertRedirect();

        // 書籍が削除されている
        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        // レビューが削除されている
        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);

        // ジャンルとのpivotが削除されている
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }
}