<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSortTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 新着順で書籍が表示される()
    {
        $user = User::factory()->create();

        $oldBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '古い書籍',
            'created_at' => now()->subDays(2),
        ]);

        $newBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '新しい書籍',
            'created_at' => now(),
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'newest',
        ]));

        $response->assertOk();

        $response->assertSeeInOrder([
            '新しい書籍',
            '古い書籍',
        ]);
    }

    /** @test */
    public function 古い順で書籍が表示される()
    {
        $user = User::factory()->create();

        Book::factory()->create([
            'user_id' => $user->id,
            'title' => '古い書籍',
            'created_at' => now()->subDays(2),
        ]);

        Book::factory()->create([
            'user_id' => $user->id,
            'title' => '新しい書籍',
            'created_at' => now(),
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'oldest',
        ]));

        $response->assertOk();

        $response->assertSeeInOrder([
            '古い書籍',
            '新しい書籍',
        ]);
    }

    /** @test */
    public function 検索条件とソート条件を併用できる()
    {
        $user = User::factory()->create();

        Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Laravel旧版',
            'created_at' => now()->subDays(2),
        ]);

        Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Laravel新版',
            'created_at' => now(),
        ]);

        Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'PHP入門',
            'created_at' => now()->subDay(),
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => 'Laravel',
            'sort' => 'newest',
        ]));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Laravel新版',
            'Laravel旧版',
        ]);
        $response->assertDontSeeText('PHP入門');
    }
}