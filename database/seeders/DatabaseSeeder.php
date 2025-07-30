<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Person;
use App\Models\Post;
use App\Models\Blog;
use App\Models\Event;
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
        // 🧹 Optional: Reset tables to avoid duplicate key errors
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Person::truncate();
        Post::truncate();
        Blog::truncate();
        Event::truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ✅ Step 1: Create Users
        $users = User::factory(5)->create();

        // ✅ Step 2: Create matching Persons
        foreach ($users as $user) {
            Person::factory()->create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        // ✅ Step 3: Other factories
        Post::factory(20)->create();
        Blog::factory(10)->create();
        Event::factory(10)->create();

        // ✅ Step 4: Sync all to Typesense
        (new PostController)->syncToTypesense();
        (new PersonController)->syncToTypesense();
        (new BlogController)->syncToTypesense();
        (new EventController)->syncToTypesense();
    }
}
