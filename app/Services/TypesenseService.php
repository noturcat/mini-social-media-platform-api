<?php

namespace App\Services;

use Typesense\Client;

class TypesenseService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'api_key' => env('TYPESENSE_API_KEY'),
            'nodes' => [[
                'host' => env('TYPESENSE_HOST'),
                'port' => env('TYPESENSE_PORT'),
                'protocol' => env('TYPESENSE_PROTOCOL'),
            ]],
            'connection_timeout_seconds' => 2,
        ]);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function createOrUpdateSchema($schema)
    {
        try {
            $this->client->collections[$schema['name']]->delete();
        } catch (\Throwable $e) {
            // Collection might not exist — ignore error
        }

        return $this->client->collections->create($schema);
    }

    public function upsertDocument($collection, $document)
    {
        return $this->client->collections[$collection]->documents->upsert($document);
    }

    public function deleteDocument($collection, $id)
    {
        return $this->client->collections[$collection]->documents[$id]->delete();
    }

    public function search($collection, $query)
    {
        return $this->client->collections[$collection]->documents->search($query);
    }

    public function reindexDocuments($collection, $records)
    {
        foreach ($records as $record) {
            $this->upsertDocument($collection, $record);
        }
    }
}
