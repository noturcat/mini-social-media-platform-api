<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReindexTypesense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reindex-typesense';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $typesense = app(\App\Services\TypesenseService::class);

        $schema = [
            'name' => 'posts',
            'fields' => [
                ['name' => 'id', 'type' => 'int32'],
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'content', 'type' => 'string'],
            ],
            'default_sorting_field' => 'id',
        ];

        $typesense->createOrUpdateSchema($schema);
        $records = \App\Models\Post::all()->toArray();
        $typesense->reindexDocuments('posts', $records);

        $this->info('Reindex complete.');
    }

}
