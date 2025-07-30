<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PersonController extends Controller
{
    public function index()
    {
        return Person::with(['posts', 'blogs', 'events'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:people,email',
            'bio' => 'nullable|string',
        ]);

        $person = Person::create($validated);

        try {
            $document = [
                'id' => (string) $person->id,
                'name' => $person->name,
                'email' => $person->email,
                'bio' => $person->bio,
            ];

            app('typesense')->upsertDocument('people', $document);   

        } catch (\Exception $e) {
            Log::error("❌ Typesense indexing failed for person ID {$person->id}: " . $e->getMessage());
        }

        return response()->json($person, 201);
    }

    public function show(Person $person)
    {
        return $person->load(['posts', 'blogs', 'events']);
    }

    public function update(Request $request, Person $person)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:people,email,' . $person->id,
            'bio' => 'nullable|string',
        ]);

        $person->update($validated);

        try {
            $document = [
                'id' => (string) $person->id,
                'name' => $person->name,
                'email' => $person->email,
                'bio' => $person->bio,
            ];
            
            app('typesense')->upsertDocument('people', $document);  
        } catch (\Exception $e) {
            Log::error("❌ Typesense update failed for person ID {$person->id}: " . $e->getMessage());
        }

        return response()->json($person);
    }

    public function destroy(Person $person)
    {
        $personId = $person->id;
        $person->delete();

        try {
            app('typesense')->deleteDocument('people', (string) $person->id); 
        } catch (\Exception $e) {
            Log::error("❌ Typesense delete failed for person ID {$personId}: " . $e->getMessage());
        }

        return response()->noContent();
    }

    public function syncToTypesense()
    {
        $people = Person::all();

        foreach ($people as $person) {
            try {
                app('typesense')->collections['people']->documents->upsert([
                    'id' => (string) $person->id,
                    'name' => $person->name,
                    'email' => $person->email,
                    'bio' => $person->bio,
                ]);
            } catch (\Exception $e) {
                Log::error("❌ Typesense sync failed for person ID {$person->id}: " . $e->getMessage());
            }
        }

        return response()->json(['message' => '✅ People synced to Typesense']);
    }
}
