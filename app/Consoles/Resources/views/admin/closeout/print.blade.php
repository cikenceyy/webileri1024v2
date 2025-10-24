@extends('layouts.admin')

@php($module = 'Consoles')
@php($page = 'closeout')

@section('content')
    <div class="container py-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h5 mb-1">Toplu Yazdırma Sonuçları</h1>
                    <p class="text-muted mb-0">Seçtiğiniz belgeler için yazdırma bağlantılarını aşağıda bulabilirsiniz.</p>
                </div>
                <a class="btn btn-outline-primary" href="{{ route('admin.consoles.closeout.index') }}">Konsola Dön</a>
            </div>
            <div class="card-body">
                @if(!empty($links))
                    <ul class="list-group list-group-flush">
                        @foreach($links as $link)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $link['label'] ?? __('Belge') }}</span>
                                <a class="btn btn-sm btn-primary" href="{{ $link['url'] ?? '#' }}" target="_blank" rel="noopener">{{ __('Yazdır') }}</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted mb-0">Yazdırılabilir belge bulunamadı. Lütfen seçimlerinizi kontrol edin.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
