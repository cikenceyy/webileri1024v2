@extends('layouts.admin')

@section('title', 'Gösterge Paneli')
@section('module', 'Core')
@section('page', 'Dashboard')

@section('content')
    <div class="container-fluid py-4">
        <x-ui-page-header
            title="{{ __('Hoş geldiniz') }}"
            description="{{ __('Şirket genel görünümünü buradan takip edebilirsiniz.') }}"
        >
            <x-slot name="actions">
                <x-ui-button href="{{ route('admin.dashboard') }}" variant="ghost">
                    {{ __('Yenile') }}
                </x-ui-button>
            </x-slot>
        </x-ui-page-header>

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <x-ui-card>
                    <x-slot name="header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h6 mb-1">{{ __('Bugünkü Durum') }}</h2>
                                <p class="text-muted small mb-0">{{ __('Kritik metrikler ve bekleyen aksiyonlar.') }}</p>
                            </div>
                            <span class="badge text-bg-success">{{ now()->format('d.m.Y') }}</span>
                        </div>
                    </x-slot>

                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-4">
                            <x-ui-kpi label="{{ __('Açık Sipariş') }}" value="128" delta="+12" trend="{{ __('Haftalık') }}" />
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <x-ui-kpi label="{{ __('Sevk Hazır') }}" value="42" delta="-5" trend="{{ __('Günlük') }}" />
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <x-ui-kpi label="{{ __('Tahsilat Bekleyen') }}" value="₺ 240K" delta="+8%" trend="{{ __('Aylık') }}" />
                        </div>
                    </div>
                </x-ui-card>
            </div>

            <div class="col-12 col-xl-4">
                <x-ui-card title="{{ __('Hızlı İşlemler') }}" subtitle="{{ __('Sık kullanılan bağlantılar') }}">
                    <div class="d-flex flex-column gap-2">
                        <a class="btn btn-outline-primary" href="#">{{ __('Yeni Satış Fırsatı') }}</a>
                        <a class="btn btn-outline-primary" href="#">{{ __('Stok Sayımı Başlat') }}</a>
                        <a class="btn btn-outline-primary" href="#">{{ __('Satınalma Talebi Oluştur') }}</a>
                    </div>
                </x-ui-card>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-12 col-lg-6">
                <x-ui-card title="{{ __('Aktivite Akışı') }}">
                    <x-ui-skeleton lines="6" class="mb-0" />
                </x-ui-card>
            </div>
            <div class="col-12 col-lg-6">
                <x-ui-card title="{{ __('Üretim Planı') }}">
                    <x-ui-empty description="{{ __('Planlı iş emirleri burada listelenecek.') }}">
                        <x-slot name="actions">
                            <x-ui-button variant="primary">{{ __('Plan Ekle') }}</x-ui-button>
                            <x-ui-button variant="ghost">{{ __('Daha sonra') }}</x-ui-button>
                        </x-slot>
                    </x-ui-empty>
                </x-ui-card>
            </div>
        </div>
    </div>
@endsection
