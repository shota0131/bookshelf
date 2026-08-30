<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::orderBy('id')->get();
        $reviews = Review::orderBy('id')->get();

        foreach ($reviews as $review) {
            $availableUsers = $users
                ->where('id', '!=', $review->user_id)
                ->values();

            $likeCount = min(rand(0, 3), $availableUsers->count());

            if ($likeCount === 0) {
                continue;
            }

            $likeUserIds = $availableUsers
                ->random($likeCount)
                ->pluck('id')
                ->toArray();

            $review->likedByUsers()->syncWithoutDetaching($likeUserIds);
        }
    }
}
