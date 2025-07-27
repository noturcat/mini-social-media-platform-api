<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // ✅ Import Log facade

class BlogController extends Controller
{
    public function index()
    {
        return Blog::with('person')->get();
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
            ];

            app('typesense')->collections['blogs']->documents->upsert($document);
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
                'tags' => is_string($blog->tags) ? json_decode($blog->tags, true) : $blog->tags,
                'person_id' => (int) $blog->person_id,
            ];

            app('typesense')->collections['blogs']->documents->upsert($document);
        } catch (\Exception $e) {
            Log::error('Typesense blog update failed: ' . $e->getMessage());
        }

        return response()->json($blog);
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();

        try {
            app('typesense')->collections['blogs']
                ->documents[(string) $blog->id]
                ->delete();
        } catch (\Exception $e) {
            Log::error('Typesense blog delete failed: ' . $e->getMessage());
        }

        return response()->noContent();
    }

    public function syncToTypesense()
    {
        $blogs = Blog::all();

        foreach ($blogs as $blog) {
            try {
                app('typesense')->collections['blogs']->documents->upsert([
                    'id' => (string) $blog->id,
                    'title' => $blog->title,
                    'summary' => $blog->summary,
                    'body' => $blog->body,
                    'person_id' => (int) $blog->person_id,
                ]);
            } catch (\Exception $e) {
                Log::error("Typesense sync failed for blog ID {$blog->id}: " . $e->getMessage());
            }
        }

        return response()->json(['message' => '✅ Blogs synced to Typesense']);
    }
}
