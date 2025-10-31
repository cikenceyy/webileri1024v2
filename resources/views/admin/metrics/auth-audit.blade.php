{{--
    Amaç: Yetki reddi denemelerini TR dil birliğiyle raporlamak.
    İlişkiler: PROMPT-1 — TR Dil Birliği.
    Notlar: Başlık ve tablo etiketleri TR metinlerle güncellendi.
--}}
@extends('layouts.admin')

@section('title', 'Yetki Denetim Kayıtları')
@section('module', 'Admin')
@section('page', 'Yetki Denetim Kayıtları')

@section('content')
    <div class="container-fluid py-4">
        <x-ui-page-header
            title="Yetki Denetim Kayıtları"
            description="Bu ekran yalnızca denetim amaçlıdır; izinsiz eylem denemelerini gösterir."
        />

        <x-ui-alert variant="warning" tone="soft" icon="bi bi-shield-lock" class="mb-3">
            Domain veya kullanıcı değişiklikleri yalnızca CLI/Superadmin tarafından yapılabilir; bu kayıtlar sadece bilgilendirme amaçlıdır.
        </x-ui-alert>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Tarih</th>
                                <th scope="col">Kullanıcı</th>
                                <th scope="col">İşlem</th>
                                <th scope="col">Kaynak</th>
                                <th scope="col">IP</th>
                                <th scope="col">Sonuç</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($audits as $audit)
                                <tr>
                                    <td>{{ $audit->created_at?->format('Y-m-d H:i') }}</td>
                                    <td>{{ $audit->user_id ? ('#' . $audit->user_id) : 'Anonim' }}</td>
                                    <td>{{ $audit->action }}</td>
                                    <td>{{ $audit->resource ?? '—' }}</td>
                                    <td>{{ $audit->ip_address ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger fw-semibold">{{ strtoupper($audit->result) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Henüz kayıt yok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Sonuçlar tenant bazlı filtrelenir.
                </div>
                {{ $audits->links() }}
            </div>
        </div>
    </div>
@endsection
