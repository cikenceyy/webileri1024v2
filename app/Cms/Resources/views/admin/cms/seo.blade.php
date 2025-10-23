<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">SEO</h2>
        <small class="text-muted">{{ __('Meta title, description and OG image per language.') }}</small>
    </div>
    <div class="card-body">
        <div class="row g-4">
            @foreach($locales as $localeKey => $localeLabel)
                <div class="col-lg-6">
                    <h3 class="h6 text-uppercase text-muted mb-3">{{ $localeLabel }}</h3>
                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" class="form-control" name="seo[{{ $localeKey }}][meta_title]" value="{{ $seo[$localeKey]['meta_title'] ?? '' }}" maxlength="180">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea class="form-control" name="seo[{{ $localeKey }}][meta_description]" rows="3" maxlength="260">{{ $seo[$localeKey]['meta_description'] ?? '' }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">OG Image</label>
                        @if(!empty($seo[$localeKey]['og_image']))
                            <div class="d-flex align-items-center justify-content-between bg-light border rounded px-3 py-2 mb-2">
                                <a href="{{ $seo[$localeKey]['og_image'] }}" target="_blank" class="small">{{ __('View current image') }}</a>
                                <label class="form-check-label small">
                                    <input type="checkbox" class="form-check-input" name="seo[{{ $localeKey }}][og_image_remove]" value="1">
                                    {{ __('Remove') }}
                                </label>
                                <input type="hidden" name="seo[{{ $localeKey }}][og_image]" value="{{ $seo[$localeKey]['og_image'] }}">
                            </div>
                        @endif
                        <input type="file" class="form-control" name="seo[{{ $localeKey }}][og_image]" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted d-block mt-1">{{ __('Recommended ratio 1200x630, max 2MB.') }}</small>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
