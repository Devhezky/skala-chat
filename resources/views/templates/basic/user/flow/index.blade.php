@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="dashboard-container">
        <div class="container-top">
            <div class="container-top__left">
                <h5 class="container-top__title">{{ __(@$pageTitle) }}</h5>
                <p class="container-top__desc">@lang('Create and manage your Automation Flow Builder to create and manage structured conversation flows for WhatsApp Business accounts.')</p>
            </div>
            <x-permission_check permission="add flow builder">
                <div class="container-top__right">
                    <div class="btn--group">
                        <a href="{{ route('user.flow.builder.create') }}" class="btn btn--base btn-shadow add-btn">
                            <i class="las la-plus"></i>
                            @lang('Add New')
                        </a>
                    </div>
                </div>
            </x-permission_check>
        </div>
        <div class="dashboard-container__body">
            <div class="body-top">
                <div class="body-top__left">
                    <form class="search-form">
                        <input type="search" class="form--control" placeholder="@lang('Search here')..." name="search"
                            value="{{ request()->search }}">
                        <span class="search-form__icon"> <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                    </form>
                </div>
            </div>
            <div class="dashboard-table">
                <table class="table table--responsive--md">
                    <thead>
                        <tr>
                            <th>@lang('Name')</th>
                            <th>@lang('Trigger Type')</th>
                            <th>@lang('Created At')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($flows as $flow)
                            <tr>
                                <td>{{ __(@$flow->name) }}</td>
                                <td>{{ @$flow->getTriggerType }}</td>
                                <td>{{ showDateTime(@$flow->created_at) }} </td>
                                <td>
                                    @if (auth()->user()->hasAgentPermission('edit flow builder'))
                                        <div class="form--switch two">
                                            <input class="form-check-input status-switch" data-id="{{ $flow->id }}"
                                                type="checkbox" role="switch" @checked(@$flow->status)>
                                        </div>
                                    @else
                                        @php
                                            echo $flow->statusBadge;
                                        @endphp
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <x-permission_check permission="edit flow builder">
                                            <a class="text--info cursor-pointer"
                                                href="{{ route('user.flow.builder.edit', $flow->id) }}"
                                                data-bs-toggle="tooltip" data-bs-title="@lang('Edit')">
                                                <i class="las la-edit fs-16"></i>
                                            </a>
                                        </x-permission_check>
                                        <x-permission_check permission="delete flow builder">
                                            <button type="button" class="text--danger confirmationBtn"
                                                data-bs-toggle="tooltip" data-bs-title="@lang('Delete')"
                                                data-action="{{ route('user.flow.builder.delete', $flow->id) }}"
                                                data-question="@lang('Are you sure to delete this Flow?')">
                                                <i class="las la-trash fs-16"></i>
                                            </button>
                                        </x-permission_check>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @include('Template::partials.empty_message')
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ paginateLinks($flows) }}
        </div>
    </div>
    <x-confirmation-modal isFrontend="true" />
@endsection

@push('script')
    <script>
        "use strict";
        (function($) {
            $('.status-switch').on('change', function() {
                let route = "{{ route('user.flow.builder.status', ':id') }}";
                let flowId = $(this).data('id');
                $.ajax({
                    url: route.replace(':id', flowId),
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        notify(response.status, response.message);
                    }
                });
            })
        })(jQuery);
    </script>
@endpush
