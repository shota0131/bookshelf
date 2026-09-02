<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ログインユーザーのレビュー
        $reviews = Review::with(['book.genres'])
            ->where('user_id', $user->id)
            ->get();

        // 総レビュー数
        $totalReviews = $reviews->count();

        // 読了冊数
        $readBooks = $reviews
            ->pluck('book_id')
            ->unique()
            ->count();

        // 平均評価
        $averageRating = $reviews->avg('rating');

        // 評価分布
        $ratingDistribution = [];

        for ($rating = 1; $rating <= 5; $rating++) {
            $ratingDistribution[$rating] = $reviews
                ->where('rating', $rating)
                ->count();
        }

        // 高評価書籍 TOP5
        $topBooks = $reviews
            ->sortByDesc('rating')
            ->take(5)
            ->values();

        // ジャンル別評価
        $genreStats = [];

        foreach ($reviews as $review) {
            foreach ($review->book->genres as $genre) {

                if (!isset($genreStats[$genre->id])) {
                    $genreStats[$genre->id] = [
                        'name' => $genre->name,
                        'ratings' => [],
                    ];
                }

                $genreStats[$genre->id]['ratings'][] = $review->rating;
            }
        }

        $genreStats = collect($genreStats)
            ->map(function ($genre) {

                $genre['review_count'] = count($genre['ratings']);

                $genre['average_rating'] = collect($genre['ratings'])
                    ->avg();

                unset($genre['ratings']);

                return $genre;
            })
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();

        return view('reports.index', compact(
            'totalReviews',
            'readBooks',
            'averageRating',
            'ratingDistribution',
            'topBooks',
            'genreStats'
        ));
    }
}