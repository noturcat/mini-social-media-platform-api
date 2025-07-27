<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TypesenseService;

class CreatePostSchema extends Command
{
    protected $signature = 'typesense:create-post-schema';
    protected $description = 'Create or update the Typesense schema for posts';

    public function handle(TypesenseService $typesense)
    {
        $schema = [
            'name' => 'posts',
            'fields' => [
                ['name' => 'id', 'type' => 'string'],
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'body', 'type' => 'string'],
                ['name' => 'image_url', 'type' => 'string', 'optional' => true],
                ['name' => 'tags', 'type' => 'string[]', 'optional' => true],
                ['name' => 'person_id', 'type' => 'int32', 'optional' => true],
            ],
            'default_sorting_field' => 'id',
        ];

        $typesense->createOrUpdateSchema($schema);

        $this->info('✅ Posts schema has been created or updated in Typesense.');
    }
}
