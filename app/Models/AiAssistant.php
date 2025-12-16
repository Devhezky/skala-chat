<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class AiAssistant extends Model
{

    use GlobalStatus;

    protected $casts = [
        'config' => 'object',
    ];

    public function sources()
    {
        return $this->hasMany(AiSource::class, 'ai_assistant_id');
    }

    public function agentConfig()
    {
        return $this->hasOne(AiAgentConfig::class, 'ai_assistant_id');
    }

}
