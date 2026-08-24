<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\RankingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// 書籍一覧
Route::get('/', [BookController::class, 'index'])
    ->name('books.index');

// 書籍登録
Route::get('/books/create', [BookController::class, 'create'])
    ->name('books.create');

// 書籍編集
Route::get('/books/{book}/edit', [BookController::class, 'edit'])
    ->name('books.edit');

// 書籍詳細
Route::get('/books/{book}', [BookController::class, 'show'])
    ->name('books.show');


// ジャンル一覧
Route::get('/genres', function () {
    return view('genres.index');
})->name('genres.index');

// ジャンル詳細
Route::get('/genres/{genre}', function ($genre) {
    return view('genres.show');
})->name('genres.show');

// ジャンル登録
Route::get('/genres/create', function () {
    return view('genres.create');
})->name('genres.create');

// ジャンル編集
Route::get('/genres/{genre}/edit', function ($genre) {
    return view('genres.edit');
})->name('genres.edit');

// レビュー編集
Route::get('/reviews/{review}/edit', function ($review) {
    return view('reviews.edit');
})->name('reviews.edit');

// お気に入り一覧
Route::get('/favorites', function () {
    return view('favorites.index');
})->name('favorites.index');

// ランキング
Route::get('/ranking', [RankingController::class, 'index'])
    ->name('ranking.index');