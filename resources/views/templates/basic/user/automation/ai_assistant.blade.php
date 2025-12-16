@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body">
                    
                    @if ($activeAiAssistant)
                     <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4>{{ __(@$pageTitle) }}</h4>
                        <span class="badge badge--success">{{ __($activeAiAssistant->name) }} is Active</span>
                     </div>
                    @else
                     <div class="alert alert--danger">@lang('No Active AI Assistant found. Please contact admin.')</div>
                    @endif

                    <ul class="nav nav-tabs" id="aiTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="config-tab" data-bs-toggle="tab" href="#config" role="tab">Configuration</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="playground-tab" data-bs-toggle="tab" href="#playground" role="tab">Playground</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="sources-tab" data-bs-toggle="tab" href="#sources" role="tab">Sources</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="agents-tab" data-bs-toggle="tab" href="#agents" role="tab">Agents</a>
                        </li>
                    </ul>

                    <div class="tab-content mt-4" id="aiTabContent">
                        
                        <!-- Configuration Tab (Existing System Prompt) -->
                        <div class="tab-pane fade show active" id="config" role="tabpanel">
                            <form action="{{ route('user.automation.ai.assistant.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>@lang('System Prompt') <i class="fas fa-info-circle text--info" title="@lang('Instruksi utama untuk AI. Jelaskan peran, gaya bahasa, dan batasan AI di sini.')"></i></label>
                                    <textarea name="system_prompt" class="form-control" rows="10" required>{{ old('system_prompt', @$aiSetting->system_prompt) }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>@lang('Fallback Response') <i class="fas fa-info-circle text--info" title="@lang('Jawaban default jika AI gagal memproses permintaan atau error.')"></i></label>
                                    <textarea name="fallback_response" class="form-control" rows="3" required>{{ old('fallback_response', @$aiSetting->fallback_response) }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Max Length') <i class="fas fa-info-circle text--info" title="@lang('Maksimal jumlah kata/token dalam satu jawaban AI.')"></i></label>
                                            <input type="number" name="max_length" class="form-control" value="{{ old('max_length', @$aiSetting->max_length) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Status') <i class="fas fa-info-circle text--info" title="@lang('Aktifkan atau nonaktifkan asisten AI ini.')"></i></label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="status" @checked(@$aiSetting->status)>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Enable Natural Split Chat') <i class="fas fa-info-circle text--info" title="@lang('Jika aktif, AI akan memecah jawaban panjang menjadi beberapa pesan pendek dengan jeda waktu ringkas.')"></i></label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="enable_split_chat" @checked(@$aiSetting->enable_split_chat)>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Max History Limit') <small>(Max 250)</small> <i class="fas fa-info-circle text--info" title="@lang('Jumlah pesan terakhir yang diingat oleh AI dalam satu sesi percakapan.')"></i></label>
                                            <input type="number" name="max_history_limit" class="form-control" value="{{ old('max_history_limit', @$aiSetting->max_history_limit ?? 10) }}" min="1" max="250">
                                        </div>
                                    </div>
                                    <div class="col-md-12 border-top pt-3 mt-3">
                                        <h5>@lang('Response Delay Settings') <small class="text-muted" style="font-size: 0.7em">(Simulate human typing)</small> <i class="fas fa-info-circle text--info" title="@lang('Pengaturan jeda waktu sebelum AI mengirim balasan.')"></i></h5>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>@lang('Delay Type') <i class="fas fa-info-circle text--info" title="@lang('Pilih \'Fixed Range\' untuk jeda acak tetap, atau \'Smart Typing Speed\' untuk jeda berdasarkan panjang teks.')"></i></label>
                                            <select name="delay_type" class="form-control">
                                                <option value="fixed" @selected(@$aiSetting->delay_type == 'fixed')>@lang('Fixed Range')</option>
                                                <option value="smart" @selected(@$aiSetting->delay_type == 'smart')>@lang('Smart Typing Speed')</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>@lang('Min Delay (Sec)') <i class="fas fa-info-circle text--info" title="@lang('Batas minimum waktu tunggu (detik).')"></i></label>
                                            <input type="number" name="min_delay" class="form-control" value="{{ old('min_delay', @$aiSetting->min_delay ?? 3) }}" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>@lang('Max Delay (Sec)') <i class="fas fa-info-circle text--info" title="@lang('Batas maksimum waktu tunggu (detik).')"></i></label>
                                            <input type="number" name="max_delay" class="form-control" value="{{ old('max_delay', @$aiSetting->max_delay ?? 5) }}" min="0">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn--primary">@lang('Save Settings')</button>
                            </form>
                        </div>

                         <!-- Playground Tab -->
                        <div class="tab-pane fade" id="playground" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8 mx-auto">
                                    <div class="chat-box border p-3 rounded" style="height: 400px; overflow-y: scroll; background: #f9f9f9;">
                                        <div class="d-flex justify-content-start mb-3">
                                            <div class="bg-light p-2 rounded">Hello! I am ready to help.</div>
                                        </div>
                                    </div>
                                    <div class="input-group mt-3">
                                        <input type="text" id="chatInput" class="form-control" placeholder="Type a message...">
                                        <button class="btn btn--primary" id="sendBtn">Send</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sources Tab -->
                        <div class="tab-pane fade" id="sources" role="tabpanel">
                           <ul class="nav nav-pills mb-3" id="sourcesSubTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="website-tab" data-bs-toggle="pill" href="#website" role="tab">Websites</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="document-tab" data-bs-toggle="pill" href="#document" role="tab">Documents</a>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="sourcesSubTabContent">
                                <div class="tab-pane fade show active" id="website" role="tabpanel">
                                    <form id="fetchUrlForm">
                                        <div class="row mb-3">
                                            <div class="col-md-9">
                                                <input type="url" name="url" class="form-control" placeholder="https://example.com" required>
                                            </div>
                                            <div class="col-md-3">
                                                <button type="submit" class="btn btn--primary w-100">Fetch & Save</button>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="table-responsive">
                                        <table class="table table--light style--two">
                                            <thead>
                                                <tr>
                                                    <th>Source</th>
                                                    <th>Content Preview</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                 @if(isset($sources))
                                                    @foreach($sources->where('type', 'web') as $source)
                                                    <tr>
                                                        <td>
                                                            @php $meta = json_decode($source->meta); @endphp
                                                            <a href="{{ @$meta->source_url }}" target="_blank">{{ Str::limit(@$meta->source_url, 30) }}</a>
                                                        </td>
                                                        <td>
                                                            {{ Str::limit($source->content, 50) }}
                                                            <div id="source-content-{{ $source->id }}" class="d-none">{{ $source->content }}</div>
                                                        </td>
                                                        <td><span class="badge badge--{{ $source->status ? 'success' : 'warning' }}">{{ $source->status ? 'Trained' : 'Untrained' }}</span></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline--info viewSourceBtn" data-id="{{ $source->id }}"><i class="las la-eye"></i></button>
                                                            <button class="btn btn-sm btn-outline--danger deleteSource" data-id="{{ $source->id }}"><i class="las la-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- View Content Modal -->
                                <div class="modal fade" id="viewSourceModal" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Crawled Content</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Full Content:</label>
                                                    <textarea id="modalSourceContent" class="form-control" rows="15" readonly></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="document" role="tabpanel">
                                     <div class="upload-dropzone border-dashed p-5 text-center">
                                        <i class="las la-cloud-upload-alt fa-3x text-muted"></i>
                                        <p class="mt-2">Drop PDF file here or browse (Mock)</p>
                                        <input type="file" class="d-none" id="pdfUpload">
                                        <button class="btn btn-sm btn--primary" onclick="$('#pdfUpload').click()">Browse</button>
                                    </div>
                                     <div class="table-responsive mt-3">
                                        <table class="table table--light style--two">
                                            <thead>
                                                <tr>
                                                    <th>File Name</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(isset($sources))
                                                    @foreach($sources->where('type', 'pdf') as $source)
                                                    <tr>
                                                        <td>{{ $source->content }}</td>
                                                        <td><button class="btn btn-sm btn-outline--danger deleteSource" data-id="{{ $source->id }}"><i class="las la-trash"></i></button></td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- Agents Tab -->
                        <div class="tab-pane fade" id="agents" role="tabpanel">
                             <form action="{{ route('user.automation.ai.assistant.save-agent-config') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Activate Live Agents</label>
                                    <div class="form-check form-switch form--switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" {{ @$agentConfig->is_active ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Trigger Condition</label>
                                        <textarea name="trigger_condition" class="form-control" rows="5" placeholder="ONLY USE this intent if...">{{ @$agentConfig->trigger_condition }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Waiting Messages</label>
                                        <textarea name="waiting_message" class="form-control" rows="5" placeholder="Thank you for your patience...">{{ @$agentConfig->waiting_message }}</textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn--primary mt-3">Save Update</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    "use strict";
    (function($) {
        
        // --- Sources Logic ---
        $('#fetchUrlForm').on('submit', function(e){
            e.preventDefault();
            let url = $(this).find('input[name="url"]').val();
             $.post("{{ route('user.automation.ai.assistant.store-source') }}", {
                _token: "{{ csrf_token() }}",
                type: 'web',
                content: url
            }, function(resp){
                if(resp.success){
                    notify('success', resp.message);
                    location.reload();
                }
            });
        });

        $('.deleteSource').on('click', function(){
            let id = $(this).data('id');
            $.post("{{ route('user.automation.ai.assistant.delete-source', ':id') }}".replace(':id', id), {
                _token: "{{ csrf_token() }}"
            }, function(resp){
                 if(resp.success){
                    notify('success', resp.message);
                    reloadWithTab('#sources');
                }
            });
        });

        // --- View Source Logic ---
        $(document).on('click', '.viewSourceBtn', function(){
            let id = $(this).data('id');
            let content = $('#source-content-' + id).text();
            $('#modalSourceContent').val(content);
            $('#viewSourceModal').modal('show');
        });

        // --- File Upload Logic ---
        $('#pdfUpload').on('change', function(){
            let formData = new FormData();
            formData.append('file', $(this)[0].files[0]);
            formData.append('type', 'pdf');
            formData.append('content', 'PDF File'); // Placeholder
            formData.append('_token', "{{ csrf_token() }}");

            notify('info', 'Uploading and processing file...');
            
            $.ajax({
                url: "{{ route('user.automation.ai.assistant.store-source') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(resp){
                    if(resp.success){
                        notify('success', resp.message);
                        location.reload();
                    } else {
                        notify('error', resp.message);
                    }
                },
                error: function(xhr){
                    notify('error', 'Upload failed');
                }
            });
        });

        // --- Chat Logic ---
        let chatHistory = []; // Client-side memory
        const MAX_HISTORY = {{ @$aiSetting->max_history_limit ?? 10 }};
        const DELAY_TYPE = "{{ @$aiSetting->delay_type ?? 'fixed' }}";
        const MIN_DELAY  = {{ @$aiSetting->min_delay ?? 3 }} * 1000;
        const MAX_DELAY  = {{ @$aiSetting->max_delay ?? 5 }} * 1000;

        function appendMessage(message, isUser = false) {
            let userHtml = `<div class="d-flex justify-content-end mb-3">
                                <div class="bg--primary text-white p-3 rounded-pill" style="max-width: 75%; border-bottom-right-radius: 0 !important;">${message}</div>
                            </div>`;
            let botHtml = `<div class="d-flex justify-content-start mb-3">
                                <div class="p-3 rounded-pill" style="background-color: #e9ecef; color: #333; max-width: 75%; border-bottom-left-radius: 0 !important;">${message}</div>
                           </div>`;
            $('.chat-box').append(isUser ? userHtml : botHtml);
            $('.chat-box').scrollTop($('.chat-box')[0].scrollHeight);
        }
        
        function showTyping() {
            let typingHtml = `<div class="d-flex justify-content-start mb-3" id="typingIndicator">
                                <div class="p-3 rounded-pill" style="background-color: #e9ecef; color: #666; font-style: italic;">
                                    <small>AI is typing...</small>
                                </div>
                              </div>`;
            $('.chat-box').append(typingHtml);
            $('.chat-box').scrollTop($('.chat-box')[0].scrollHeight);
        }
        
        function hideTyping() {
            $('#typingIndicator').remove();
        }
        
        function calculateDelay(text) {
             if(DELAY_TYPE === 'smart') {
                 // Avg typing speed: 300 CPM (~5 chars/sec)
                 let charCount = text.length;
                 let delay = (charCount / 300) * 60 * 1000; 
                 // Clamp between min and max settings as boundaries
                 return Math.max(MIN_DELAY, Math.min(delay, MAX_DELAY * 2)); // Allow slightly longer for very long texts
             } else {
                 // Fixed Random
                 return Math.floor(Math.random() * (MAX_DELAY - MIN_DELAY + 1)) + MIN_DELAY;
             }
        }

        $('#sendBtn').on('click', function() {
            let msg = $('#chatInput').val();
            if(!msg) return;

            appendMessage(msg, true);
            $('#chatInput').val('');

            // Add to history
            chatHistory.push({ role: 'user', content: msg });

            // Keep only last N messages to avoid token limits
            if (chatHistory.length > MAX_HISTORY) {
                 chatHistory = chatHistory.slice(-MAX_HISTORY);
            }
            
            showTyping(); // Show initial typing

            $.post("{{ route('user.automation.ai.assistant.chat') }}", {
                _token: "{{ csrf_token() }}",
                message: msg,
                history: chatHistory // Send history
            }, function(resp){
                if(resp.success){
                    let responseText = resp.response;

                    if (responseText.includes('[SPLIT]')) {
                        let parts = responseText.split('[SPLIT]');
                        hideTyping();
                        processSplitMessages(parts, 0);
                        
                        // Add full response to history (clean)
                        chatHistory.push({ role: 'assistant', content: responseText.replace(/\[SPLIT\]/g, ' ') });
                    } else {
                        // Calculate normal delay
                        let delay = calculateDelay(responseText);
                        setTimeout(function(){
                            hideTyping();
                            appendMessage(responseText, false);
                            // Add bot response to history
                            chatHistory.push({ role: 'assistant', content: responseText });
                        }, delay);
                    }
                } else {
                    hideTyping();
                    alert(resp.message);
                }
            });
        });

        function processSplitMessages(parts, index) {
            if (index >= parts.length) {
                hideTyping();
                return;
            }

            let part = parts[index].trim();
            
            // Show message
            if(part) {
                appendMessage(part, false);
            }

            if (index + 1 < parts.length) {
                // Calculate delay for NEXT message
                let nextPart = parts[index+1].trim();
                let delay = calculateDelay(nextPart);
                
                showTyping();
                setTimeout(function() {
                     hideTyping();
                    processSplitMessages(parts, index + 1);
                }, delay); 
            }
        }

        // Send on Enter Key
        $('#chatInput').on('keypress', function (e) {
            if(e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                $('#sendBtn').click();
            }
        });

        // Maintain Active Tab on Reload
        $(document).ready(function(){
            // Check if there is a hash in the URL
            var hash = window.location.hash;
            if (hash) {
                // Show the tab corresponding to the hash
                $('.nav-tabs a[href="' + hash + '"]').tab('show');
            }
            
            // Update hash when tab is changed
            $('.nav-tabs a').on('shown.bs.tab', function (e) {
                window.location.hash = e.target.hash;
            });
        });

    })(jQuery);

    function reloadWithTab(tabHash) {
        window.location.hash = tabHash;
        location.reload();
    }

</script>
@endpush
