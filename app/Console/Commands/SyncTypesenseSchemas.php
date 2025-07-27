<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TypesenseService;

class SyncTypesenseSchemas extends Command
{
    protected $signature = 'typesense:sync';
    protected $description = 'Sync Typesense schemas for all models';

    public function handle()
    {
        $typesense = new TypesenseService();

        // POSTS
        $typesense->createOrUpdateSchema([
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
    ]);


        // BLOGS
        $typesense->createOrUpdateSchema([
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
        ]);

        // PEOPLE
        $typesense->createOrUpdateSchema([
            'name' => 'people',
            'fields' => [
                ['name' => 'id', 'type' => 'int32'],
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'email', 'type' => 'string'],
                ['name' => 'bio', 'type' => 'string'],
                ['name' => 'created_at', 'type' => 'int64'],
            ],
            'default_sorting_field' => 'created_at'
        ]);

        // EVENTS
        $typesense->createOrUpdateSchema([
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
        ]);

        $this->info('✅ All Typesense schemas synced successfully.');
    }
}
