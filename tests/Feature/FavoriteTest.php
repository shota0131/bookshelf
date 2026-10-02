<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログインユーザーは書籍をお気に入り登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user);

        $response = $this->post(
            route('favorites.toggle', $book)
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('book_user', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function お気に入り登録済みの書籍を再度操作すると解除される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $this->actingAs($user);

        $response = $this->post(
            route('favorites.toggle', $book)
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('book_user', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function ゲストがお気に入り操作をするとログイン画面へ遷移する(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(
            route('favorites.toggle', $book)
        );

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function ゲストはお気に入り一覧を閲覧できない(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function お気に入り一覧には自分の登録した書籍だけが表示される(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownBook = Book::factory()->create([
            'title' => '自分のお気に入り書籍',
        ]);

        $otherBook = Book::factory()->create([
            'title' => '他人のお気に入り書籍',
        ]);

        $user->favoriteBooks()->attach($ownBook->id);
        $otherUser->favoriteBooks()->attach($otherBook->id);

        $this->actingAs($user);

        $response = $this->get(route('favorites.index'));

        $response->assertOk();
        $response->assertSee('自分のお気に入り書籍');
        $response->assertDontSee('他人のお気に入り書籍');
    }

    /** @test */
    public function お気に入り一覧は10件ずつ表示される(): void
    {
        $user = User::factory()->create();

        $books = Book::factory()->count(11)->create();
        $user->favoriteBooks()->attach($books->pluck('id')->all());

        $this->actingAs($user);

        $response = $this->get(route('favorites.index'));

        $response->assertOk();

        $response->assertViewHas('books', function ($paginator) {
            return $paginator->perPage() === 10
                && $paginator->total() === 11;
        });
    }
}