<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AutoExpireBatchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 期限切れの読書計画を失効状態に変更する()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->subDay(),
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        Artisan::call('reading-plans:auto-expire');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::EXPIRED->value,
        ]);
    }

    /** @test */
    public function 期限内の読書計画は失効しない()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(3),
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        Artisan::call('reading-plans:auto-expire');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::IN_PROGRESS->value,
        ]);
    }

    /** @test */
    public function 完了済みの読書計画は失効しない()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->subDay(),
            'status' => ReadingPlanStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        Artisan::call('reading-plans:auto-expire');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::COMPLETED->value,
        ]);
    }

    /** @test */
    public function 失効済みの読書計画は再処理しても状態が変わらない()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->subDays(2),
            'status' => ReadingPlanStatus::EXPIRED,
        ]);

        Artisan::call('reading-plans:auto-expire');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::EXPIRED->value,
        ]);
    }
}