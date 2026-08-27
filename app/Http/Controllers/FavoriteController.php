<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

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
}