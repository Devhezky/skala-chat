<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAgentConfig extends Model
{
    protected $guarded = ['id'];

    public function assistant()
    {
        return $this->belongsTo(AiAssistant::class, 'ai_assistant_id');
    }
}
