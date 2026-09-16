<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ISBNから書籍情報を取得できる(): void
    {
        Http::fake([
            '*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト書籍',
                            'authors' => ['テスト著者'],
                            'publishedDate' => '2026-01-01',
                            'description' => 'テスト説明',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/image.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson(
            route('books.isbn-search', [
                'isbn' => '9784101010014',
            ])
        );

        $response->assertStatus(200);

        $response->assertJson([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);
    }

    /** @test */
    public function ISBN検索で外部APIがエラーの場合はエラーになる(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $response = $this->getJson(
            route('books.isbn-search', [
                'isbn' => '9784101010014',
            ])
        );

        $response->assertStatus(500);
    }
}