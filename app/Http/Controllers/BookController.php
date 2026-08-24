<?php

namespace App\Http\Controllers;

use App\Models\Book;

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

    public function show($book)
    {
        return view('books.show');
    }

    public function create()
    {
        return view('books.create');
    }

    public function edit($book)
    {
        return view('books.edit');
    }
}
