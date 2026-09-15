<?php

namespace Tests\Feature;

use App\Models\Book;
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

        $data = [
            'rating' => 5,
            'comment' => 'とても面白い本でした。',
        ];

        $response = $this->post(
            route('reviews.store', $book),
            $data
        );

        $response->assertRedirect(
            route('books.show', $book)
        );

        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'とても面白い本でした。',
        ]);
    }

        /** @test */
    public function レビュー評価は1から5の範囲である(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user);

        $response = $this->post(
            route('reviews.store', $book),
            [
                'rating' => 6,
                'comment' => 'コメント',
            ]
        );

        $response->assertSessionHasErrors('rating');
    }
}