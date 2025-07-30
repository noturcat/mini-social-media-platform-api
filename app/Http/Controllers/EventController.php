<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function index()
    {
        return Event::with('person')->latest('created_at')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'location' => 'required|string',
            'time' => 'required|date',
            'person_id' => 'required|exists:people,id',
        ]);

        $event = Event::create($validated);

        try {
            $document = [
                'id' => (string) $event->id,
                'title' => $event->title,
                'location' => $event->location,
                'time' => $event->time,
                'person_id' => (int) $event->person_id,
                'created_at' => strtotime($event->created_at),
            ];

            app('typesense')->upsertDocument('events', $document);
        } catch (\Exception $e) {
            Log::error('Typesense event store failed: ' . $e->getMessage());
        }

        return response()->json($event, 201);
    }

    public function show(Event $event)
    {
        return $event->load('person');
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string',
            'location' => 'sometimes|string',
            'time' => 'sometimes|date',
            'person_id' => 'sometimes|exists:people,id',
        ]);

        $event->update($validated);

        try {
            $document = [
                'id' => (string) $event->id,
                'title' => $event->title,
                'location' => $event->location,
                'time' => $event->time,
                'person_id' => (int) $event->person_id,
                'created_at' => strtotime($event->created_at),
            ];

            app('typesense')->upsertDocument('events', $document);
        } catch (\Exception $e) {
            Log::error('Typesense event update failed: ' . $e->getMessage());
        }

        return response()->json($event);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        try {
            app('typesense')->deleteDocument('events', (string) $event->id);
        } catch (\Exception $e) {
            Log::error('Typesense event delete failed: ' . $e->getMessage());
        }

        return response()->noContent();
    }

    public function syncToTypesense()
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

            $result = app('typesense')
                ->getClient()
                ->collections['events']
                ->documents
                ->import($documents, ['action' => 'upsert']);

            return response()->json([
                'message' => '✅ Events synced to Typesense',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Typesense event sync failed: ' . $e->getMessage());
            return response()->json(['error' => 'Typesense sync failed'], 500);
        }
    }
}
