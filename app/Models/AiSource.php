<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSource extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
        'status' => 'boolean'
    ];

    public function assistant()
    {
        return $this->belongsTo(AiAssistant::class, 'ai_assistant_id');
    }
}
