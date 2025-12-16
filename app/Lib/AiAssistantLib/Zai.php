<?php

namespace App\Lib\AiAssistantLib;

use App\Models\AiAssistant;
use Exception;
use Illuminate\Support\Facades\Http;

class Zai
{
    protected $apiKey;
    protected $model;
    protected $temperature;

    public function __construct()
    {
        $assistant = AiAssistant::where('provider', 'zai')->active()->first();

        if ($assistant) {
            $config = (object) $assistant->config;

            $this->apiKey       = $config->api_key ?? null;
            $this->model        = $config->model ?? 'glm-4';
            $this->temperature  = $config->temperature ?? 0.7;
        }
    }

    public function getAiReply(string $systemPrompt, string $prompt, array $history = [])
    {
        try {
            $systemPrompt = strip_tags($systemPrompt);
            $prompt = strip_tags($prompt);
            
            // Z.ai / GLM-4
            $url = 'https://open.bigmodel.cn/api/paas/v4/chat/completions';

            // Construct messages array
            $messages = [
                [
                    'role'    => 'system',
                    'content' => $systemPrompt,
                ]
            ];

            // Append history
            foreach ($history as $msg) {
                if (isset($msg['role']) && isset($msg['content'])) {
                     $messages[] = [
                        'role' => $msg['role'],
                        'content' => strip_tags($msg['content']) 
                     ];
                }
            }

            // Append current user message
            $messages[] = [
                'role'    => 'user',
                'content' => $prompt,
            ];

            $response = Http::withToken($this->apiKey)
                ->post($url, [
                    'model'       => $this->model,
                    'temperature' => (float)$this->temperature,
                    'messages'    => $messages,
                ]);

            $data = $response->json();

            if (isset($data['error'])) {
                throw new Exception($data['error']['message'] ?? 'Something went wrong');
            }

            if (!isset($data['choices'][0]['message']['content'])) {
                throw new Exception("Unable to generate response");
            }

            if ($response->successful()) {
                return [
                    'response' => $data['choices'][0]['message']['content'] ?? null,
                    'success'  => true
                ];
            }
        } catch (Exception $e) {
            return [
                'response' => $e->getMessage(),
                'success'  => false
            ];
        }
    }
}
