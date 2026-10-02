<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ReminderBatchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 期限が近い読書計画にリマインダー通知を作成する()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDay(),
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        Artisan::call('reading-plans:send-reminders');

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
        ]);
    }

    /** @test */
    public function 期限が遠い読書計画にはリマインダー通知を作成しない()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDays(30),
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        Artisan::call('reading-plans:send-reminders');

        $this->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function 完了済みの読書計画にはリマインダー通知を作成しない()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDay(),
            'status' => ReadingPlanStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        Artisan::call('reading-plans:send-reminders');

        $this->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function バッチを複数回実行しても通知が重複しない()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => today()->addDay(),
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        Artisan::call('reading-plans:send-reminders');
        Artisan::call('reading-plans:send-reminders');

        $this->assertDatabaseCount('notifications', 1);
    }
}