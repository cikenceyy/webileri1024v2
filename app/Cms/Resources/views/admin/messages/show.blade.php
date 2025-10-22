@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        <h1 class="mb-4">{{ $message->subject }}</h1>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Ad Soyad</dt>
                    <dd class="col-sm-9">{{ $message->name }}</dd>
                    <dt class="col-sm-3">E-posta</dt>
                    <dd class="col-sm-9">{{ $message->email }}</dd>
                    <dt class="col-sm-3">Gönderim Tarihi</dt>
                    <dd class="col-sm-9">{{ $message->created_at->format('d.m.Y H:i') }}</dd>
                    <dt class="col-sm-3">IP</dt>
                    <dd class="col-sm-9">{{ $message->ip }}</dd>
                    <dt class="col-sm-3">User Agent</dt>
                    <dd class="col-sm-9 text-break">{{ $message->user_agent }}</dd>
                </dl>
                <hr>
                <h5>Mesaj</h5>
                <p class="white-space-pre-wrap">{{ $message->message }}</p>
            </div>
        </div>
        <a href="{{ route('cms.admin.messages.index') }}" class="btn btn-link mt-3">Mesaj listesine dön</a>
    </div>
@endsection
