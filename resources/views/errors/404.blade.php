@extends('layouts.auth')

@section('title', 'Sayfa bulunamadı')
@push('meta')
    <meta name="robots" content="noindex">
@endpush

@php
    use Illuminate\Support\Str;

    $errorId = $errorId ?? Str::upper(Str::ulid());
@endphp

@section('content')
    <x-ui-error
        code="404"
        title="Aradığınız sayfayı bulamadık"
        description="Adres değişmiş veya sayfa kaldırılmış olabilir."
        hint="Yukarıdaki hata kodunu destek ekibimizle paylaşırsanız yardımcı olabiliriz."
        :meta="[
            'Hata kodu' => $errorId,
            'İstek yolu' => $requestPath ?? request()->path(),
        ]"
    >
        <x-ui-button tag="button" type="button" variant="ghost" data-action="history-back">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            <span>Geri</span>
        </x-ui-button>
        <x-ui-button href="{{ url('/') }}" variant="primary">
            <i class="bi bi-house-door" aria-hidden="true"></i>
            <span>Ana sayfaya dön</span>
        </x-ui-button>
    </x-ui-error>
@endsection
