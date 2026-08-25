<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookIndexRequest;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BookIndexRequest $request)
    {
        $books = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
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
            ->latest()
            ->paginate($request->per_page ?? 10);

        return BookResource::collection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request): BookDetailResource
    {
        $validated = $request->validated();

        $genreIds = $validated['genre_ids'];
        unset($validated['genre_ids']);

        $book = Book::create($validated);

        $book->genres()->sync($genreIds);

        $book->load([
            'user',
            'genres',
            'reviews.user',
        ]);

        return new BookDetailResource($book);
    }


    /**
     * Display the specified resource.
     */
    public function show(Book $book): BookDetailResource
    {
        $book->load([
            'user',
            'genres',
            'reviews.user',
        ]);

        return new BookDetailResource($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateBookRequest $request,
        Book $book
    ): BookDetailResource {
        $validated = $request->validated();

        $genreIds = $validated['genre_ids'];
        unset($validated['genre_ids']);

        $book->update($validated);

        $book->genres()->sync($genreIds);

        $book->load([
            'user',
            'genres',
            'reviews.user',
        ]);

        return new BookDetailResource($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book): JsonResponse
    {
        $book->genres()->detach();

        $book->reviews()->delete();

        $book->delete();

        return response()->json([
            'message' => '書籍を削除しました。',
        ], 200);
    }
}
