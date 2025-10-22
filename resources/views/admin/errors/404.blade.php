@extends('layouts.admin')

@section('title', 'Sayfa bulunamadı')
@push('meta')
    <meta name="robots" content="noindex">
@endpush

@php
    use Illuminate\Support\Str;

    $errorId = $errorId ?? Str::upper(Str::ulid());
@endphp

@section('content')
    <div class="ui-content" data-ui="content-section">
        <x-ui-error
            code="404"
            title="Bu sayfaya erişemiyoruz"
            description="Aradığınız yönetim sayfası kaldırılmış olabilir veya erişim yetkiniz bulunmuyor."
            hint="Adresin doğru olduğundan emin olun. Destek ekibine ulaşırken aşağıdaki hata kodunu paylaşabilirsiniz."
            :meta="[
                'Hata kodu' => $errorId,
                'İstek yolu' => $requestPath ?? request()->path(),
            ]"
        >
            <x-ui-button href="{{ route('admin.dashboard') }}" variant="primary">
                <i class="bi bi-speedometer2" aria-hidden="true"></i>
                <span>Dashboard’a dön</span>
            </x-ui-button>
            <x-ui-button tag="button" type="button" variant="ghost" data-action="history-back">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                <span>Geri</span>
            </x-ui-button>
            <x-ui-button href="{{ url('admin/support/new-ticket') }}" variant="outline-secondary">
                <i class="bi bi-life-preserver" aria-hidden="true"></i>
                <span>Yardım / Destek</span>
            </x-ui-button>
        </x-ui-error>
    </div>
@endsection
