@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        <h1 class="mb-4">{{ $pageConfig['label'] ?? ucfirst($pageKey) }}</h1>
        <form method="POST" enctype="multipart/form-data" action="{{ route('cms.admin.pages.update', $pageKey) }}">
            @csrf
            <div class="mb-4">
                <ul class="nav nav-tabs" id="localeTabs" role="tablist">
                    @foreach(['tr' => 'Türkçe', 'en' => 'English'] as $localeKey => $label)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if($loop->first) active @endif" id="tab-{{ $localeKey }}" data-bs-toggle="tab" data-bs-target="#locale-{{ $localeKey }}" type="button" role="tab">{{ $label }}</button>
                        </li>
                    @endforeach
                </ul>
                <div class="tab-content border border-top-0 p-4" id="localeTabContent">
                    @foreach(['tr' => 'Türkçe', 'en' => 'English'] as $localeKey => $label)
                        <div class="tab-pane fade @if($loop->first) show active @endif" id="locale-{{ $localeKey }}" role="tabpanel">
                            @foreach($pageConfig['blocks'] ?? [] as $blockKey => $definition)
                                <div class="accordion mb-3" id="accordion-{{ $localeKey }}-{{ $blockKey }}">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading-{{ $localeKey }}-{{ $blockKey }}">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $localeKey }}-{{ $blockKey }}">
                                                {{ $definition['label'] ?? ucfirst($blockKey) }}
                                            </button>
                                        </h2>
                                        <div id="collapse-{{ $localeKey }}-{{ $blockKey }}" class="accordion-collapse collapse show">
                                            <div class="accordion-body">
                                                @php $blockData = $content[$localeKey]['blocks'][$blockKey] ?? ($definition['repeater'] ?? false ? [] : []); @endphp
                                                @if(!empty($definition['repeater']))
                                                    <div class="cms-repeater" data-repeater-for="{{ $blockKey }}">
                                                        @php $items = $blockData ?: [array_fill_keys(array_keys($definition['fields'] ?? []), null)]; @endphp
                                                        @foreach($items as $index => $item)
                                                            <div class="card mb-3 repeater-item">
                                                                <div class="card-body">
                                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                                        <h5 class="card-title mb-0">Öğe {{ $loop->iteration }}</h5>
                                                                        <button class="btn btn-sm btn-outline-danger" type="button" data-repeater-remove>Sil</button>
                                                                    </div>
                                                                    @foreach($definition['fields'] ?? [] as $fieldKey => $fieldDefinition)
                                                                        @php $value = $item[$fieldKey] ?? null; @endphp
                                                                        <div class="mb-3">
                                                                            <label class="form-label">{{ ucfirst(str_replace('_', ' ', $fieldKey)) }}</label>
                                                                            @if(in_array($fieldDefinition['type'] ?? 'text', ['image', 'file'], true))
                                                                                @if($value)
                                                                                    <div class="mb-2"><a href="{{ $value }}" target="_blank">Mevcut Dosya</a></div>
                                                                                    <input type="hidden" name="content[{{ $localeKey }}][{{ $blockKey }}][{{ $index }}][{{ $fieldKey }}]" value="{{ $value }}">
                                                                                @endif
                                                                                <input type="file" class="form-control" name="content[{{ $localeKey }}][{{ $blockKey }}][{{ $index }}][{{ $fieldKey }}]">
                                                                            @else
                                                                                <input type="text" class="form-control" name="content[{{ $localeKey }}][{{ $blockKey }}][{{ $index }}][{{ $fieldKey }}]" value="{{ $value }}">
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-repeater-add="{{ $blockKey }}">Yeni Öğe Ekle</button>
                                                @else
                                                    @foreach($definition['fields'] ?? [] as $fieldKey => $fieldDefinition)
                                                        @php $value = $blockData[$fieldKey] ?? null; @endphp
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ ucfirst(str_replace('_', ' ', $fieldKey)) }}</label>
                                                            @if(($fieldDefinition['type'] ?? 'text') === 'textarea')
                                                                <textarea class="form-control" rows="4" name="content[{{ $localeKey }}][{{ $blockKey }}][{{ $fieldKey }}]">{{ $value }}</textarea>
                                                            @elseif(in_array($fieldDefinition['type'] ?? 'text', ['image', 'file'], true))
                                                                @if($value)
                                                                    <div class="mb-2"><a href="{{ $value }}" target="_blank">Mevcut Dosya</a></div>
                                                                    <input type="hidden" name="content[{{ $localeKey }}][{{ $blockKey }}][{{ $fieldKey }}]" value="{{ $value }}">
                                                                @endif
                                                                <input type="file" class="form-control" name="content[{{ $localeKey }}][{{ $blockKey }}][{{ $fieldKey }}]">
                                                            @else
                                                                <input type="text" class="form-control" name="content[{{ $localeKey }}][{{ $blockKey }}][{{ $fieldKey }}]" value="{{ $value }}">
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="border rounded p-3 mb-4">
                                <h5>SEO</h5>
                                <div class="mb-3">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" class="form-control" name="seo[{{ $localeKey }}][meta_title]" value="{{ $seo[$localeKey]['meta_title'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Meta Description</label>
                                    <textarea class="form-control" rows="3" name="seo[{{ $localeKey }}][meta_description]">{{ $seo[$localeKey]['meta_description'] ?? '' }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">OG Image</label>
                                    @if(!empty($seo[$localeKey]['og_image']))
                                        <div class="mb-2"><a href="{{ $seo[$localeKey]['og_image'] }}" target="_blank">Mevcut Görsel</a></div>
                                        <input type="hidden" name="seo[{{ $localeKey }}][og_image]" value="{{ $seo[$localeKey]['og_image'] }}">
                                    @endif
                                    <input type="file" class="form-control" name="seo[{{ $localeKey }}][og_image]">
                                </div>
                            </div>

                            <div class="border rounded p-3 mb-4">
                                <h5>Header/Footer Script</h5>
                                <div class="mb-3">
                                    <label class="form-label">Header Script</label>
                                    <textarea class="form-control" rows="3" name="scripts[{{ $localeKey }}][header]">{{ $scripts[$localeKey]['header'] ?? '' }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Footer Script</label>
                                    <textarea class="form-control" rows="3" name="scripts[{{ $localeKey }}][footer]">{{ $scripts[$localeKey]['footer'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="border rounded p-3 mb-4">
                <h5>E-posta Ayarları</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Info E-posta</label>
                        <input type="email" name="emails[info_email]" class="form-control" value="{{ $emails['info_email'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Notify E-posta</label>
                        <input type="email" name="emails[notify_email]" class="form-control" value="{{ $emails['notify_email'] ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-repeater-add]').forEach(function (button) {
            button.addEventListener('click', function () {
                var key = this.getAttribute('data-repeater-add');
                var container = this.previousElementSibling;
                if (!container) return;
                var lastItem = container.querySelector('.repeater-item:last-child');
                if (!lastItem) return;
                var clone = lastItem.cloneNode(true);
                var index = container.querySelectorAll('.repeater-item').length;
                clone.querySelectorAll('input, textarea').forEach(function (input) {
                    var name = input.getAttribute('name');
                    if (name) {
                        input.setAttribute('name', name.replace(/\]\[(\d+)\]\[/, '][' + index + ']['));
                    }
                    if (input.type !== 'hidden' && input.type !== 'file') {
                        input.value = '';
                    }
                });
                container.appendChild(clone);
            });
        });

        document.addEventListener('click', function (event) {
            if (event.target.matches('[data-repeater-remove]')) {
                var item = event.target.closest('.repeater-item');
                var container = event.target.closest('.cms-repeater');
                if (container && item && container.querySelectorAll('.repeater-item').length > 1) {
                    item.remove();
                }
            }
        });
    </script>
@endpush
