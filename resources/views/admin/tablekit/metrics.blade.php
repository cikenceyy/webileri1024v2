{{--
    Amaç: TableKit performans metriklerini TR dil birliğiyle raporlamak.
    İlişkiler: PROMPT-1 — TR Dil Birliği.
    Notlar: Form ve tablo etiketleri TR karşılıklarıyla güncellendi.
--}}
@extends('layouts.admin')

@section('title', 'Tablo Metrikleri')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">TableKit Performansı</h1>
            <p class="text-muted mb-0">En sık kullanılan listeler ve yanıt süreleri</p>
        </div>
        <form method="get" class="d-flex gap-2" aria-label="Metrik filtresi">
            <input type="date" name="date" value="{{ $selectedDate->toDateString() }}" class="form-control"
                   aria-label="Tarih seç">
            <input type="text" name="table_key" value="{{ $selectedTableKey }}" class="form-control"
                   placeholder="Tablo anahtarı" aria-label="Tablo anahtarı">
            <button type="submit" class="btn btn-primary">Uygula</button>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">En Çok Kullanılanlar</h2>
                    <ul class="list-unstyled mb-0">
                        @forelse($topTables as $entry)
                            <li class="mb-2">
                                <strong>{{ $entry->table_key }}</strong>
                                <span class="text-muted">— {{ $entry->request_count }} istek</span>
                            </li>
                        @empty
                            <li class="text-muted">Kayıt yok</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">P95 Yanıt Süresi (ms)</h2>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr>
                                <th scope="col">Tablo</th>
                                <th scope="col" class="text-end">P95 (ms)</th>
                                <th scope="col" class="text-end">Ortalama (ms)</th>
                                <th scope="col" class="text-end">Önbellek %</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($entries as $entry)
                                <tr>
                                    <td>{{ $entry->table_key }}</td>
                                    <td class="text-end">{{ $entry->p95_total_time_ms }}</td>
                                    <td class="text-end">{{ $entry->avg_total_time_ms }}</td>
                                    <td class="text-end">{{ number_format($entry->cache_hit_ratio, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Kayıt yok</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
