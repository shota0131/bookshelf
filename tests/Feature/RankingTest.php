<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 平均評価の高い順にランキングが表示される(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $high = Book::factory()->create([
            'title' => '高評価の書籍',
        ]);

        $low = Book::factory()->create([
            'title' => '低評価の書籍',
        ]);

        Review::factory()->create([
            'book_id' => $high->id,
            'user_id' => $user1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $high->id,
            'user_id' => $user2->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'book_id' => $low->id,
            'user_id' => $user1->id,
            'rating' => 2,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertSeeInOrder([
            '高評価の書籍',
            '低評価の書籍',
        ]);
    }

    /** @test */
    public function ランキングにはレビューのある書籍だけが表示される(): void
    {
        $user = User::factory()->create();

        $reviewedBook = Book::factory()->create([
            'title' => 'レビューあり書籍',
        ]);

        $unreviewedBook = Book::factory()->create([
            'title' => 'レビューなし書籍',
        ]);

        Review::factory()->create([
            'book_id' => $reviewedBook->id,
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertSee('レビューあり書籍');
        $response->assertDontSee('レビューなし書籍');
    }

    /** @test */
    public function ランキングは平均評価順で上位10件まで表示される(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 11; $i++) {
            $book = Book::factory()->create([
                'title' => "ランキング書籍{$i}",
            ]);

            Review::factory()->create([
                'book_id' => $book->id,
                'user_id' => $user->id,
                'rating' => $i <= 5 ? 5 : 1,
            ]);
        }

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            return $rankedBooks->count() === 10;
        });
    }
}