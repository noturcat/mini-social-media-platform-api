<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Typesense\Client;
use App\Models\Post;
use App\Models\Blog;
use App\Models\Event;
use App\Models\Person;
use App\Services\TypesenseService;


class TypesenseController extends Controller
{
    protected Client $typesense;

    public function __construct()
    {
        $this->typesense = new Client([
            'nodes' => [[
                'host' => env('TYPESENSE_HOST'),
                'port' => env('TYPESENSE_PORT'),
                'protocol' => env('TYPESENSE_PROTOCOL'),
            ]],
            'api_key' => env('TYPESENSE_API_KEY'),
            'connection_timeout_seconds' => 2,
        ]);
    }

    public function syncAll()
    {
        $this->createPostsCollection();
        $this->syncPosts();

        $this->createBlogsCollection();
        $this->syncBlogs();

        $this->createPeopleCollection();
        $this->syncPeople();

        $this->createEventsCollection();
        $this->syncEvents();

        return response()->json(['message' => '✅ All collections synced']);
    }

    public function health()
    {
        $health = $this->typesense->health->retrieve();
        return response()->json($health);
    }

    public function createPostsCollection()
    {
        try {
            $schema = [
                'name' => 'posts',
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'body', 'type' => 'string'],
                    ['name' => 'tags', 'type' => 'string[]', 'facet' => true],
                    ['name' => 'person_id', 'type' => 'string'],
                    ['name' => 'created_at', 'type' => 'int64'],
                ],
                'default_sorting_field' => 'created_at'
            ];

            $collection = $this->typesense->collections->create($schema);

            return response()->json(['message' => 'Posts collection created!', 'collection' => $collection]);
        } catch (\Typesense\Exceptions\ObjectAlreadyExists $e) {
            return response()->json(['message' => 'Posts collection already exists.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncPosts()
    {
        try {
            $posts = Post::all();

            $documents = $posts->map(function ($post) {
                return [
                    'id' => (string) $post->id,
                    'title' => $post->title,
                    'body' => $post->body,
                    'tags' => ($post->tags) ?? [],
                    'person_id' => (string) $post->person_id,
                    'created_at' => strtotime($post->created_at),
                ];
            })->toArray();

            $result = $this->typesense->collections['posts']
                ->documents
                ->import($documents, ['action' => 'upsert']);

            return response()->json(['message' => 'Posts synced to Typesense', 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error syncing posts', 'error' => $e->getMessage()], 500);
        }
    }

    public function createBlogsCollection()
    {
        try {
            $schema = [
                'name' => 'blogs',
                'fields' => [
                    ['name' => 'id', 'type' => 'int32'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'summary', 'type' => 'string'],
                    ['name' => 'body', 'type' => 'string'],
                    ['name' => 'tags', 'type' => 'string[]', 'facet' => true],
                    ['name' => 'person_id', 'type' => 'int32'],
                    ['name' => 'created_at', 'type' => 'int64'],
                ],
                'default_sorting_field' => 'created_at'
            ];

            $collection = $this->typesense->collections->create($schema);

            return response()->json(['message' => 'Blogs collection created!', 'collection' => $collection]);
        } catch (\Typesense\Exceptions\ObjectAlreadyExists $e) {
            return response()->json(['message' => 'Blogs collection already exists.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncBlogs()
    {
        try {
            $blogs = Blog::all();

            $documents = $blogs->map(function ($blog) {
                return [
                    'id' => (string) $blog->id,
                    'title' => $blog->title,
                    'summary' => $blog->summary,
                    'body' => $blog->body, 
                    'tags' => is_array($blog->tags) ? $blog->tags : [],
                    'person_id' => (int) $blog->person_id,
                    'created_at' => strtotime($blog->created_at),
                ];
            })->toArray();

            $result = $this->typesense->collections['blogs']
                ->documents
                ->import($documents, ['action' => 'upsert']);

            return response()->json(['message' => 'Blogs synced to Typesense', 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error syncing blogs', 'error' => $e->getMessage()], 500);
        }
    }

    public function createPeopleCollection()
    {
        try {
            $schema = [
                'name' => 'people',
                'fields' => [
                    ['name' => 'id', 'type' => 'int32'],
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'email', 'type' => 'string'],
                    ['name' => 'bio', 'type' => 'string'],
                    ['name' => 'created_at', 'type' => 'int64'],
                ],
                'default_sorting_field' => 'created_at'
            ];

            $collection = $this->typesense->collections->create($schema);

            return response()->json(['message' => 'People collection created!', 'collection' => $collection]);
        } catch (\Typesense\Exceptions\ObjectAlreadyExists $e) {
            return response()->json(['message' => 'People collection already exists.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncPeople()
    {
        try {
            $people = Person::all();

            $documents = $people->map(function ($person) {
                return [
                    'id' => (string) $person->id,
                    'name' => $person->name,
                    'email' => $person->email,
                    'bio' => $person->bio,
                    'created_at' => strtotime($person->created_at),
                ];
            })->toArray();

            $result = $this->typesense->collections['people']
                ->documents
                ->import($documents, ['action' => 'upsert']);

            return response()->json(['message' => 'People synced to Typesense', 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error syncing people', 'error' => $e->getMessage()], 500);
        }
    }

    public function createEventsCollection()
    {
        try {
            $schema = [
                'name' => 'events',
                'fields' => [
                    ['name' => 'id', 'type' => 'int32'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'location', 'type' => 'string'],
                    ['name' => 'time', 'type' => 'string'],
                    ['name' => 'person_id', 'type' => 'int32'],
                    ['name' => 'created_at', 'type' => 'int64'],
                ],
                'default_sorting_field' => 'created_at'
            ];

            $collection = $this->typesense->collections->create($schema);

            return response()->json(['message' => 'Events collection created!', 'collection' => $collection]);
        } catch (\Typesense\Exceptions\ObjectAlreadyExists $e) {
            return response()->json(['message' => 'Events collection already exists.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncEvents()
    {
        try {
            $events = Event::all();

            $documents = $events->map(function ($event) {
                return [
                    'id' => (string) $event->id,
                    'title' => $event->title,
                    'location' => $event->location,
                    'time' => $event->time,
                    'person_id' => (int) $event->person_id,
                    'created_at' => strtotime($event->created_at),
                ];
            })->toArray();

            $result = $this->typesense->collections['events']
                ->documents
                ->import($documents, ['action' => 'upsert']);

            return response()->json(['message' => 'Events synced to Typesense', 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error syncing events', 'error' => $e->getMessage()], 500);
        }
    }

}
