<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\AiAssistant;
use App\Models\AiAgentConfig;
use App\Models\AiSource;
use App\Models\WelcomeMessage;
use App\Models\WhatsappAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutomationController extends Controller
{
    public function welcomeMessage()
    {
        $pageTitle       = "Welcome Message";
        $user            = getParentUser();
        $accounts        = WhatsappAccount::where('user_id', $user->id)->get();
        $availableAccounts = WhatsappAccount::where('user_id', $user->id)->whereDoesntHave('welcomeMessage')->get();
        $welcomeMessages = WelcomeMessage::whereHas('whatsappAccount', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('whatsappAccount')->get();

        if ($accounts->isEmpty()) {
            $view   = 'Template::user.inbox.whatsapp_account_empty';
        } else {
            $view = 'Template::user.automation.welcome_message';
        }

        return responseManager("welcome_message", $pageTitle, "success", [
            'pageTitle'       => $pageTitle,
            'accounts'        => $availableAccounts,
            'welcomeMessages' => $welcomeMessages,
            'view'            => $view,
        ]);
    }

    public function welcomeMessageStore(Request $request, $id = 0)
    {
        $isRequired = $id ? 'nullable' : 'required';
        $request->validate([
            'whatsapp_account_id' => $isRequired,
            'message'             => 'required|string',
        ]);

        $user = getParentUser();
        if ($id) {
            $welcomeMessage = WelcomeMessage::whereHas('whatsappAccount', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->findOrFail($id);
            $message = "Welcome message updated successfully.";
        } else {

            if (!featureAccessLimitCheck($user->welcome_message)) {
                return responseManager('not_available', "The Welcome Message feature is not included in your current plan. Please upgrade to access this feature");
            }
            $whatsappAccount = WhatsappAccount::where('user_id', $user->id)->whereDoesntHave('welcomeMessage')->find($request->whatsapp_account_id);

            if (!$whatsappAccount) {
                $notify[] = ['error', 'The whatsapp account is invalid'];
                return back()->withNotify($notify);
            }
            if ($whatsappAccount->welcomeMessage) {
                $notify[] = ['error', 'The welcome message already exists for this account'];
                return back()->withNotify($notify);
            }
            $welcomeMessage                      = new WelcomeMessage();
            $welcomeMessage->whatsapp_account_id = $whatsappAccount->id;
            $message                             = "Welcome message created successfully.";
        }

        $welcomeMessage->message = $request->message;
        $welcomeMessage->save();

        $notify[] = ['success', $message];
        return back()->withNotify($notify);
    }

    public function welcomeMessageStatus($id)
    {
        $user           = getParentUser();
        $welcomeMessage = WelcomeMessage::whereHas('whatsappAccount', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        $welcomeMessage->status = !$welcomeMessage->status;
        $welcomeMessage->save();

        $notify[] = ['success', "Welcome message status changed successfully"];
        return back()->withNotify($notify);
    }

    public function aiAssistant()
    {
        $pageTitle = "AI Assistant";

        $activeAiAssistant = AiAssistant::active()->first();

        $user  =  getParentUser();

        $aiSetting = $user->aiSetting;

        if (!$aiSetting) {
            $aiSetting = createAiSetting($user);
        }

        // Fetch User's RAG Data
        $sources = AiSource::where('user_id', $user->id)->get();
        $agentConfig = AiAgentConfig::where('user_id', $user->id)->first();

        return view('Template::user.automation.ai_assistant', compact('pageTitle', 'aiSetting', 'activeAiAssistant', 'sources', 'agentConfig'));
    }

    public function aiAssistantStore(Request $request)
    {
        // ... (existing implementation)
        $check = AiAssistant::active()->exists();

        if (!$check) {
            $notify[] = ['error', ' AI Assistant is not active for this platform.'];
            return back()->withNotify($notify);
        }

        $user = getParentUser();
        
        if(!featureAccessLimitCheck($user->ai_assistance)) {
            return responseManager('not_available', 'Your current plan does not support AI Assistant. Please upgrade your plan.');
        }

        $request->validate([
            'max_length'        => 'required|integer|gte:0',
            'system_prompt'     => 'required|string',
            'fallback_response' => 'nullable|string',
            'max_history_limit' => 'required|integer|min:1|max:250',
            'delay_type'        => 'required|in:fixed,smart',
            'min_delay'         => 'required|integer|min:0',
            'max_delay'         => 'required|integer|gte:min_delay',
        ]);


        if (!$user->aiSetting) {
            createAiSetting($user);
        }

        $aiSetting                    = $user->aiSetting;
        $aiSetting->max_length        = $request->max_length;
        $aiSetting->system_prompt     = $request->system_prompt;
        $aiSetting->fallback_response = $request->fallback_response;
        $aiSetting->max_history_limit = $request->max_history_limit;
        $aiSetting->enable_split_chat = $request->enable_split_chat ? 1 : 0;
        
        $aiSetting->delay_type        = $request->delay_type;
        $aiSetting->min_delay         = $request->min_delay;
        $aiSetting->max_delay         = $request->max_delay;

        $aiSetting->status            = $request->status ? Status::ENABLE : Status::DISABLE;
        $aiSetting->save();

        $notify[] = ['success', 'AI Assistant settings updated successfully'];
        return back()->withNotify($notify);
    }
    
    // NEW METHODS FOR USER DASHBOARD
    
    public function fetchUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        try {
            // Basic Scraper
            $content = file_get_contents($request->url);
            
            if (!$content) {
                return response()->json(['success' => false, 'message' => 'Failed to fetch URL contents.']);
            }

            // Extract Text
            $dom = new \DOMDocument();
            @$dom->loadHTML($content);
            $xpath = new \DOMXPath($dom);
            
            // Remove scripts and styles
            foreach ($xpath->query('//script|//style') as $node) {
                $node->parentNode->removeChild($node);
            }
            
            // Get text content (simple extraction)
            $text = trim($dom->textContent);
            
            // Clean up whitespace
            $text = preg_replace('/\s+/', ' ', $text);
            $text = mb_substr($text, 0, 5000); // Limit to 5000 chars for now
            
            if (empty($text)) {
                 return response()->json(['success' => false, 'message' => 'No readable text found on the page.']);
            }

            // Prepare links info (mocked for now as we just scraped one page)
            $links = [
                ['url' => $request->url, 'status' => 'Fetched', 'content_preview' => substr($text, 0, 100) . '...']
            ];

            return response()->json([
                'success' => true, 
                'links' => $links,
                'scraped_data' => $text 
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function storeSource(Request $request)
    {
        $request->validate([
            'type' => 'required|in:web,pdf,faq',
            'content' => 'required',
        ]);

        $user = getParentUser();
        $source = new AiSource();
        $source->user_id = $user->id; 
        $source->type = $request->type;
        // If it's a web scrape, we might want to store the URL in 'content' and the text in 'meta' or vice-versa?
        // Current Schema: `content` is longText. 
        // Let's store the actual text content there so RAG works easily. 
        // We'll store the Source Origin (URL/Filename) in meta.
        
        $source->content = $request->content; // This receives the scraped text or URL?
        // Wait, the frontend sends the URL in 'content' field for 'type=web'.
        // If we want RAG to work, we need to store the SCRAPED TEXT.
        // Let's adjust logic: 
        // 1. fetchUrl returns the text to Frontend.
        // 2. Frontend sends "URL" as 'content' and "Scraped Text" as a new field?
        // OR:
        // We handle scraping INSIDE storeSource? 
        // The UI flow suggests: Enter URL -> "Fetch & Save". 
        // The current JS does: fetchUrl is NOT called on "Fetch & Save" button submit?
        // Let's check the JS.
        
        // JS Check: 
        // $('#fetchUrlForm').on('submit', ... $.post("...store-source", { type: 'web', content: url } ...)
        // So the frontend just sends the URL to `storeSource`.
        // So `storeSource` should do the scraping.
        
        if($request->type == 'web'){
             try {
                $url = $request->content;
                // ... (existing crawling logic) ...
                $html = file_get_contents($url);
                $dom = new \DOMDocument();
                @$dom->loadHTML($html);
                $xpath = new \DOMXPath($dom);
                foreach ($xpath->query('//script|//style') as $node) { $node->parentNode->removeChild($node); }
                $text = trim($dom->textContent);
                $text = preg_replace('/\s+/', ' ', $text);
                
                $source->content = $text; // Store the KNOWLEDGE
                $source->meta = json_encode(['source_url' => $url]);
                $source->status = 1; // Trained immediately
             } catch(\Exception $e){
                 return response()->json(['success' => false, 'message' => 'Failed to crawl URL: ' . $e->getMessage()]);
             }
        } elseif ($request->type == 'pdf' && $request->hasFile('file')) {
            try {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                
                if (strtolower($extension) != 'pdf') {
                    return response()->json(['success' => false, 'message' => 'Only PDF files are allowed.']);
                }

                $filename = uniqid() . '_' . time() . '.' . $extension;
                $path = $file->storeAs('ai_sources/' . $user->id, $filename, 'public');
                
                $text = "";
                // Try to parse PDF if library exists
                if (class_exists('Smalot\PdfParser\Parser')) {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile(storage_path('app/public/' . $path));
                    $text = $pdf->getText();
                } else {
                    // Fallback or just store path
                    $text = "PDF Content (Parser not installed): " . $file->getClientOriginalName(); 
                    // Note: Real text extraction needs the library.
                }

                $source->content = $text ?: "No readable text extracted.";
                $source->meta = json_encode([
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path
                ]);
                $source->status = !empty($text) ? 1 : 0;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File Upload Error: ' . $e->getMessage()]);
            }
        } else {
             $source->content = $request->content;
             $source->status = 0;
        }
        
        $source->save();

        return response()->json(['success' => true, 'message' => 'Source added and processed successfully']);
    }

    public function deleteSource($id)
    {
        $user = getParentUser();
        $source = AiSource::where('user_id', $user->id)->findOrFail($id);
        $source->delete();
        return response()->json(['success' => true, 'message' => 'Source deleted successfully']);
    }

    public function saveAgentConfig(Request $request)
    {
        $request->validate([
            'trigger_condition' => 'nullable|string|max:500',
            'waiting_message'   => 'nullable|string|max:500',
        ]);

        $user = getParentUser();

        $config = AiAgentConfig::updateOrCreate(
            ['user_id' => $user->id],
            [
                'trigger_condition' => $request->trigger_condition,
                'waiting_message'   => $request->waiting_message,
                'is_active'         => $request->is_active ? 1 : 0
            ]
        );

        $notify[] = ['success', 'Agent configuration updated successfully'];
        return back()->withNotify($notify);
    }

    public function chat(Request $request)
    {
        $request->validate([
             'message' => 'required|string',
             'history' => 'nullable|array', // Validate history array
        ]);
        
        $user = getParentUser();
        $assistant = AiAssistant::active()->first(); 
        
        if(!$assistant){
             return response()->json(['success' => false, 'response' => 'No active assistant found.']);
        }
        
        // Prepare Prompts
        $aiSetting = $user->aiSetting;
        $systemPrompt = $aiSetting ? $aiSetting->system_prompt : "You are a helpful assistant.";

        // --- SPLIT CHAT INSTRUCTION ---
        if ($aiSetting && $aiSetting->enable_split_chat) {
            $systemPrompt .= "\n\n[IMPORTANT MODE: NATURAL SPLIT CHAT ENABLED]\nYou are mimicking a human chatting on WhatsApp. You MUST split your response into multiple short bubbles instead of one long block. Use the tag [SPLIT] to separate these bubbles. \n\nRULES:\n1. Use [SPLIT] at least once in your response.\n2. Keep each part short and conversational.\n3. Example: 'Halo kak! [SPLIT] Salam kenal ya. [SPLIT] Ada yang bisa saya bantu?'";
        }
        // --- END SPLIT CHAT ---

        // --- RAG IMPLEMENTATION (Context Stuffing) ---
        // Fetch User's Knowledge Base (Websites & Files)
        $sources = AiSource::where('user_id', $user->id)
                    ->where('status', 1) // Only trained sources
                    ->take(5) // Limit to top 5 sources to save tokens
                    ->get();

        $contextData = "";
        foreach($sources as $source) {
            // Basic cleanup
            $cleanContent = substr(strip_tags($source->content), 0, 2000); // Limit each source to 2000 chars
            $contextData .= "- Source (" . $source->type . "): " . $cleanContent . "\n\n";
        }

        if(!empty($contextData)){
            $systemPrompt .= "\n\n# RELEVANT BUSINESS KNOWLEDGE / CONTEXT:\nUse the following information to answer the user's question. If the answer is found in this context, prioritize it above your general knowledge.\n\n" . $contextData;
        }
        
        // --- END RAG ---
        
        $response = "System: AI Provider not supported for chat yet.";
        $reply = null;

        // Instantiate AI Library based on Provider
        try {
            switch ($assistant->provider) {
                case '1':
                case 'openai':
                    $bot = new \App\Lib\AiAssistantLib\OpenAi();
                    break;
                case '3':
                case 'gemini':
                    $bot = new \App\Lib\AiAssistantLib\Gemini();
                    break;
                case 'deepseek':
                case '4': // Assuming ID might be 4
                    $bot = new \App\Lib\AiAssistantLib\Deepseek();
                    break;
                case 'zai':
                case '5': // Assuming ID might be 5
                    $bot = new \App\Lib\AiAssistantLib\Zai();
                    break;
                default:
                    throw new \Exception("Unsupported AI Provider: " . $assistant->provider);
            }
            
            // Pass history to the library if provided
            $history = $request->history ?? [];
            $reply = $bot->getAiReply($systemPrompt, $request->message, $history);

        } catch (\Exception $e) {
            $reply = ['success' => false, 'response' => $e->getMessage()];
        }
        
        if(isset($reply['success']) && $reply['success']){
            $response = $reply['response'];
        } else {
             $response = "AI Error: " . ($reply['response'] ?? 'Unknown error');
        }
        
        return response()->json([
            'success' => true,
            'response' => $response
        ]);
    }
}
