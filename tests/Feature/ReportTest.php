<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ゲストは読書レポートを閲覧できない(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function ログインユーザーは読書レポートを閲覧できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('reports.index'));

        $response->assertOk();
    }

    /** @test */
    public function 読書レポートはログインユーザーのレビューを対象にする(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book2->id,
            'rating' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('reports.index'));

        $response->assertOk();

        // レポートの集計値をビューに渡している場合は、
        // 実際のビュー変数名に合わせて件数・平均評価を検証する。
        $response->assertViewHas('totalReviews', 1);
        $response->assertViewHas('averageRating', 5);
    }

    /** @test */
    public function レビューがないユーザーも読書レポートを閲覧できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('reports.index'));

        $response->assertOk();
    }
}