<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
    public function run(): void
    {
        // Step 1: Create Users
        $users = User::factory(10)->create();

        // Step 2: Create Persons with matching user_id and email
        foreach ($users as $user) {
            Person::factory()->create([
                'user_id' => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
            ]);
        }

        // Step 3: Create other resources
        Post::factory(20)->create();
        Blog::factory(10)->create();
        Event::factory(10)->create();

        // Step 4: Sync to Typesense
        (new PostController)->syncToTypesense();
        (new PersonController)->syncToTypesense();
        (new BlogController)->syncToTypesense();
        (new EventController)->syncToTypesense();
    }
}
