<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::orderBy('id')->get();
        $books = Book::orderBy('id')->get();

        $favorites = [
            $users[0]->id => [1, 2, 3, 4, 5],
            $users[1]->id => [2, 5, 7, 8],
            $users[2]->id => [3, 6, 9],
            $users[3]->id => [1, 4, 8, 10],
            $users[4]->id => [2, 6, 10, 11],
        ];

        foreach ($favorites as $userId => $bookIds) {
            $user = User::find($userId);

            $ids = [];

            foreach ($bookIds as $bookId) {
                $ids[] = $books[$bookId - 1]->id;
            }

            $user->favoriteBooks()->syncWithoutDetaching($ids);
        }
    }
}
