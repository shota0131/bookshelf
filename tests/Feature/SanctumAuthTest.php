<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SanctumAuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証では認証必須APIにアクセスできない()
    {
        $response = $this->getJson('/api/books');

        $response->assertUnauthorized();
    }

    /** @test */
    public function Sanctum認証済みユーザーはAPIにアクセスできる()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/books');

        $response->assertOk();
    }

    /** @test */
    public function Sanctum認証済みユーザーは書籍を登録できる()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/books', [
            'title' => 'APIテスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'description' => 'APIから登録した書籍',
            'user_id' => $user->id,
            'genres' => [],
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('books', [
            'title' => 'APIテスト書籍',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function Sanctum認証済みでも不正なデータは登録できない()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/books', [
            'title' => '',
            'author' => '',
            'isbn' => 'invalid',
            'published_date' => 'invalid-date',
            'user_id' => $user->id,
            'genres' => [],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'title',
            'author',
            'isbn',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => 'invalid',
        ]);
    }

    /** @test */
    public function Sanctum認証済みユーザーはログインユーザーの情報で操作できる()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('id', $user->id);
    }
}