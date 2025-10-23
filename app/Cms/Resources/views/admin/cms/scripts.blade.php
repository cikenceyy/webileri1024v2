<div class="alert alert-warning mb-3">
    {{ __('Only <script> tags with external src attributes are allowed. Inline event handlers will be stripped for security.') }}
</div>
<div class="row g-4">
    @foreach($locales as $localeKey => $localeLabel)
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>{{ $localeLabel }}</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">&lt;head&gt;</label>
                        <textarea class="form-control" rows="4" name="scripts[{{ $localeKey }}][header]">{{ $scripts[$localeKey]['header'] ?? '' }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">&lt;footer&gt;</label>
                        <textarea class="form-control" rows="4" name="scripts[{{ $localeKey }}][footer]">{{ $scripts[$localeKey]['footer'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
