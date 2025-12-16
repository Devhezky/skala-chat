<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiAssistantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assistants = [
            [
                'name'     => 'Deepseek AI',
                'provider' => 'deepseek',
                'info'     => 'DeepSeek-V3 is a strong Mixture-of-Experts (MoE) language model with 671B total parameters with 37B active',
                'config'   => json_encode([
                    'api_key' => '',
                    'model'   => 'deepseek-chat',
                ]),
                'status'   => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'     => 'Z.ai (GLM)',
                'provider' => 'zai',
                'info'     => 'Z.ai provides powerful GLM models for various AI tasks.',
                'config'   => json_encode([
                    'api_key' => '',
                    'model'   => 'glm-4',
                ]),
                'status'   => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('ai_assistants')->insert($assistants);
    }
}
