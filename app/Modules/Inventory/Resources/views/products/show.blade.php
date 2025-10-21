@extends('layouts.admin')

@section('title', $product->name)

@section('content')
<x-ui.page-header :title="$product->name" description="Ürün detayları">
    <x-slot name="actions">
        @can('update', $product)
            <x-ui.button variant="outline" href="{{ route('admin.inventory.products.edit', $product) }}">Düzenle</x-ui.button>
        @endcan
        <x-ui.button variant="secondary" href="{{ route('admin.inventory.products.index') }}">Listeye Dön</x-ui.button>
    </x-slot>
</x-ui.page-header>

@if(session('status'))
    <x-ui.alert type="success" dismissible>{{ session('status') }}</x-ui.alert>
@endif

<div class="row g-4">
    <div class="col-xl-6">
        <x-ui.card>
            <div class="d-flex align-items-center gap-3">
                <div class="flex-shrink-0">
                    @if($product->media)
                        <x-ui.file-icon :ext="$product->media->ext" size="56" />
                    @else
                        <x-ui.file-icon ext="file" size="56" />
                    @endif
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted small">SKU</div>
                    <div class="fw-semibold fs-5">{{ $product->sku }}</div>
                    <div class="text-muted small">Durum</div>
                    <x-ui.badge :type="$product->status === 'active' ? 'success' : 'secondary'" soft>{{ $product->status === 'active' ? 'Aktif' : 'Pasif' }}</x-ui.badge>
                </div>
            </div>
            <hr>
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="text-muted small">Kategori</div>
                    <div class="fw-semibold">{{ $product->category?->name ?? 'Belirtilmemiş' }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small">Barkod</div>
                    <div class="fw-semibold">{{ $product->barcode ?? '—' }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small">Temel Birim</div>
                    <div class="fw-semibold">{{ $product->baseUnit?->code ?? $product->unit }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small">Yeniden Sipariş Noktası</div>
                    <div class="fw-semibold">{{ number_format((float) ($product->reorder_point ?? 0), 3, ',', '.') }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small">Varsayılan Fiyat</div>
                    <div class="fw-semibold">{{ number_format((float) $product->price, 2, ',', '.') }} {{ $product->unit }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">Açıklama</div>
                    <p class="mb-0">{{ $product->description ?: '—' }}</p>
                </div>
            </div>
        </x-ui.card>
    </div>
    <div class="col-xl-6">
        <x-ui.card>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Galeri</h2>
                @can('attachMedia', $product)
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-action="open-gallery-picker" data-gallery-target="productGalleryInput">Görsel Ekle</button>
                @endcan
            </div>
            @if($product->gallery->isEmpty())
                <x-ui.empty title="Galeri boş" description="Drive'dan görseller ekleyebilirsiniz.">
                    @can('attachMedia', $product)
                        <x-slot name="actions">
                            <x-ui.button variant="primary" type="button" data-action="open-gallery-picker" data-gallery-target="productGalleryInput">Görsel Seç</x-ui.button>
                        </x-slot>
                    @endcan
                </x-ui.empty>
            @else
                <div class="list-group list-group-flush">
                    @foreach($product->gallery as $item)
                        <div class="list-group-item d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <x-ui.file-icon :ext="$item->media?->ext" size="28" />
                                <div>
                                    <div class="fw-semibold">{{ $item->media?->original_name ?? 'Silinmiş dosya' }}</div>
                                    <div class="text-muted small">{{ $item->media?->mime }}</div>
                                </div>
                            </div>
                            @can('attachMedia', $product)
                                <form method="POST" action="{{ route('admin.inventory.products.gallery.remove', [$product, $item]) }}" onsubmit="return confirm('Görseli kaldırmak istediğinize emin misiniz?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="ghost" size="sm">Kaldır</x-ui.button>
                                </form>
                            @endcan
                        </div>
                    @endforeach
                </div>
            @endif
            @can('attachMedia', $product)
                <form method="POST" action="{{ route('admin.inventory.products.gallery.add', $product) }}" class="mt-3" data-gallery-form>
                    @csrf
                    <input type="hidden" name="media_id" id="productGalleryInput">
                    <div class="small text-muted mb-2" data-gallery-selection aria-live="polite">Henüz bir dosya seçilmedi.</div>
                    <x-ui.button type="submit" variant="primary">Seçilen Görseli Ekle</x-ui.button>
                </form>
            @endcan
        </x-ui.card>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-6">
        <x-ui.card>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Fiyat Listeleri</h2>
                <a href="{{ route('admin.inventory.pricelists.index') }}" class="btn btn-sm btn-outline-secondary">Tüm listeler</a>
            </div>
            @if($priceLists->isEmpty())
                <p class="text-muted mb-0">Bu ürüne atanmış fiyat listesi bulunmuyor.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Liste</th>
                                <th>Fiyat</th>
                                <th class="text-end">Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($priceLists as $priceList)
                                @foreach($priceList->items as $item)
                                    <tr>
                                        <td>{{ $priceList->name }} ({{ strtoupper($priceList->currency) }})</td>
                                        <td>{{ number_format((float) $item->price, 2, ',', '.') }}</td>
                                        <td class="text-end">
                                            <x-ui.badge :type="$priceList->active ? 'success' : 'secondary'" soft>{{ $priceList->active ? 'Aktif' : 'Pasif' }}</x-ui.badge>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>
    </div>
    <div class="col-xl-6">
        <x-ui.card>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Varyantlar</h2>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.inventory.products.variants.index', $product) }}">Varyantları Yönet</a>
            </div>
            @if($product->variants->isEmpty())
                <p class="text-muted mb-0">Bu ürün için tanımlı varyant bulunmuyor.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Barkod</th>
                                <th>Seçenekler</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->variants as $variant)
                                <tr>
                                    <td>{{ $variant->sku }}</td>
                                    <td>{{ $variant->barcode ?? '—' }}</td>
                                    <td>
                                        @if(empty($variant->options))
                                            <span class="text-muted">—</span>
                                        @else
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($variant->options as $key => $value)
                                                    <x-ui.badge type="secondary" soft>{{ $key }}: {{ $value }}</x-ui.badge>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <x-ui.badge :type="$variant->status === 'active' ? 'success' : 'secondary'" soft>{{ $variant->status === 'active' ? 'Aktif' : 'Pasif' }}</x-ui.badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-6">
        <x-ui.card>
            <h2 class="h5 mb-3">Stok Durumu</h2>
            @if($stockByWarehouse->isEmpty())
                <p class="text-muted mb-0">Bu ürün için stok kaydı bulunmuyor.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Ambar</th>
                                <th class="text-end">Mevcut</th>
                                <th class="text-end">Rezerve</th>
                                <th class="text-end">Kritik Seviye</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockByWarehouse as $item)
                                <tr>
                                    <td>{{ $item->warehouse?->name ?? '—' }}</td>
                                    <td class="text-end fw-semibold">{{ number_format((float) $item->qty, 3, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format((float) $item->reserved_qty, 3, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format((float) $item->reorder_point, 3, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($variantStock->isNotEmpty())
                <hr>
                <h3 class="h6">Varyant Bazında</h3>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Ambar</th>
                                <th>Varyant</th>
                                <th class="text-end">Mevcut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($variantStock as $item)
                                <tr>
                                    <td>{{ $item->warehouse?->name ?? '—' }}</td>
                                    <td>{{ $item->variant?->sku ?? 'Varsayılan' }}</td>
                                    <td class="text-end">{{ number_format((float) $item->qty, 3, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>
    </div>
    <div class="col-xl-6">
        <x-ui.card>
            <h2 class="h5 mb-3">Son Hareketler</h2>
            @if($recentMovements->isEmpty())
                <p class="text-muted mb-0">Bu ürün için hareket kaydı bulunmuyor.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Ambar</th>
                                <th>Yön</th>
                                <th class="text-end">Miktar</th>
                                <th>Neden</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentMovements as $movement)
                                <tr>
                                    <td>{{ $movement->moved_at?->format('d.m.Y H:i') }}</td>
                                    <td>{{ $movement->warehouse?->name ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $movement->direction === 'in' ? 'success' : 'danger' }}">{{ strtoupper($movement->direction) }}</span>
                                    </td>
                                    <td class="text-end">{{ number_format((float) $movement->qty, 3, ',', '.') }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $movement->reason)) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>
    </div>
</div>

<x-ui.modal id="galleryPickerModal" size="xl">
    <x-slot name="title">Drive'dan Galeri Görseli Seç</x-slot>
    <div class="ratio ratio-16x9" data-gallery-picker-container>
        <iframe
            src="{{ route('admin.drive.media.index', ['tab' => 'media_products', 'picker' => 1]) }}"
            title="Drive Galeri Seçici"
            allow="autoplay"
            data-gallery-picker-frame
        ></iframe>
    </div>
</x-ui.modal>
@endsection
