<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
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
}
