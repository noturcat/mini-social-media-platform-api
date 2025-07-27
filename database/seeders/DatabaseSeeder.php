<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\EventController;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        \App\Models\Post::factory(20)->create();
        \App\Models\Person::factory(10)->create();
        \App\Models\Blog::factory(10)->create();
        \App\Models\Event::factory(10)->create();
        
        // Sync to Typesense
        (new PostController)->syncToTypesense();
        (new PersonController)->syncToTypesense();
        (new BlogController)->syncToTypesense();
        (new EventController)->syncToTypesense();
    }
}
