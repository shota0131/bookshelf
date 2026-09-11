<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\NotificationController;

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

// CSV出力
Route::get('/books/csv', [BookController::class, 'csv'])
    ->name('books.csv');

// 書籍登録
Route::get('/books/create', [BookController::class, 'create'])
    ->name('books.create');

// 書籍登録処理
Route::post('/books', [BookController::class, 'store'])
    ->name('books.store');

// 書籍編集
Route::get('/books/{book}/edit', [BookController::class, 'edit'])
    ->name('books.edit');

// ISBN検索
Route::get('/books/isbn-search', [BookController::class, 'isbnSearch'])
    ->name('books.isbn-search');

// 書籍更新
Route::put('/books/{book}', [BookController::class, 'update'])
    ->name('books.update');

// 書籍削除
Route::delete('/books/{book}', [BookController::class, 'destroy'])
    ->name('books.destroy');

// 書籍詳細
Route::get('/books/{book}', [BookController::class, 'show'])
    ->name('books.show');

// マイ読書レポート
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware('auth')
    ->name('reports.index');

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

// ランキング
Route::get('/ranking', [RankingController::class, 'index'])
    ->name('ranking.index');

Route::middleware('auth')->group(function () {

    // お気に入り一覧
    Route::get('/favorites', [FavoriteController::class, 'index'])
        ->name('favorites.index');

    // お気に入り登録・解除
    Route::post('/favorites/{book}/toggle', [FavoriteController::class, 'toggle'])
        ->name('favorites.toggle');

    // レビュー投稿
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');

    // レビュー編集画面
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])
        ->name('reviews.edit');

    // レビュー更新
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])
        ->name('reviews.update');

    // レビュー削除
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');

    // レビューいいね
    Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'like'])
        ->name('reviews.like');

    // 通知一覧
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    // 通知を既読にする
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])
        ->name('notifications.read');

    // 読書計画一覧
    Route::get('/reading-plans', [ReadingPlanController::class, 'index'])
        ->name('reading-plans.index');

    // 読書計画作成画面
    Route::get('/reading-plans/create', [ReadingPlanController::class, 'create'])
        ->name('reading-plans.create');

    // 読書計画登録
    Route::post('/reading-plans', [ReadingPlanController::class, 'store'])
        ->name('reading-plans.store');

    // 読書計画完了
    Route::post('/reading-plans/{plan}/complete', [ReadingPlanController::class, 'complete'])
        ->name('reading-plans.complete');

    // 読書計画編集画面
    Route::get('/reading-plans/{plan}/edit', [ReadingPlanController::class, 'edit'])
        ->name('reading-plans.edit');

    // 読書計画更新
    Route::put('/reading-plans/{plan}', [ReadingPlanController::class, 'update'])
        ->name('reading-plans.update');

    // 読書計画削除
    Route::delete('/reading-plans/{plan}', [ReadingPlanController::class, 'destroy'])
        ->name('reading-plans.destroy');
});