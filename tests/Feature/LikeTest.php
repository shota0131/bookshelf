<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログインユーザーはレビューにいいねできる()
    {
        $user = User::factory()->create();
        $author = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $author->id,
        ]);

        $review = Review::factory()->create([
            'user_id' => $author->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reviews.like', $review));

        $response->assertRedirect();

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /** @test */
    public function ログインユーザーはいいねを解除できる()
    {
        $user = User::factory()->create();
        $author = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $author->id,
        ]);

        $review = Review::factory()->create([
            'user_id' => $author->id,
            'book_id' => $book->id,
        ]);

        $user->likedReviews()->attach($review->id);

        $response = $this->actingAs($user)
            ->post(route('reviews.like', $review));

        $response->assertRedirect();

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /** @test */
    public function 未ログインユーザーはいいねできない()
    {
        $author = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $author->id,
        ]);

        $review = Review::factory()->create([
            'user_id' => $author->id,
            'book_id' => $book->id,
        ]);

        $response = $this->post(route('reviews.like', $review));

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $review->id,
        ]);
    }

    /** @test */
    public function 同じレビューに重複していいねできない()
    {
        $user = User::factory()->create();
        $author = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $author->id,
        ]);

        $review = Review::factory()->create([
            'user_id' => $author->id,
            'book_id' => $book->id,
        ]);

        $user->likedReviews()->attach($review->id);

        $this->actingAs($user)
            ->post(route('reviews.like', $review));

        $this->assertDatabaseCount('review_likes', 0);
    }
}