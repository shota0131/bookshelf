<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Book;

class FavoriteController extends Controller
{
    public function index()
    {
        $books = Auth::user()
            ->favoriteBooks()
            ->with(['genres', 'user'])
            ->withAvg('reviews', 'rating')
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book)
    {
        $user = auth()->user();

        if ($user->favoriteBooks()->where('books.id', $book->id)->exists()) {
            $user->favoriteBooks()->detach($book->id);

            return back()->with(
                'success',
                'お気に入りから削除しました。'
            );
        }

        $user->favoriteBooks()->attach($book->id);

        return back()->with(
            'success',
            'お気に入りに登録しました。'
        );
    }
}