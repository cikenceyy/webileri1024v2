@extends('layouts.admin')

@section('title', 'İş Emirleri')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Üretim İş Emirleri</h1>
            <p class="text-muted mb-0">Kobilerin tedarik, üretim ve teslimat akışlarını tek yerden planlayıp ilerlemesini izleyin.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
            <a href="{{ route('admin.production.work-orders.index') }}" class="btn btn-outline-secondary">Yenile</a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWorkOrderModal">
                Üretim Emri Ver
            </button>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            İş emri oluşturulurken bazı alanları kontrol edin.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="alert alert-info d-flex flex-column flex-md-row gap-2 align-items-start align-items-md-center justify-content-between mb-4">
        <div>
            <h2 class="h6 mb-1">Kobiler için üretim planlama yardımcısı</h2>
            <p class="mb-0 small">Müşteri siparişlerine bağlı üretim emirlerini görünür hale getirir, geciken işleri anında yakalamanızı ve kapasitenizi doğru planlamanızı sağlar.</p>
        </div>
        <div class="text-md-end">
            <span class="badge bg-dark-subtle text-dark">Sipariş &rarr; Üretim &rarr; Teslimat</span>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-uppercase small text-muted mb-2">Taslak</div>
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <span class="display-6 fw-semibold text-primary">{{ $statusCounts['draft'] }}</span>
                        <span class="badge rounded-pill text-bg-secondary">Hazırlık</span>
                    </div>
                    <p class="text-muted small mb-0">Planlama bekleyen iş emirleri; ekip kapasitesini dengelemek için önceliklendirin.</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-uppercase small text-muted mb-2">Üretimde</div>
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <span class="display-6 fw-semibold text-primary">{{ $statusCounts['in_progress'] }}</span>
                        <span class="badge rounded-pill text-bg-primary">Sürmekte</span>
                    </div>
                    <p class="text-muted small mb-0">Atölyede çalışan siparişler; darboğazları önceden tespit edin.</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-uppercase small text-muted mb-2">Tamamlanan</div>
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <span class="display-6 fw-semibold text-primary">{{ $statusCounts['done'] }}</span>
                        <span class="badge rounded-pill text-bg-success">Teslim</span>
                    </div>
                    <p class="text-muted small mb-0">Hazır stoklarınızı anında finans ve lojistik ekipleriyle paylaşın.</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-uppercase small text-muted mb-2">Termin Takibi</div>
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <span class="display-6 fw-semibold text-danger">{{ $statusCounts['overdue'] }}</span>
                        <span class="badge rounded-pill text-bg-danger">Geciken</span>
                    </div>
                    <p class="text-muted small mb-1">Teslim tarihi geçen üretimler müşteriye bilgi vermeden kaçmasın.</p>
                    <div class="small text-muted">7 gün içinde termin: <strong class="text-body">{{ $statusCounts['due_soon'] }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.production.work-orders.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5 col-lg-4">
                    <label for="search" class="form-label small text-uppercase text-muted">Arama</label>
                    <input type="search" class="form-control" id="search" name="search" value="{{ $filters['search'] }}" placeholder="İş emri, sipariş no, müşteri, ürün...">
                </div>
                <div class="col-md-4 col-lg-3">
                    <label for="status" class="form-label small text-uppercase text-muted">Durum</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Tümü</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-3">
                    <label for="focus" class="form-label small text-uppercase text-muted">Odak</label>
                    <select id="focus" name="focus" class="form-select">
                        <option value="">—</option>
                        @foreach($focusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['focus'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-2 text-md-end">
                    <button type="submit" class="btn btn-outline-primary w-100">Filtrele</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">İş Emri</th>
                            <th scope="col">Sipariş &amp; Müşteri</th>
                            <th scope="col">Ürün</th>
                            <th scope="col" class="text-end">Miktar</th>
                            <th scope="col">Planlanan Başlangıç</th>
                            <th scope="col">Termin</th>
                            <th scope="col">Durum</th>
                            <th scope="col" class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $statusBadges = [
                                'draft' => 'secondary',
                                'planned' => 'info',
                                'in_progress' => 'primary',
                                'done' => 'success',
                                'cancelled' => 'dark',
                            ];
                        @endphp
                        @forelse($workOrders as $workOrder)
                            <tr @class(['table-warning' => $workOrder->due_date && $workOrder->due_date->isPast() && $workOrder->status !== 'done'])>
                                <td class="fw-semibold">
                                    {{ $workOrder->work_order_no }}
                                    <div class="text-muted small">{{ $workOrder->created_at?->format('d.m.Y H:i') }}</div>
                                </td>
                                <td>
                                    @if($workOrder->order)
                                        <div class="fw-semibold">{{ $workOrder->order->order_no }}</div>
                                        <div class="text-muted small">{{ $workOrder->order->customer?->name ?? 'Müşteri bilgisi yok' }}</div>
                                    @else
                                        <span class="text-muted">Bağlı sipariş yok</span>
                                    @endif
                                </td>
                                <td>
                                    @if($workOrder->product)
                                        <div class="fw-semibold">{{ $workOrder->product->name }}</div>
                                        @if($workOrder->variant)
                                            <div class="text-muted small">{{ $workOrder->variant->option_summary ?: $workOrder->variant->sku }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold">{{ number_format((float) $workOrder->qty, 3, ',', '.') }}</span>
                                    <span class="text-muted">{{ $workOrder->unit }}</span>
                                </td>
                                <td>{{ $workOrder->planned_start_date?->format('d.m.Y') ?? '—' }}</td>
                                <td>
                                    <div>{{ $workOrder->due_date?->format('d.m.Y') ?? '—' }}</div>
                                    @if($workOrder->due_date && $workOrder->due_date->isPast() && $workOrder->status !== 'done')
                                        <div class="small text-danger">Gecikti</div>
                                    @elseif($workOrder->due_date && $workOrder->due_date->isBetween(now(), now()->addDays(7)))
                                        <div class="small text-warning">Bu hafta termin</div>
                                    @endif
                                </td>
                                <td>
                                    @php($badge = $statusBadges[$workOrder->status] ?? 'secondary')
                                    <span class="badge rounded-pill text-bg-{{ $badge }}">
                                        {{ $statusOptions[$workOrder->status] ?? strtoupper($workOrder->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.production.work-orders.show', $workOrder) }}" class="btn btn-sm btn-outline-primary">Detay</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="mb-2">Henüz iş emri bulunmuyor.</div>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWorkOrderModal">İlk üretim emrini oluştur</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $workOrders->links() }}
        </div>
    </div>

    <div class="modal fade" id="createWorkOrderModal" tabindex="-1" aria-labelledby="createWorkOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="createWorkOrderModalLabel">Yeni Üretim Emri</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <form method="POST" action="{{ route('admin.production.work-orders.store') }}" novalidate>
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted small">İsteğe bağlı alanlar kobilerin siparişten üretime geçişini kolaylaştırmak için hazırlanmıştır. Dilerseniz yalnızca ürün ve miktar girerek hızlıca iş emri oluşturabilirsiniz.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="order_id" class="form-label">Sipariş ID (opsiyonel)</label>
                                <input type="number" class="form-control @error('order_id') is-invalid @enderror" id="order_id" name="order_id" value="{{ old('order_id') }}" placeholder="Mevcut sipariş kaydının ID bilgisi">
                                @error('order_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="order_line_id" class="form-label">Sipariş Kalemi ID (opsiyonel)</label>
                                <input type="number" class="form-control @error('order_line_id') is-invalid @enderror" id="order_line_id" name="order_line_id" value="{{ old('order_line_id') }}" placeholder="Üretilecek satırın ID bilgisi">
                                @error('order_line_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="product_id" class="form-label">Ürün ID</label>
                                <input type="number" class="form-control @error('product_id') is-invalid @enderror" id="product_id" name="product_id" value="{{ old('product_id') }}" placeholder="Üretilecek ürünün ID bilgisi">
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="variant_id" class="form-label">Varyant ID (opsiyonel)</label>
                                <input type="number" class="form-control @error('variant_id') is-invalid @enderror" id="variant_id" name="variant_id" value="{{ old('variant_id') }}" placeholder="Belirli varyant gerekiyorsa">
                                @error('variant_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="qty" class="form-label">Üretim Miktarı</label>
                                <input type="number" step="0.001" min="0" class="form-control @error('qty') is-invalid @enderror" id="qty" name="qty" value="{{ old('qty', 1) }}" required>
                                @error('qty')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="unit" class="form-label">Birim</label>
                                <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" value="{{ old('unit', 'adet') }}" maxlength="32">
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="planned_start_date" class="form-label">Planlanan Başlangıç</label>
                                <input type="date" class="form-control @error('planned_start_date') is-invalid @enderror" id="planned_start_date" name="planned_start_date" value="{{ old('planned_start_date') }}">
                                @error('planned_start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="due_date" class="form-label">Termin Tarihi</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="notes" class="form-label">Notlar</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Üretim talimatları, kalite kontrolleri vb.">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button>
                        <button type="submit" class="btn btn-primary">İş Emri Oluştur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if($errors->any())
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                var modalElement = document.getElementById('createWorkOrderModal');
                if (!modalElement) {
                    return;
                }

                var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.show();
            });
        </script>
    @endif
@endpush
