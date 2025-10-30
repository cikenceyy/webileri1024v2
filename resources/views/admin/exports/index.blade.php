{{-- TableKit export geçmişini listeler. --}}
@extends('layouts.admin')

@section('title', __('Export Geçmişi'))

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('Export Geçmişi') }}</h1>
            <p class="text-muted mb-0">{{ __('Tamamlanan ve devam eden dışa aktarmalar') }}</p>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">{{ __('Tablo') }}</th>
                    <th scope="col">{{ __('Format') }}</th>
                    <th scope="col">{{ __('Durum') }}</th>
                    <th scope="col">{{ __('Satır') }}</th>
                    <th scope="col">{{ __('İlerleme') }}</th>
                    <th scope="col">{{ __('Oluşturuldu') }}</th>
                    <th scope="col" class="text-end">{{ __('Aksiyonlar') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($exports as $export)
                    <tr>
                        <td>{{ $export->id }}</td>
                        <td>{{ $export->table_key }}</td>
                        <td>{{ strtoupper($export->format) }}</td>
                        <td>{{ __($export->status) }}</td>
                        <td>{{ $export->row_count }}</td>
                        <td>
                            <div class="progress" role="progressbar" aria-valuenow="{{ $export->progress }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: {{ $export->progress }}%"></div>
                            </div>
                        </td>
                        <td>{{ optional($export->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            @if($export->status === \App\Core\Exports\Models\TableExport::STATUS_DONE && $export->file_path)
                                <a href="{{ route('admin.exports.download', $export) }}" class="btn btn-sm btn-outline-primary">{{ __('İndir') }}</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">{{ __('Henüz export bulunmuyor.') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
