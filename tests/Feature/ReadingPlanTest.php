<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ゲストは読書計画一覧にアクセスするとログイン画面へ遷移する(): void
    {
        $response = $this->get(route('reading-plans.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function ログインユーザーは読書計画を作成できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(3)->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function 読書計画の作成画面に書籍一覧が表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'title' => '選択対象の書籍',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('reading-plans.create'));

        $response->assertOk();
        $response->assertSee('選択対象の書籍');
    }

    /** @test */
    public function 過去の日付では読書計画を作成できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('target_date');

        $this->assertDatabaseMissing('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function 存在しない書籍では読書計画を作成できない(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('reading-plans.store'), [
            'book_id' => 999999,
            'target_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('book_id');
    }

    /** @test */
    public function 同じユーザーは同じ書籍の進行中計画を重複登録できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('book_id');

        $this->assertDatabaseCount('reading_plans', 1);
    }

    /** @test */
    public function 完了済み計画がある書籍は新しい計画を作成できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::COMPLETED,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseCount('reading_plans', 2);
    }

    /** @test */
    public function 読書計画一覧にはログインユーザー自身の計画だけが表示される(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownBook = Book::factory()->create([
            'title' => '自分の計画の書籍',
        ]);

        $otherBook = Book::factory()->create([
            'title' => '他人の計画の書籍',
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $ownBook->id,
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $otherBook->id,
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('reading-plans.index'));

        $response->assertOk();
        $response->assertSee('自分の計画の書籍');
        $response->assertDontSee('他人の計画の書籍');
    }

    /** @test */
    public function 読書計画一覧で状態を絞り込める(): void
    {
        $user = User::factory()->create();

        $inProgressBook = Book::factory()->create([
            'title' => '進行中の書籍',
        ]);

        $completedBook = Book::factory()->create([
            'title' => '完了済みの書籍',
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $inProgressBook->id,
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $completedBook->id,
            'status' => ReadingPlanStatus::COMPLETED,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('reading-plans.index', [
            'status' => 'in_progress',
        ]));

        $response->assertOk();
        $response->assertSee('進行中の書籍');
        $response->assertDontSee('完了済みの書籍');
    }

    /** @test */
    public function 所有者は読書計画の編集画面を閲覧できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('reading-plans.edit', $plan));

        $response->assertOk();
    }

    /** @test */
    public function 他ユーザーは読書計画の編集画面を閲覧できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($otherUser);

        $response = $this->get(route('reading-plans.edit', $plan));

        $response->assertForbidden();
    }

    /** @test */
    public function 所有者は読書計画の期日を更新できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $newDate = now()->addDays(10)->format('Y-m-d');

        $this->actingAs($user);

        $response = $this->put(route('reading-plans.update', $plan), [
            'book_id' => $book->id,
            'target_date' => $newDate,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'target_date' => $newDate,
        ]);
    }

    /** @test */
    public function 他ユーザーは読書計画を更新できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
        ]);

        $originalDate = $plan->target_date->format('Y-m-d');

        $this->actingAs($otherUser);

        $response = $this->put(route('reading-plans.update', $plan), [
            'book_id' => $book->id,
            'target_date' => now()->addDays(20)->format('Y-m-d'),
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'target_date' => $originalDate,
        ]);
    }

    /** @test */
    public function 所有者は読書計画を完了できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        $this->actingAs($user);

        $response = $this->post(
            route('reading-plans.complete', $plan)
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::COMPLETED->value,
        ]);
    }

    /** @test */
    public function 所有者は読書計画を削除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($user);

        $response = $this->delete(
            route('reading-plans.destroy', $plan)
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $plan->id,
        ]);
    }

    /** @test */
    public function 他ユーザーは読書計画を削除できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($otherUser);

        $response = $this->delete(
            route('reading-plans.destroy', $plan)
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
        ]);
    }
}