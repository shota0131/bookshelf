<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Book;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Book $book)
    {
        $book->reviews()->create([
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()
            ->route('books.show', $book)
            ->with('success', 'レビューを投稿しました。');
    }
}