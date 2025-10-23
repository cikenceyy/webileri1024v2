@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <a href="{{ route('cms.admin.messages.index') }}" class="btn btn-link px-0">&larr; {{ __('Back to list') }}</a>
                <h1 class="h3 mb-1">{{ $message->subject }}</h1>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="text-muted small">{{ $message->created_at->format('d.m.Y H:i') }}</span>
                    @if($message->status === 'responded')
                        <span class="badge bg-success-subtle text-success">{{ __('Responded') }}</span>
                    @elseif($message->status === 'read')
                        <span class="badge bg-primary-subtle text-primary">{{ __('Read') }}</span>
                    @else
                        <span class="badge bg-warning text-dark">{{ __('New') }}</span>
                    @endif
                </div>
            </div>
            <div class="btn-group" role="group" aria-label="Message actions">
                <form method="POST" action="{{ route('cms.admin.messages.update', $message) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="responded">
                    <button class="btn btn-success" type="submit">{{ __('Mark responded') }}</button>
                </form>
                <form method="POST" action="{{ route('cms.admin.messages.update', $message) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="read">
                    <button class="btn btn-outline-primary" type="submit">{{ __('Mark read') }}</button>
                </form>
                <form method="POST" action="{{ route('cms.admin.messages.update', $message) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="new">
                    <button class="btn btn-outline-secondary" type="submit">{{ __('Mark unread') }}</button>
                </form>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">{{ __('Name') }}</dt>
                    <dd class="col-sm-9">{{ $message->name }}</dd>
                    <dt class="col-sm-3">{{ __('Email') }}</dt>
                    <dd class="col-sm-9"><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></dd>
                    <dt class="col-sm-3">{{ __('IP address') }}</dt>
                    <dd class="col-sm-9">{{ $message->ip }}</dd>
                    <dt class="col-sm-3">{{ __('User agent') }}</dt>
                    <dd class="col-sm-9 text-break">{{ $message->user_agent }}</dd>
                    <dt class="col-sm-3">{{ __('Read at') }}</dt>
                    <dd class="col-sm-9">{{ optional($message->read_at)->format('d.m.Y H:i') ?? __('Not yet') }}</dd>
                    <dt class="col-sm-3">{{ __('Responded at') }}</dt>
                    <dd class="col-sm-9">{{ optional($message->responded_at)->format('d.m.Y H:i') ?? __('Not yet') }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">{{ __('Message') }}</h5>
                <p class="white-space-pre-wrap mb-0">{{ $message->message }}</p>
            </div>
        </div>
    </div>
@endsection
