@extends('layouts.admin')

@section('title', __('Today Board'))
@section('module', 'Consoles')
@section('page', 'Today')

@section('content')
    <div class="container-fluid py-4 consoles-today">
        <x-ui-page-header
            title="{{ __('Today Board') }}"
            description="{{ __('Günlük operasyon durumunuzu tek yerden takip edin.') }}"
        >
            <x-slot name="actions">
                <x-ui-button href="{{ route('consoles.today') }}" variant="ghost">
                    {{ __('Yenile') }}
                </x-ui-button>
            </x-slot>
        </x-ui-page-header>

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <x-ui-card title="{{ __('Durum Özeti') }}" subtitle="{{ __('Kritik metrikler ve bekleyen aksiyonlar.') }}">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-4">
                            <x-ui-kpi
                                label="{{ __('Açık Sipariş') }}"
                                value="{{ number_format((int) ($summary['orders'] ?? 0)) }}"
                                trend="{{ __('Bugün') }}"
                            />
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <x-ui-kpi
                                label="{{ __('Sevk Hazır') }}"
                                value="{{ number_format((int) ($summary['shipments'] ?? 0)) }}"
                                trend="{{ __('Bugün') }}"
                            />
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <x-ui-kpi
                                label="{{ __('Geciken İşler') }}"
                                value="0"
                                trend="{{ __('Takip Gerekiyor') }}"
                            />
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-muted mb-2">{{ __('Bu özet, tenant kullanıcılarının açık sipariş ve sevkiyat sayılarını temel alır.') }}</p>
                        <p class="mb-0">{{ __('Detaylı veri bağlantısı tamamlandığında kartlar gerçek zamanlı olarak güncellenecektir.') }}</p>
                    </div>
                </x-ui-card>
            </div>

            <div class="col-12 col-xl-4">
                <x-ui-card title="{{ __('Hızlı İşlemler') }}" subtitle="{{ __('En çok kullanılan adımlara hızlı erişim') }}">
                    <div class="d-flex flex-column gap-2">
                        <x-ui-button href="#" variant="outline-primary">{{ __('Yeni Sipariş Oluştur') }}</x-ui-button>
                        <x-ui-button href="#" variant="outline-primary">{{ __('Sevkiyat Planla') }}</x-ui-button>
                        <x-ui-button href="#" variant="outline-primary">{{ __('Üretim Talimatı Yarat') }}</x-ui-button>
                    </div>
                </x-ui-card>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-12 col-lg-6">
                <x-ui-card title="{{ __('Aktivite Akışı') }}" subtitle="{{ __('Son hareketler ve bildirimler') }}">
                    <x-ui-empty description="{{ __('Henüz gösterilecek aktivite bulunmuyor.') }}">
                        <x-slot name="actions">
                            <x-ui-button variant="ghost">{{ __('Yenile') }}</x-ui-button>
                        </x-slot>
                    </x-ui-empty>
                </x-ui-card>
            </div>

            <div class="col-12 col-lg-6">
                <x-ui-card title="{{ __('Günün Görevleri') }}" subtitle="{{ __('Takip edilmesi gereken kritik adımlar') }}">
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                        <li>
                            <h4 class="h6 mb-1">{{ __('Bekleyen sipariş onayları') }}</h4>
                            <p class="text-muted mb-0">{{ __('Satış ekibinin onayına sunulan talepleri gözden geçirin.') }}</p>
                        </li>
                        <li>
                            <h4 class="h6 mb-1">{{ __('Üretim kapasite kontrolü') }}</h4>
                            <p class="text-muted mb-0">{{ __('Çalışan iş emirleri için kapasite planını doğrulayın.') }}</p>
                        </li>
                        <li>
                            <h4 class="h6 mb-1">{{ __('Sevkiyat hazırlığı') }}</h4>
                            <p class="text-muted mb-0">{{ __('Bugün çıkması gereken sevkiyatlar için paketleme durumunu kontrol edin.') }}</p>
                        </li>
                    </ul>
                </x-ui-card>
            </div>
        </div>
    </div>
@endsection