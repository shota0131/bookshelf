<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with(['genres', 'user'])
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(10);

        return view('books.index', compact('books'));
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