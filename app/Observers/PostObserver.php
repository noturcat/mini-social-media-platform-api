<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\TypesenseService;

class PostObserver
{
    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post)
    {
        app(TypesenseService::class)->upsertDocument('posts', [
            'id' => (string) $post->id,
            'title' => $post->title,
            'body' => $post->body,
            'tags' => $post->tags,
            'person_id' => $post->person_id,
            'created_at' => $post->created_at->timestamp,
        ]);
    }

    public function updated(Post $post)
    {
        app(TypesenseService::class)->upsertDocument('posts', [
            'id' => (string) $post->id,
            'title' => $post->title,
            'body' => $post->body,
            'tags' => $post->tags,
            'person_id' => $post->person_id,
            'created_at' => $post->created_at->timestamp,
        ]);
    }

    public function deleted(Post $post)
    {
        app(TypesenseService::class)->deleteDocument('posts', (string) $post->id);
    }

    /**
     * Handle the Post "restored" event.
     */
    public function restored(Post $post): void
    {
        //
    }

    /**
     * Handle the Post "force deleted" event.
     */
    public function forceDeleted(Post $post): void
    {
        //
    }
}
