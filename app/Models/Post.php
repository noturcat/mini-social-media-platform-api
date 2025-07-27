<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'body', 'image_url', 'tags', 'person_id'];

    protected $casts = ['tags' => 'array'];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function syncToTypesense()
    {
        try {
            $document = [
                'id' => (string) $this->id,
                'title' => $this->title,
                'body' => $this->body,
                'image_url' => $this->image_url,
                'tags' => $this->tags ?? [],
                'person_id' => (int) $this->person_id,
            ];

            app('typesense')->collections['posts']
                ->documents
                ->upsert($document);
        } catch (\Exception $e) {
            // Optional: log error
        }
    }

    public function deleteFromTypesense()
    {
        try {
            app('typesense')->collections['posts']
                ->documents[(string) $this->id]
                ->delete();
        } catch (\Exception $e) {
            // Optional: log error
        }
    }
}

