<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TypesenseService;
use App\Models\Post;
use App\Models\Blog;
use App\Models\Event;
use App\Models\Person;

class TypesenseReindex extends Command
{
    protected $signature = 'typesense:reindex';
    protected $description = 'Push all existing records to Typesense';

    public function handle()
    {
        $typesense = new TypesenseService();

        // === Reindex Posts ===
        $this->info('Reindexing posts...');
        foreach (Post::all() as $post) {
            $typesense->upsertDocument('posts', [
                'id' => $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'tags' => $post->tags,
                'person_id' => $post->person_id,
                'created_at' => $post->created_at->timestamp,
            ]);
        }

        // === Reindex Blogs ===
        $this->info('Reindexing blogs...');
        foreach (Blog::all() as $blog) {
            $typesense->upsertDocument('blogs', [
                'id' => $blog->id,
                'title' => $blog->title,
                'summary' => $blog->summary,
                'body' => $blog->body,
                'tags' => $blog->tags,
                'person_id' => $blog->person_id,
                'created_at' => $blog->created_at->timestamp,
            ]);
        }

        // === Reindex Events ===
        $this->info('Reindexing events...');
        foreach (Event::all() as $event) {
            $typesense->upsertDocument('events', [
                'id' => $event->id,
                'title' => $event->title,
                'location' => $event->location,
                'time' => $event->time,
                'person_id' => $event->person_id,
                'created_at' => $event->created_at->timestamp,
            ]);
        }

        // === Reindex People ===
        $this->info('Reindexing people...');
        foreach (Person::all() as $person) {
            $typesense->upsertDocument('people', [
                'id' => $person->id,
                'name' => $person->name,
                'email' => $person->email,
                'bio' => $person->bio,
                'created_at' => $person->created_at->timestamp,
            ]);
        }

        $this->info('✅ All data reindexed into Typesense.');
    }
}
