<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    public function index()
    {
        return Blog::with('person')->latest('created_at')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'summary' => 'required|string',
            'body' => 'required|string',
            'image_url' => 'nullable|string',
            'tags' => 'nullable|array',
            'person_id' => 'required|exists:people,id',
        ]);

        $validated['tags'] = json_encode($validated['tags'] ?? []);
        $blog = Blog::create($validated);

        try {
            $document = [
                'id' => (string) $blog->id,
                'title' => $blog->title,
                'summary' => $blog->summary,
                'body' => $blog->body,
                'image_url' => $blog->image_url,
                'tags' => json_decode($blog->tags, true),
                'person_id' => (int) $blog->person_id,
                'created_at' => strtotime($blog->created_at),
            ];

            app('typesense')->upsertDocument('blogs', $document);
        } catch (\Exception $e) {
            Log::error('Typesense blog store failed: ' . $e->getMessage());
        }

        return response()->json($blog, 201);
    }

    public function show(Blog $blog)
    {
        return $blog->load('person');
    }

    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string',
            'summary' => 'sometimes|string',
            'body' => 'sometimes|string',
            'image_url' => 'nullable|string',
            'tags' => 'nullable|array',
            'person_id' => 'sometimes|exists:people,id',
        ]);

        if (isset($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        }

        $blog->update($validated);

        try {
            $document = [
                'id' => (string) $blog->id,
                'title' => $blog->title,
                'summary' => $blog->summary,
                'body' => $blog->body,
                'image_url' => $blog->image_url,
                'tags' => is_string($blog->tags) ? json_decode($blog->tags, true) : ($blog->tags ?? []),
                'person_id' => (int) $blog->person_id,
                'created_at' => strtotime($blog->created_at),
            ];

            app('typesense')->upsertDocument('blogs', $document);
        } catch (\Exception $e) {
            Log::error('Typesense blog update failed: ' . $e->getMessage());
        }

        return response()->json($blog);
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();

        try {
            app('typesense')->deleteDocument('blogs', (string) $blog->id);
        } catch (\Exception $e) {
            Log::error('Typesense blog delete failed: ' . $e->getMessage());
        }

        return response()->noContent();
    }

    public function syncToTypesense()
    {
        try {
            $blogs = Blog::all();

            $documents = $blogs->map(function ($blog) {
                return [
                    'id' => (string) $blog->id,
                    'title' => $blog->title,
                    'summary' => $blog->summary,
                    'body' => $blog->body,
                    'image_url' => $blog->image_url,
                    'tags' => is_string($blog->tags) ? json_decode($blog->tags, true) : ($blog->tags ?? []),
                    'person_id' => (int) $blog->person_id,
                    'created_at' => strtotime($blog->created_at),
                ];
            })->toArray();

            $result = app('typesense')
                ->getClient()
                ->collections['blogs']
                ->documents
                ->import($documents, ['action' => 'upsert']);

            return response()->json([
                'message' => '✅ Blogs synced to Typesense',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Typesense blog sync failed: ' . $e->getMessage());
            return response()->json(['error' => 'Typesense sync failed'], 500);
        }
    }
}
