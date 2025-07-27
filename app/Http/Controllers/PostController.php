<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // ✅ Add this

class PostController extends Controller
{
    public function index()
    {
        return Post::with('person')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'image_url' => 'nullable|string',
            'tags' => 'nullable|array',
            'person_id' => 'required|exists:people,id',
        ]);

        $post = Post::create($validated);

        try {
            $document = [
                'id' => (string) $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'image_url' => $post->image_url,
                'tags' => $post->tags,
                'person_id' => (int) $post->person_id,
            ];

            app('typesense')->collections['posts']->documents->upsert($document);
        } catch (\Exception $e) {
            Log::error('Typesense post store failed: ' . $e->getMessage());
        }

        return response()->json($post, 201);
    }

    public function show(Post $post)
    {
        return $post->load('person');
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string',
            'body' => 'sometimes|string',
            'image_url' => 'nullable|string',
            'tags' => 'nullable|array',
            'person_id' => 'sometimes|exists:people,id',
        ]);

        if (isset($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        }

        $post->update($validated);

        try {
            $document = [
                'id' => (string) $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'image_url' => $post->image_url,
                'tags' => is_string($post->tags) ? json_decode($post->tags, true) : $post->tags,
                'person_id' => (int) $post->person_id,
            ];

            app('typesense')->collections['posts']->documents->upsert($document);
        } catch (\Exception $e) {
            Log::error('Typesense post update failed: ' . $e->getMessage());
        }

        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        $post->delete();

        try {
            app('typesense')->collections['posts']->documents[(string) $post->id]->delete();
        } catch (\Exception $e) {
            Log::error('Typesense post delete failed: ' . $e->getMessage());
        }

        return response()->noContent();
    }

    public function syncToTypesense()
    {
        $posts = Post::all();

        foreach ($posts as $post) {
            try {
                app('typesense')->collections['posts']->documents->upsert([
                    'id' => (string) $post->id,
                    'title' => $post->title,
                    'body' => $post->body,
                    'image_url' => $post->image_url,
                    'tags' => $post->tags,
                    'person_id' => (int) $post->person_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Typesense sync failed for post ID ' . $post->id . ': ' . $e->getMessage());
            }
        }

        return response()->json(['message' => '✅ Posts synced to Typesense']);
    }
}
