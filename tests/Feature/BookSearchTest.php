<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function キーワードで書籍を検索できる()
    {
        $user = User::factory()->create();

        $matchedBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Laravel入門',
        ]);

        Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'PHP入門',
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => 'Laravel',
        ]));

        $response->assertOk();
        $response->assertSeeText('Laravel入門');
        $response->assertDontSeeText('PHP入門');
    }

    /** @test */
    public function ジャンルで書籍を絞り込める()
    {
        $user = User::factory()->create();

        $targetGenre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $otherGenre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $targetBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '小説の本',
        ]);

        $otherBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '技術書の本',
        ]);

        $targetBook->genres()->attach($targetGenre->id);
        $otherBook->genres()->attach($otherGenre->id);

        $response = $this->get(route('books.index', [
            'genre_id' => $targetGenre->id,
        ]));

        $response->assertOk();
        $response->assertSeeText('小説の本');
        $response->assertDontSeeText('技術書の本');
    }

    /** @test */
    public function キーワードとジャンルを組み合わせて検索できる()
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $matchedBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Laravel実践入門',
        ]);

        $wrongKeywordBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'PHP実践入門',
        ]);

        $wrongGenreBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Laravel小説',
        ]);

        $matchedBook->genres()->attach($genre->id);
        $wrongKeywordBook->genres()->attach($genre->id);

        $response = $this->get(route('books.index', [
            'keyword' => 'Laravel',
            'genre_id' => $genre->id,
        ]));

        $response->assertOk();
        $response->assertSeeText('Laravel実践入門');
        $response->assertDontSeeText('PHP実践入門');
        $response->assertDontSeeText('Laravel小説');
    }

    /** @test */
    public function 検索結果がない場合も一覧画面を表示できる()
    {
        $response = $this->get(route('books.index', [
            'keyword' => '存在しない書籍名',
        ]));

        $response->assertOk();
    }
}