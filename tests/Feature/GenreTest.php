<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ゲストはジャンル一覧を閲覧できない(): void
    {
        $response = $this->get(route('genres.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function ログインユーザーはジャンル一覧を閲覧できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '小説']);

        $this->actingAs($user);

        $response = $this->get(route('genres.index'));

        $response->assertOk();
        $response->assertSee('小説');
    }

    /** @test */
    public function ジャンル一覧に各ジャンルの書籍数が表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '小説']);

        $book1 = Book::factory()->create(['user_id' => $user->id]);
        $book2 = Book::factory()->create(['user_id' => $user->id]);

        $book1->genres()->attach($genre->id);
        $book2->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->get(route('genres.index'));

        $response->assertOk();
        $response->assertSee('小説');
        $response->assertSee('2');
    }

    /** @test */
    public function ゲストはジャンル詳細を閲覧できない(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.show', $genre));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function ジャンル詳細に紐づく書籍が表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => 'ミステリー']);
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テストミステリー',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->get(route('genres.show', $genre));

        $response->assertOk();
        $response->assertSee('ミステリー');
        $response->assertSee('テストミステリー');
    }

    /** @test */
    public function ジャンル詳細で書籍がない場合は空表示になる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '未分類']);

        $this->actingAs($user);

        $response = $this->get(route('genres.show', $genre));

        $response->assertOk();
        $response->assertSee('このジャンルの書籍はまだ登録されていません。');
    }

    /** @test */
    public function ジャンル詳細の書籍は10件ずつ表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        for ($i = 1; $i <= 11; $i++) {
            $book = Book::factory()->create([
                'user_id' => $user->id,
                'title' => "ジャンル書籍{$i}",
            ]);

            $book->genres()->attach($genre->id);
        }

        $this->actingAs($user);

        $response = $this->get(route('genres.show', $genre));

        $response->assertOk();

        $response->assertSee('ジャンル書籍1');
        $response->assertSee('ジャンル書籍10');
        $response->assertDontSee('ジャンル書籍11');

        $response->assertViewHas('books', function ($books) {
            return $books->perPage() === 10
                && $books->total() === 11;
        });
    }

    /** @test */
    public function ゲストはジャンル登録画面を閲覧できない(): void
    {
        $response = $this->get(route('genres.create'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function ログインユーザーはジャンル登録画面を閲覧できる(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('genres.create'));

        $response->assertOk();
    }

    /** @test */
    public function ログインユーザーはジャンルを登録できる(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('genres.store'), [
            'name' => '歴史',
        ]);

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseHas('genres', [
            'name' => '歴史',
        ]);
    }

    /** @test */
    public function ジャンル名が未入力の場合は登録できない(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('genres.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function ゲストはジャンル編集画面を閲覧できない(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.edit', $genre));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function ログインユーザーはジャンル編集画面を閲覧できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('genres.edit', $genre));

        $response->assertOk();
    }

    /** @test */
    public function ログインユーザーはジャンルを更新できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => '旧ジャンル',
        ]);

        $this->actingAs($user);

        $response = $this->put(route('genres.update', $genre), [
            'name' => '新ジャンル',
        ]);

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '新ジャンル',
        ]);
    }

    /** @test */
    public function 空のジャンル名では更新できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => '既存ジャンル',
        ]);

        $this->actingAs($user);

        $response = $this->put(route('genres.update', $genre), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '既存ジャンル',
        ]);
    }

    /** @test */
    public function 書籍が紐づいていないジャンルは削除できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    /** @test */
    public function 書籍が紐づいているジャンルは削除できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->delete(route('genres.destroy', $genre));

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }
}