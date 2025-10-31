{{--
    Amaç: Dışa aktarımların geçmişini TR dil birliği ile listelemek.
    İlişkiler: PROMPT-1 — TR Dil Birliği.
    Notlar: Statü etiketleri TR karşılıklarıyla eşlendi.
--}}
@extends('layouts.admin')

@section('title', 'Dışa Aktarım Geçmişi')

@php($statusLabels = [
    \App\Core\Exports\Models\TableExport::STATUS_PENDING => 'Bekliyor',
    \App\Core\Exports\Models\TableExport::STATUS_RUNNING => 'İşleniyor',
    \App\Core\Exports\Models\TableExport::STATUS_DONE => 'Tamamlandı',
    \App\Core\Exports\Models\TableExport::STATUS_FAILED => 'Hatalı',
])

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Dışa Aktarım Geçmişi</h1>
            <p class="text-muted mb-0">Tamamlanan ve devam eden dışa aktarmalar</p>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Tablo</th>
                    <th scope="col">Format</th>
                    <th scope="col">Durum</th>
                    <th scope="col">Satır</th>
                    <th scope="col">İlerleme</th>
                    <th scope="col">Oluşturuldu</th>
                    <th scope="col" class="text-end">İşlemler</th>
                </tr>
                </thead>
                <tbody>
                @forelse($exports as $export)
                    <tr>
                        <td>{{ $export->id }}</td>
                        <td>{{ $export->table_key }}</td>
                        <td>{{ strtoupper($export->format) }}</td>
                        <td>{{ $statusLabels[$export->status] ?? ucfirst($export->status) }}</td>
                        <td>{{ $export->row_count }}</td>
                        <td>
                            <div class="progress" role="progressbar" aria-valuenow="{{ $export->progress }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: {{ $export->progress }}%"></div>
                            </div>
                        </td>
                        <td>{{ optional($export->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            @if($export->status === \App\Core\Exports\Models\TableExport::STATUS_DONE && $export->file_path)
                                <a href="{{ route('admin.exports.download', $export) }}" class="btn btn-sm btn-outline-primary">İndir</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Henüz dışa aktarım bulunmuyor.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
