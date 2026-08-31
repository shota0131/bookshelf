<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::with(['genres', 'user'])
            ->withAvg('reviews', 'rating')

            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('title', 'like', "%{$keyword}%")
                        ->orWhere('author', 'like', "%{$keyword}%");
                });
            })

            ->when($request->genre_id, function ($query, $genreId) {
                $query->whereHas('genres', function ($query) use ($genreId) {
                    $query->where('genres.id', $genreId);
                });
            })

            ->when($request->sort === 'oldest', function ($query) {
                $query->oldest();
            })
            ->when($request->sort === 'title', function ($query) {
                $query->orderBy('title');
            })
            ->when($request->sort === 'rating', function ($query) {
                $query->orderByDesc('reviews_avg_rating');
            })
            ->when(!$request->sort, function ($query) {
                $query->latest();
            })

            ->paginate(10)
            ->withQueryString();

        $genres = Genre::orderBy('name')->get();

        return view('books.index', compact('books', 'genres'));
    }

    public function csv()
    {
        $books = Book::with('genres')
            ->orderBy('id')
            ->get();

        $filename = 'books.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($books) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'タイトル',
                '著者',
                'ISBN',
                '出版日',
                'ジャンル',
            ]);

            foreach ($books as $book) {
                fputcsv($handle, [
                    $book->id,
                    $book->title,
                    $book->author,
                    $book->isbn,
                    $book->published_date,
                    $book->genres->pluck('name')->implode(', '),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function isbnSearch(Request $request)
    {
        $isbn = $request->input('isbn');

        if (!$isbn || !preg_match('/^\d{13}$/', $isbn)) {
            return response()->json([
                'message' => 'ISBNは13桁の数字で入力してください。',
            ], 422);
        }

        $url = 'https://www.googleapis.com/books/v1/volumes?q=isbn:' . $isbn;

        $response = Http::get($url);

        if (!$response->successful()) {
            return response()->json([
                'message' => '書籍情報の取得に失敗しました。',
            ], 500);
        }

        $data = $response->json();

        if (empty($data['items'])) {
            return response()->json([
                'message' => '該当する書籍が見つかりませんでした。',
            ], 404);
        }

        $book = $data['items'][0]['volumeInfo'] ?? [];

        return response()->json([
            'title' => $book['title'] ?? '',
            'author' => isset($book['authors'])
                ? implode(', ', $book['authors'])
                : '',
            'isbn' => $isbn,
            'published_date' => $book['publishedDate'] ?? '',
            'description' => isset($book['description'])
                ? strip_tags($book['description'])
                : '',
            'image_url' => isset($book['imageLinks']['thumbnail'])
                ? str_replace('http://', 'https://', $book['imageLinks']['thumbnail'])
                : '',
        ]);
    }

    public function show(Book $book)
    {
        $book->load([
            'genres',
            'user',
            'reviews.user',
        ]);

        return view('books.show', compact('book'));
    }

    public function create()
    {
        $genres = Genre::orderBy('name')->get();

        return view('books.create', compact('genres'));
    }

    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();

        $genreIds = $validated['genre_ids'];
        unset($validated['genre_ids']);

        $book = Book::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        $book->genres()->sync($genreIds);

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を登録しました。');
    }

    public function edit(Book $book)
    {
        $genres = Genre::orderBy('name')->get();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $validated = $request->validated();

        $genreIds = $validated['genre_ids'];
        unset($validated['genre_ids']);

        $book->update($validated);

        $book->genres()->sync($genreIds);

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

    public function destroy(Book $book)
    {
        $book->genres()->detach();

        $book->reviews()->delete();

        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を削除しました。');
    }
}