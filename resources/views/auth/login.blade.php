@extends('layouts.auth')

@section('title', 'Yönetici Girişi')
@section('page', 'AuthLogin')

@section('content')
    <div class="container" style="max-width: 420px;">
        <x-ui-card class="shadow-lg">
            <x-slot name="header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="h5 mb-1">{{ __('Yönetim Paneli Girişi') }}</h1>
                        <p class="text-muted small mb-0">{{ __('Şirket alanınız için oturum açın.') }}</p>
                    </div>
                    <span class="badge text-bg-primary">{{ config('app.name', 'Webileri') }}</span>
                </div>
            </x-slot>

            @if(session('status'))
                <x-ui-alert type="success" dismissible class="mb-3">{{ session('status') }}</x-ui-alert>
            @endif

            @if($errors->any())
                <x-ui-alert type="danger" dismissible class="mb-3">
                    {{ $errors->first() }}
                </x-ui-alert>
            @endif

            <form method="POST" action="{{ route('admin.auth.login.attempt') }}" class="d-flex flex-column gap-3" autocomplete="off" novalidate>
                @csrf

                <x-ui-input
                    name="email"
                    type="email"
                    label="{{ __('E-posta adresi') }}"
                    :value="old('email')"
                    required
                    autofocus
                />

                <x-ui-input
                    name="password"
                    type="password"
                    label="{{ __('Parola') }}"
                    required
                />

                <x-ui-switch
                    name="remember"
                    label="{{ __('Beni hatırla') }}"
                    :checked="old('remember', false)"
                />

                <div class="d-grid gap-2">
                    <x-ui-button type="submit" variant="primary">{{ __('Oturum Aç') }}</x-ui-button>
                </div>
            </form>

            <x-slot name="footer">
                <p class="small text-muted mb-0">
                    {{ __('Güvenlik için ortak cihazlardan çıktıktan sonra oturumu kapatmayı unutmayın.') }}
                </p>
            </x-slot>
        </x-ui-card>
    </div>
@endsection
