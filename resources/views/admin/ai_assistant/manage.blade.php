@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="aiConfigTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="playground-tab" data-bs-toggle="tab" href="#playground" role="tab">Playground</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="sources-tab" data-bs-toggle="tab" href="#sources" role="tab">Sources</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="agents-tab" data-bs-toggle="tab" href="#agents" role="tab">Agents</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="channel-tab" data-bs-toggle="tab" href="#channel" role="tab">Channel</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-4" id="aiConfigTabContent">
                        
                        <!-- Playground Tab -->
                        <div class="tab-pane fade show active" id="playground" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8 mx-auto">
                                    <div class="chat-box border p-3 rounded" style="height: 400px; overflow-y: scroll; background: #f9f9f9;">
                                        <!-- Mock Chat History -->
                                        <div class="d-flex justify-content-start mb-3">
                                            <div class="bg-light p-2 rounded">Hello! How can I help you?</div>
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
                                <li class="nav-item">
                                    <a class="nav-link" id="faq-tab" data-bs-toggle="pill" href="#faq" role="tab">FAQ</a>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="sourcesSubTabContent">
                                <!-- Websites -->
                                <div class="tab-pane fade show active" id="website" role="tabpanel">
                                    <form id="fetchUrlForm">
                                        <div class="row mb-3">
                                            <div class="col-md-9">
                                                <input type="url" name="url" class="form-control" placeholder="https://example.com" required>
                                            </div>
                                            <div class="col-md-3">
                                                <button type="submit" class="btn btn--primary w-100">Fetch Links</button>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="table-responsive">
                                        <table class="table table--light style--two">
                                            <thead>
                                                <tr>
                                                    <th>Link</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic Content -->
                                                @foreach($assistant->sources->where('type', 'web') as $source)
                                                <tr>
                                                    <td>{{ $source->content }}</td>
                                                    <td><span class="badge badge--{{ $source->status ? 'success' : 'warning' }}">{{ $source->status ? 'Trained' : 'Untrained' }}</span></td>
                                                    <td><button class="btn btn-sm btn-outline--danger deleteSource" data-id="{{ $source->id }}"><i class="las la-trash"></i></button></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Documents -->
                                <div class="tab-pane fade" id="document" role="tabpanel">
                                    <div class="upload-dropzone border-dashed p-5 text-center">
                                        <i class="las la-cloud-upload-alt fa-3x text-muted"></i>
                                        <p class="mt-2">Drop PDF file here or browse</p>
                                        <input type="file" class="d-none" id="pdfUpload">
                                        <button class="btn btn-sm btn--primary" onclick="$('#pdfUpload').click()">Browse</button>
                                    </div>
                                     <div class="table-responsive mt-3">
                                        <table class="table table--light style--two">
                                            <thead>
                                                <tr>
                                                    <th>File Name</th>
                                                    <th>Size</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                 @foreach($assistant->sources->where('type', 'pdf') as $source)
                                                <tr>
                                                    <td>{{ $source->content }}</td>
                                                    <td>{{ @$source->meta['size'] }}</td>
                                                    <td><button class="btn btn-sm btn-outline--danger deleteSource" data-id="{{ $source->id }}"><i class="las la-trash"></i></button></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- FAQ -->
                                <div class="tab-pane fade" id="faq" role="tabpanel">
                                     <button class="btn btn--primary mb-3">Add New FAQ</button>
                                     <p class="text-muted">FAQ Management UI Placeholder</p>
                                </div>
                            </div>
                        </div>

                        <!-- Agents Tab -->
                        <div class="tab-pane fade" id="agents" role="tabpanel">
                            <form action="{{ route('admin.ai-assistant.config.save-agent-config', $assistant->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Activate Live Agents</label>
                                    <div class="form-check form-switch form--switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" {{ @$assistant->agentConfig->is_active ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Trigger Condition</label>
                                        <textarea name="trigger_condition" class="form-control" rows="5" placeholder="ONLY USE this intent if...">{{ @$assistant->agentConfig->trigger_condition }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Waiting Messages</label>
                                        <textarea name="waiting_message" class="form-control" rows="5" placeholder="Thank you for your patience...">{{ @$assistant->agentConfig->waiting_message }}</textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn--primary mt-3">Save Update</button>
                            </form>
                        </div>

                        <!-- Channel Tab -->
                        <div class="tab-pane fade" id="channel" role="tabpanel">
                             <div class="row">
                                <div class="col-md-6">
                                    <h5>Provider Configuration</h5>
                                    <!-- Re-implement config form here -->
                                     <form action="{{ route('admin.ai-assistant.configure', $assistant->id) }}" method="POST">
                                        @csrf
                                        @foreach($assistant->config as $key => $value)
                                        <div class="form-group">
                                            <label>{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                                            <input type="text" name="{{ $key }}" class="form-control" value="{{ $value }}">
                                        </div>
                                        @endforeach
                                        <button type="submit" class="btn btn--primary">Save Configuration</button>
                                     </form>
                                </div>
                            </div>
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
        // Fetch URL logic
        $('#fetchUrlForm').on('submit', function(e){
            e.preventDefault();
            let url = $(this).find('input[name="url"]').val();
            // Implement AJAX call to fetchUrl route
            $.post("{{ route('admin.ai-assistant.config.fetch-url', $assistant->id) }}", {
                _token: "{{ csrf_token() }}",
                url: url
            }, function(resp){
                if(resp.success){
                    notify('success', 'Links fetched successfully (Mock)');
                    // Reload or append to table
                }
            });
            // Also call storeSource to save it
             $.post("{{ route('admin.ai-assistant.config.store-source', $assistant->id) }}", {
                _token: "{{ csrf_token() }}",
                type: 'web',
                content: url
            }, function(resp){
                if(resp.success){
                    location.reload();
                }
            });
        });

        $('.deleteSource').on('click', function(){
            let id = $(this).data('id');
            $.post("{{ route('admin.ai-assistant.config.delete-source', ':id') }}".replace(':id', id), {
                _token: "{{ csrf_token() }}"
            }, function(resp){
                 if(resp.success){
                    notify('success', resp.message);
                    location.reload();
                }
            });
        });

        // Chat logic
        function appendMessage(message, isUser = false) {
            let userHtml = `<div class="d-flex justify-content-end mb-3">
                                <div class="bg--primary text-white p-2 rounded">${message}</div>
                            </div>`;
            let botHtml = `<div class="d-flex justify-content-start mb-3">
                                <div class="bg-light p-2 rounded">${message}</div>
                           </div>`;
            $('.chat-box').append(isUser ? userHtml : botHtml);
            $('.chat-box').scrollTop($('.chat-box')[0].scrollHeight);
        }

        $('#sendBtn').on('click', function() {
            let msg = $('#chatInput').val();
            if(!msg) return;

            appendMessage(msg, true);
            $('#chatInput').val('');

            $.post("{{ route('admin.ai-assistant.config.chat', $assistant->id) }}", {
                _token: "{{ csrf_token() }}",
                message: msg
            }, function(resp){
                if(resp.success){
                    appendMessage(resp.response, false);
                }
            });
        });

    })(jQuery);
</script>
@endpush
