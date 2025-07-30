<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    public function index()
    {
        return Post::with('person')->latest('created_at')->get();
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

        $validated['tags'] = json_encode($validated['tags'] ?? []);
        $post = Post::create($validated);

        try {
            $document = [
                'id' => (string) $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'image_url' => $post->image_url,
                'tags' => json_decode($post->tags, true),
                'person_id' => (int) $post->person_id,
                'created_at' => strtotime($post->created_at),
            ];

            app('typesense')->upsertDocument('posts', $document);
        } catch (\Exception $e) {
            Log::error("❌ Typesense post store failed: " . $e->getMessage());
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
                'tags' => is_string($post->tags) ? json_decode($post->tags, true) : [],
                'person_id' => (int) $post->person_id,
                'created_at' => strtotime($post->created_at),
            ];

            app('typesense')->upsertDocument('posts', $document);
        } catch (\Exception $e) {
            Log::error("❌ Typesense post update failed: " . $e->getMessage());
        }

        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        $postId = (string) $post->id;
        $post->delete();

        try {
            app('typesense')->deleteDocument('posts', $postId);
        } catch (\Exception $e) {
            Log::error("❌ Typesense post delete failed for ID {$postId}: " . $e->getMessage());
        }

        return response()->noContent();
    }

    public function syncToTypesense()
    {
        try {
            $posts = Post::all();

            $documents = $posts->map(function ($post) {
                return [
                    'id' => (string) $post->id,
                    'title' => $post->title,
                    'body' => $post->body,
                    'image_url' => $post->image_url,
                    'tags' => is_string($post->tags) ? json_decode($post->tags, true) : [],
                    'person_id' => (int) $post->person_id,
                    'created_at' => strtotime($post->created_at),
                ];
            })->toArray();

            $result = app('typesense')
                ->getClient()
                ->collections['posts']
                ->documents
                ->import($documents, ['action' => 'upsert']);

            return response()->json([
                'message' => '✅ Posts synced to Typesense',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Typesense post sync failed: ' . $e->getMessage());
            return response()->json(['error' => 'Typesense sync failed'], 500);
        }
    }
}
