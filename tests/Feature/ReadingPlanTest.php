<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 読書計画を作成できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user);

        $response = $this->post(
            route('reading-plans.store'),
            [
                'book_id' => $book->id,
                'target_date' => now()->addDays(3)->format('Y-m-d'),
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

        /** @test */
    public function 読書計画の期限は今日より前にできない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user);

        $response = $this->post(
            route('reading-plans.store'),
            [
                'book_id' => $book->id,
                'target_date' => now()->subDay()->format('Y-m-d'),
            ]
        );

        $response->assertSessionHasErrors('target_date');
    }

    /** @test */
    public function 同じユーザーが同じ書籍のin_progress計画を重複登録できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        \App\Models\ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'in_progress',
        ]);

        $this->actingAs($user);

        $response = $this->post(
            route('reading-plans.store'),
            [
                'book_id' => $book->id,
                'target_date' => now()->addDays(3)->format('Y-m-d'),
            ]
        );

        $response->assertSessionHasErrors('book_id');
    }

    /** @test */
    public function 同じ書籍でもcompletedなら新しい読書計画を登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        \App\Models\ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'completed',
        ]);

        $this->actingAs($user);

        $response = $this->post(
            route('reading-plans.store'),
            [
                'book_id' => $book->id,
                'target_date' => now()->addDays(3)->format('Y-m-d'),
            ]
        );

        $response->assertSessionDoesntHaveErrors();
    }
}