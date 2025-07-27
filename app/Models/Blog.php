<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'summary',
        'body',
        'image_url',
        'tags',
        'person_id',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function toTypesenseArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'body' => $this->body, 
            'person_id' => $this->person_id,
            'created_at' => $this->created_at,
        ];
    }
}
