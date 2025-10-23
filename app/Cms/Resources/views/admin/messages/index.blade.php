@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="mb-1">{{ __('Contact Messages') }}</h1>
                <p class="text-muted small mb-0">{{ __('Track enquiries, mark responses and keep your team in sync.') }}</p>
            </div>
            <form method="GET" class="row gy-2 gx-2 align-items-end" role="search">
                <div class="col-12 col-sm-4">
                    <label class="form-label small text-muted">{{ __('Search') }}</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="{{ __('Name, email or subject') }}">
                </div>
                <div class="col-6 col-sm-2">
                    <label class="form-label small text-muted">{{ __('From') }}</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                </div>
                <div class="col-6 col-sm-2">
                    <label class="form-label small text-muted">{{ __('To') }}</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                </div>
                <div class="col-6 col-sm-2">
                    <label class="form-label small text-muted">{{ __('Status') }}</label>
                    <select class="form-select" name="status">
                        <option value="">{{ __('All') }}</option>
                        <option value="new" @selected(($filters['status'] ?? null) === 'new')>{{ __('New') }}</option>
                        <option value="read" @selected(($filters['status'] ?? null) === 'read')>{{ __('Read') }}</option>
                        <option value="responded" @selected(($filters['status'] ?? null) === 'responded')>{{ __('Responded') }}</option>
                    </select>
                </div>
                <div class="col-6 col-sm-2">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="unread" value="1" id="unreadCheck" @checked(($filters['unread'] ?? false))>
                        <label class="form-check-label" for="unreadCheck">{{ __('Only unread') }}</label>
                    </div>
                </div>
                <div class="col-12 col-sm-2 d-flex justify-content-end">
                    <button class="btn btn-primary w-100" type="submit">{{ __('Filter') }}</button>
                </div>
            </form>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Subject') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Received') }}</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                            <tr @class(['fw-semibold' => !$message->is_read])>
                                <td>{{ $message->name }}</td>
                                <td>{{ $message->email }}</td>
                                <td>{{ $message->subject }}</td>
                                <td>
                                    @if($message->status === 'responded')
                                        <span class="badge bg-success-subtle text-success">{{ __('Responded') }}</span>
                                    @elseif($message->status === 'read')
                                        <span class="badge bg-primary-subtle text-primary">{{ __('Read') }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ __('New') }}</span>
                                    @endif
                                </td>
                                <td>{{ $message->created_at->format('d.m.Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('cms.admin.messages.show', $message) }}" class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">{{ __('No messages found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="text-muted small">{{ trans_choice(':count message|:count messages', $messages->total(), ['count' => $messages->total()]) }}</span>
                {{ $messages->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
