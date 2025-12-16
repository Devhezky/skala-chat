<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiAgentConfig;
use App\Models\AiAssistant;
use App\Models\AiSource;
use Illuminate\Http\Request;

class AiConfigurationController extends Controller
{
    public function manage($id)
    {
        $assistant = AiAssistant::with(['sources', 'agentConfig'])->findOrFail($id);
        $pageTitle = 'Configure ' . $assistant->name;
        return view('admin.ai_assistant.manage', compact('pageTitle', 'assistant'));
    }

    public function fetchUrl(Request $request, $id)
    {
        $request->validate([
            'url' => 'required|url'
        ]);
        
        // Mock crawler response for now or implement basic crawler
        $links = [
            ['url' => $request->url, 'status' => 'Fetched'],
            ['url' => $request->url . '/about', 'status' => 'Fetched'],
            ['url' => $request->url . '/contact', 'status' => 'Fetched'],
        ];

        return response()->json(['success' => true, 'links' => $links]);
    }

    public function storeSource(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:web,pdf,faq',
            'content' => 'required',
        ]);

        $source = new AiSource();
        $source->ai_assistant_id = $id;
        $source->type = $request->type;
        $source->content = $request->content;
        // meta handling
        if($request->type == 'pdf' && $request->hasFile('file')){
             // store file logic
        }
        $source->status = 0; // Untrained
        $source->save();

        return response()->json(['success' => true, 'message' => 'Source added successfully']);
    }

    public function deleteSource($id)
    {
        $source = AiSource::findOrFail($id);
        $source->delete();
        return response()->json(['success' => true, 'message' => 'Source deleted successfully']);
    }

    public function saveAgentConfig(Request $request, $id)
    {
        $request->validate([
            'trigger_condition' => 'nullable|string|max:500',
            'waiting_message'   => 'nullable|string|max:500',
        ]);

        $config = AiAgentConfig::updateOrCreate(
            ['ai_assistant_id' => $id],
            [
                'trigger_condition' => $request->trigger_condition,
                'waiting_message'   => $request->waiting_message,
                'is_active'         => $request->is_active ? 1 : 0
            ]
        );

        return back()->withNotify($notify);
    }

    public function chat(Request $request, $id)
    {
        $request->validate([
             'message' => 'required|string',
        ]);
        
        $assistant = AiAssistant::with(['sources'])->findOrFail($id);
        
        // MOCK LOGIC for "Testing Knowledge"
        // In a real scenario, this would generate embeddings -> vector search -> LLM call
        // For testing, we will check if any source content matches the query
        
        $response = "I am " . $assistant->name . ". I don't have a real brain yet, but I am listening.";
        
        // Simple keyword search in sources
        foreach($assistant->sources as $source){
             if($source->type == 'web' && stripos($source->content, 'github') !== false){
                 // Mocking finding something
             }
             // Just a simple keyword check
             if(stripos($source->content, $request->message) !== false){
                 $response = "I found something relevant in your sources: " . substr($source->content, 0, 100) . "...";
                 break;
             }
        }
        
        // If "knowledge" is requested, we can simulate retrieval
        // Since we don't have API Keys working for deepseek/z.ai yet in the backend service
        // We return a mock response to prove the UI works.
        
        return response()->json([
            'success' => true,
            'response' => $response
        ]);
    }
}
