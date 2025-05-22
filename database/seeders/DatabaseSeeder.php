<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
//        User::factory()->create([
//            'name' => 'Tim Jossund',
//            'email' => 'timjossund@gmail.com',
//            'username' => 'timjossund',
//            'avatar' => 'default-avatar.jpg',
//        ]);

        $categories = [
            'Practice',
            'Doctrine',
            'Culture',
            'History',
            'Evangelism',
            'Humor',
        ];

        foreach ($categories as $category) {
            Category::create([
                'title' => $category
            ]);
        }

//        Post::factory(100)->create();
    }
}
