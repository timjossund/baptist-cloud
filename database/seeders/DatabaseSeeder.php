<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = [
          'Religion',
          'Doctrine',
          'Culture',
          'Church',
          'Evangelism',
        ];

        foreach ($categories as $category) {
            Category::create([
                'title' => $category
            ]);
        }

        Post::factory(100)->create();
    }
}
