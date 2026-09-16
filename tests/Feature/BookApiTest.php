<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍一覧APIはJSONを返す(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    /** @test */
    public function 書籍詳細APIはJSONを返す(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson(
            "/api/v1/books/{$book->id}"
        );

        $response->assertStatus(200);
    }

    /** @test */
    public function 存在しない書籍IDの場合はエラーを返す(): void
    {
        $response = $this->getJson('/api/v1/books/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function APIから書籍を登録できる(): void
    {
        $genre = Genre::factory()->create();
        $user = User::factory()->create();

        $data = [
            'title' => 'APIテスト書籍',
            'author' => 'APIテスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
            'user_id' => $user->id,
            'genres' => [$genre->id],
        ];

        Sanctum::actingAs($user);

        $response = $this->postJson(
            '/api/v1/books',
            $data
        );

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', [
            'title' => 'APIテスト書籍',
        ]);
    }

    /** @test */
    public function API書籍登録でバリデーションエラーの場合はエラーを返す(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'title',
            'author',
            'isbn',
            'published_date',
            'user_id',
            'genres',
        ]);
    }

    /** @test */
    public function 未認証ユーザーはAPIから書籍を登録できず401になる(): void
    {
        $genre = Genre::factory()->create();
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/books', [
            'title' => 'APIテスト書籍',
            'author' => 'APIテスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
            'user_id' => $user->id,
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function 未認証ユーザーはAPIから書籍を更新できず401になる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->putJson(
            "/api/v1/books/{$book->id}",
            [
                'title' => '更新後タイトル',
                'author' => $book->author,
                'isbn' => $book->isbn,
                'published_date' => $book->published_date,
                'description' => $book->description,
                'image_url' => $book->image_url,
                'user_id' => $user->id,
                'genres' => $book->genres->pluck('id')->toArray(),
            ]
        );

        $response->assertStatus(401);
    }

    /** @test */
    public function 未認証ユーザーはAPIから書籍を削除できず401になる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson(
            "/api/v1/books/{$book->id}"
        );

        $response->assertStatus(401);
    }

    /** @test */
    public function 書籍の登録者本人はAPIから書籍を更新できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(
            "/api/v1/books/{$book->id}",
            [
                'title' => '更新後タイトル',
                'author' => $book->author,
                'isbn' => $book->isbn,
                'published_date' => $book->published_date,
                'description' => $book->description,
                'image_url' => $book->image_url,
                'user_id' => $user->id,
                'genres' => [$genre->id],
            ]
        );

        $response->assertSuccessful();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
        ]);
    }

    /** @test */
    public function 他ユーザーはAPIから他人の書籍を更新できず403になる(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $genre = Genre::factory()->create();

        Sanctum::actingAs($otherUser);

        $response = $this->putJson(
            "/api/v1/books/{$book->id}",
            [
                'title' => '他ユーザーによる更新',
                'author' => $book->author,
                'isbn' => $book->isbn,
                'published_date' => $book->published_date,
                'description' => $book->description,
                'image_url' => $book->image_url,
                'user_id' => $owner->id,
                'genres' => [$genre->id],
            ]
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function 書籍の登録者本人はAPIから書籍を削除できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            "/api/v1/books/{$book->id}"
        );

        $response->assertSuccessful();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    /** @test */
    public function 他ユーザーはAPIから他人の書籍を削除できず403になる(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson(
            "/api/v1/books/{$book->id}"
        );

        $response->assertStatus(403);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}