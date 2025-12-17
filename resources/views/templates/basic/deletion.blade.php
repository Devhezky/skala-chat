@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card custom--card">
                    <div class="card-header">
                        <h5 class="card-title">@lang('Data Deletion Instructions')</h5>
                    </div>
                    <div class="card-body">
                        <p>@lang('According to the Facebook Platform rules, we have to provide User Data Deletion Callback URL or Data Deletion Instructions URL. If you want to delete your activities for the App, you can remove your information by following these steps:')</p>
                        
                        <ol class="mt-3">
                            <li>@lang('Go to your Facebook Account\'s Setting & Privacy. Click "Settings".')</li>
                            <li>@lang('Look for "Security and Login" and then click "Apps and Websites".')</li>
                            <li>@lang('See all of the apps and websites you linked with your Facebook.')</li>
                            <li>@lang('Search and Click "'){{ gs('site_name') }}@lang('".')</li>
                            <li>@lang('Scroll and click "Remove".')</li>
                            <li>@lang('Congratulations, you have successfully removed your app activities.')</li>
                        </ol>

                        <div class="mt-4">
                            <h6>@lang('Direct Request')</h6>
                            <p>@lang('If you wish to delete your user account data permanently from our system, please send an email request to:')</p>
                            <a href="mailto:support@narapatistudio.com" class="fw-bold">support@narapatistudio.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
