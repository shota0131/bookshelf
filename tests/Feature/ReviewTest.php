<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログインユーザーはレビューを投稿できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user);

        $response = $this->post(
            route('reviews.store', $book),
            [
                'rating' => 5,
                'comment' => 'とても面白い本でした。',
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても面白い本でした。',
        ]);
    }

    /** @test */
    public function ゲストはレビューを投稿できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(
            route('reviews.store', $book),
            [
                'rating' => 5,
                'comment' => 'レビュー',
            ]
        );

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 評価が上限を超える場合は投稿できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user);

        $response = $this->post(
            route('reviews.store', $book),
            [
                'rating' => 6,
                'comment' => 'レビュー',
            ]
        );

        $response->assertSessionHasErrors('rating');
    }

    /** @test */
    public function 評価が下限未満の場合は投稿できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user);

        $response = $this->post(
            route('reviews.store', $book),
            [
                'rating' => 0,
                'comment' => 'レビュー',
            ]
        );

        $response->assertSessionHasErrors('rating');
    }

    /** @test */
    public function コメントが1000文字を超える場合は投稿できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user);

        $response = $this->post(
            route('reviews.store', $book),
            [
                'rating' => 4,
                'comment' => str_repeat('あ', 1001),
            ]
        );

        $response->assertSessionHasErrors('comment');
    }

    /** @test */
    public function 投稿者はレビュー編集画面を閲覧できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(
            route('reviews.edit', $review)
        );

        $response->assertOk();
    }

    /** @test */
    public function 他のユーザーはレビュー編集画面を閲覧できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($otherUser);

        $response = $this->get(
            route('reviews.edit', $review)
        );

        $response->assertForbidden();
    }

    /** @test */
    public function 投稿者はレビューを更新できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '更新前のコメント',
        ]);

        $this->actingAs($user);

        $response = $this->put(
            route('reviews.update', $review),
            [
                'rating' => 5,
                'comment' => '更新後のコメント',
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => '更新後のコメント',
        ]);
    }

    /** @test */
    public function 他のユーザーはレビューを更新できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '元のコメント',
        ]);

        $this->actingAs($otherUser);

        $response = $this->put(
            route('reviews.update', $review),
            [
                'rating' => 5,
                'comment' => '不正な更新',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '元のコメント',
        ]);
    }

    /** @test */
    public function 投稿者はレビューを削除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($user);

        $response = $this->delete(
            route('reviews.destroy', $review)
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    /** @test */
    public function 他のユーザーはレビューを削除できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($otherUser);

        $response = $this->delete(
            route('reviews.destroy', $review)
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }
}