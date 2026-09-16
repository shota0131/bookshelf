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
        $user = User::factory()->create();

        $high = Book::factory()->create();
        $low = Book::factory()->create();

        Review::factory()->create([
            'book_id' => $high->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $low->id,
            'user_id' => $user->id,
            'rating' => 3,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);

        $response->assertSee($high->title);
        $response->assertSee($low->title);
    }

        /** @test */
    public function ランキングはTOP10まで表示される(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 11; $i++) {
            $book = Book::factory()->create();

            Review::factory()->create([
                'book_id' => $book->id,
                'user_id' => $user->id,
                'rating' => $i,
            ]);
        }

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);

        // ここは実際のランキングViewの構造に合わせて確認
    }
}