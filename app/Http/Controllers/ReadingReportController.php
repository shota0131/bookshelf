<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReadingReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $reviews = Review::with(['book.genres'])
            ->where('user_id', $user->id)
            ->get();

        $totalReviews = $reviews->count();

        $readBooks = $reviews
            ->pluck('book_id')
            ->unique()
            ->count();

        $averageRating = $reviews->avg('rating');


        $ratingDistribution = [];

        for ($rating = 1; $rating <= 5; $rating++) {
            $ratingDistribution[$rating] = $reviews
                ->where('rating', $rating)
                ->count();
        }



        $topBooks = $reviews
            ->sortByDesc('rating')
            ->take(5)
            ->values();


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


        return view('reading-reports.index', compact(
            'totalReviews',
            'readBooks',
            'averageRating',
            'ratingDistribution',
            'topBooks',
            'genreStats'
        ));
    }
}
