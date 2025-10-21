@extends('layouts.admin')

@section('content')
<x-ui.page-header :title="__('Opportunities')">
    <x-slot:name>actions</x-slot:name>
    <x-ui.button variant="primary" href="{{ route('admin.marketing.opportunities.create') }}">{{ __('New Opportunity') }}</x-ui.button>
</x-ui.page-header>

<x-ui.card>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Stage') }}</th>
                    <th>{{ __('Probability') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($opportunities as $opportunity)
                    <tr>
                        <td>{{ $opportunity->title }}</td>
                        <td>{{ $opportunity->customer?->name }}</td>
                        <td>{{ ucfirst($opportunity->stage) }}</td>
                        <td>{{ $opportunity->probability }}%</td>
                        <td>{{ number_format($opportunity->amount, 2) }} {{ $opportunity->currency }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.marketing.opportunities.edit', $opportunity) }}" class="btn btn-sm btn-light">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"><x-ui.empty :title="__('No opportunities.')" /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $opportunities->links() }}</div>
</x-ui.card>
@endsection
