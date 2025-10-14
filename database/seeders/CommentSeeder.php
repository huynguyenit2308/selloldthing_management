<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = Review::all();
        $users = User::all();

        if ($reviews->count() == 0 || $users->count() == 0) {
            return;
        }

        foreach ($reviews as $review) {
            $commentCount = rand(1, 3);

            for ($i = 0; $i < $commentCount; $i++) {
                Comment::create([
                    'review_id' => $review->id,
                    'user_id' => $users->random()->id,
                    'content' => fake()->sentence(10),
                ]);
            }
        }
    }
}
