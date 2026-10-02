<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ジャンルは複数の書籍と関連付けられる(): void
    {
        $genre = Genre::factory()->create();

        $books = Book::factory()->count(2)->create();

        $genre->books()->attach($books->pluck('id')->all());

        $this->assertCount(2, $genre->books()->get());

        $this->assertTrue(
            $genre->books()->whereKey($books->first()->id)->exists()
        );
    }

    /** @test */
    public function 書籍に複数のジャンルを関連付けられる(): void
    {
        $book = Book::factory()->create();

        $genres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id')->all());

        $this->assertCount(2, $book->genres()->get());

        $this->assertTrue(
            $book->genres()->whereKey($genres->first()->id)->exists()
        );
    }
}