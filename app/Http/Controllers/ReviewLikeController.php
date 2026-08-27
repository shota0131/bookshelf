<?php

namespace App\Http\Controllers;

use App\Models\Review;

class ReviewLikeController extends Controller
{
    public function toggle(Review $review)
    {
        $user = auth()->user();

        if ($user->likedReviews()->where('review_id', $review->id)->exists()) {
            $user->likedReviews()->detach($review->id);

            return back()->with('success', 'いいねを取り消しました。');
        }

        $user->likedReviews()->attach($review->id);

        return back()->with('success', 'いいねしました。');
    }

    public function like(Review $review)
    {
        $user = auth()->user();

        if ($user->likedReviews()->where('review_id', $review->id)->exists()) {
            $user->likedReviews()->detach($review->id);

            return back()->with('success', 'いいねを取り消しました。');
        }

        $user->likedReviews()->attach($review->id);

        return back()->with('success', 'いいねしました。');
    }
}
