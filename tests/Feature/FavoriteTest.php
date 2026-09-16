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
    public function お気に入り登録と解除を切り替えられる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user);

        $response = $this->post(
            route('favorites.toggle', $book)
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->post(
            route('favorites.toggle', $book)
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function ゲストがお気に入り操作するとログイン画面へリダイレクトされる(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(
            route('favorites.toggle', $book)
        );

        $response->assertRedirect(route('login'));
    }
}