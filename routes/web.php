<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;

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

// 書籍登録処理
Route::post('/books', [BookController::class, 'store'])
    ->name('books.store');

// 書籍編集
Route::get('/books/{book}/edit', [BookController::class, 'edit'])
    ->name('books.edit');

// 書籍更新
Route::put('/books/{book}', [BookController::class, 'update'])
    ->name('books.update');

// 書籍削除
Route::delete('/books/{book}', [BookController::class, 'destroy'])
    ->name('books.destroy');

// 書籍詳細
Route::get('/books/{book}', [BookController::class, 'show'])
    ->name('books.show');


// ジャンル一覧
Route::get('/genres', [GenreController::class, 'index'])
    ->name('genres.index');

// ジャンル登録画面
Route::get('/genres/create', [GenreController::class, 'create'])
    ->name('genres.create');

// ジャンル登録処理
Route::post('/genres', [GenreController::class, 'store'])
    ->name('genres.store');

// ジャンル詳細
Route::get('/genres/{genre}', [GenreController::class, 'show'])
    ->name('genres.show');

// ジャンル編集画面
Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])
    ->name('genres.edit');

// ジャンル更新処理
Route::put('/genres/{genre}', [GenreController::class, 'update'])
    ->name('genres.update');

// ジャンル削除
Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])
    ->name('genres.destroy');

// レビュー編集
Route::get('/reviews/{review}/edit', function ($review) {
    return view('reviews.edit');
})->name('reviews.edit');


// ランキング
Route::get('/ranking', [RankingController::class, 'index'])
    ->name('ranking.index');

Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index'])
        ->name('favorites.index');

    Route::post('/favorites/{book}/toggle', [FavoriteController::class, 'toggle'])
        ->name('favorites.toggle');
    
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');

    Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'like'])
    ->name('reviews.like');
});