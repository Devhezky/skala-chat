@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="dashboard-container">
        <div class="container-top">
            <div class="container-top__left">
                <h5 class="container-top__title">{{ __(@$pageTitle) }}</h5>
                <p class="container-top__desc">
                    @lang('Connect your WhatsApp Business Account easily using Facebook Login.')
                </p>
            </div>
            <div class="container-top__right">
                <div class="btn--group">
                    <a href="{{ route('user.whatsapp.account.index') }}" class="btn btn--dark"><i class="las la-undo"></i>
                        @lang('Back')</a>
                </div>
            </div>
        </div>
        <div class="dashboard-container__body">
            <div class="row gy-4 justify-content-center">
                <div class="col-md-8 text-center">
                    <div class="card p-5">
                        <div class="mb-4">
                            <i class="fab fa-whatsapp text--success" style="font-size: 64px;"></i>
                        </div>
                        <h3>@lang('Connect WhatsApp Business')</h3>
                        <p class="mb-4">@lang('Click the button below to log in with Facebook and select your WhatsApp Business Account.')</p>
                        
                        <!-- Facebook Login Button -->
                        <button onclick="launchWhatsAppSignup()" class="btn btn--primary btn-lg w-100" style="max-width: 300px;">
                            <i class="fab fa-facebook me-2"></i> @lang('Connect with Facebook')
                        </button>

                        <div id="status-message" class="mt-4 text--muted" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> @lang('Processing...')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('topbar_tabs')
    @include('Template::partials.profile_tab')
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            // No custom script needed here
        })(jQuery);

        // --- Facebook Embedded Signup Configuration ---
        const fbAppId = '{{ gs("meta_app_id") }}';
        const fbConfigId = '{{ gs("meta_configuration_id") }}';

        window.fbAsyncInit = function() {
            FB.init({
                appId            : fbAppId,
                autoLogAppEvents : true,
                xfbml            : true,
                version          : 'v19.0'
            });
        };

        // Load the SDK asynchronously
        (function(d, s, id){
             var js, fjs = d.getElementsByTagName(s)[0];
             if (d.getElementById(id)) {return;}
             js = d.createElement(s); js.id = id;
             js.src = "https://connect.facebook.net/en_US/sdk.js";
             fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));


        // Main Launch Function
        function launchWhatsAppSignup() {
            if(!fbAppId || !fbConfigId) {
                notify('error', 'Meta App ID or Configuration ID is missing in system settings.');
                return;
            }

            // Start Facebook Login with Config ID (Embedded Signup Flow)
            FB.login(function(response) {
                if (response.authResponse) {
                    const code = response.authResponse.code;
                    console.log('FB Login Success, Code obtained:', code);
                    
                    // We have the code, now we wait for the message event 
                    // OR if the flow is different, we might just send the code.
                    // But for Embedded Signup, the popup usually sends data back via window.postMessage
                } else {
                    console.log('User cancelled login or did not fully authorize.');
                }
            }, {
                config_id: fbConfigId,
                response_type: 'code',
                override_default_response_type: true,
                extras: {
                    feature: 'whatsapp_embedded_signup',
                    sessionInfoVersion: '3',
                }
            });
        }

        // Listen for data from the Embedded Signup popup
        window.addEventListener('message', (event) => {
            if (event.origin !== "https://www.facebook.com" && event.origin !== "https://web.facebook.com") {
                return;
            }
            
            try {
                const data = JSON.parse(event.data);
                
                if (data.type === 'WA_EMBEDDED_SIGNUP') {
                    console.log('Embedded Signup Data:', data);
                    
                    if (data.data.current_step === 'FINISH') {
                        const { waba_id, phone_number_id } = data.data;
                        const accessToken = FB.getAuthResponse()?.accessToken; // Short lived token?
                        // Actually we need the 'code' from the earlier FB.login response to exchange for long-lived system user token.
                        // But FB.login callback might have fired already.
                        
                        // We will rely on getting the code from FB.login response.
                        // WAIT: FB.login callback fires when the popup closes.
                        // The 'message' event fires BEFORE the popup closes (when user clicks Finish).
                        
                        // Strategy: We store the IDs in a variable, and when FB.login callback fires, we send everything.
                        // OR: We check if we can get the code here.
                        
                        // Let's use a simpler approach: 
                        // The backend needs 'code' to generate access token.
                        // It can then fetch WABA info. 
                        // BUT providing waba_id helps filter/target if multiple exist.
                        
                        $('#status-message').show().html('<i class="fas fa-spinner fa-spin"></i> Processing connection...');
                        
                        // We need to wait for the FB.login callback to get the CODE.
                        // The popup closes after this event.
                        window.tempWabaData = {
                            waba_id: waba_id,
                            phone_number_id: phone_number_id
                        };
                    }
                }
            } catch (e) {
                // Ignore non-JSON
            }
        });
        
        // Overwrite the FB.login callback logic to include sending data
        function launchWhatsAppSignup() {
             if(!fbAppId || !fbConfigId) {
                notify('error', 'Meta App ID or Configuration ID is missing in system settings.');
                return;
            }

            FB.login(function(response) {
                if (response.authResponse) {
                    const code = response.authResponse.code;
                    // Check if we captured WABA data from the event listener
                    const wabaData = window.tempWabaData || {};
                    
                    $('#status-message').show().html('<i class="fas fa-spinner fa-spin"></i> verifying credentials...');

                    // Send to backend
                    $.ajax({
                        url: "{{ route('user.whatsapp.account.embedded.signup') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            code: code, // The OAuth code
                            waba_id: wabaData.waba_id, 
                            phone_number_id: wabaData.phone_number_id
                        },
                        success: function(resp) {
                            if(resp.success) {
                                notify('success', resp.message);
                                setTimeout(() => {
                                    window.location.href = "{{ route('user.whatsapp.account.index') }}";
                                }, 1500);
                            } else {
                                notify('error', resp.message || 'Connection failed');
                                $('#status-message').hide();
                            }
                        },
                        error: function(err) {
                            notify('error', 'Something went wrong');
                            $('#status-message').hide();
                        }
                    });

                } else {
                    console.log('User cancelled login or did not fully authorize.');
                }
            }, {
                config_id: fbConfigId,
                response_type: 'code',
                override_default_response_type: true
            });
        }
    </script>
