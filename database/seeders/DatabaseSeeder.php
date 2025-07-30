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
        // Step 1: Create Users
        $users = \App\Models\User::factory(5)->create();

        // Step 2: Create Persons with matching user_id
        foreach ($users as $user) {
            \App\Models\Person::factory()->create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        // Continue with other seeds (optional)
        \App\Models\Post::factory(20)->create();
        \App\Models\Blog::factory(10)->create();
        \App\Models\Event::factory(10)->create();

        // Sync to Typesense
        (new \App\Http\Controllers\PostController)->syncToTypesense();
        (new \App\Http\Controllers\PersonController)->syncToTypesense();
        (new \App\Http\Controllers\BlogController)->syncToTypesense();
        (new \App\Http\Controllers\EventController)->syncToTypesense();
    }


}
