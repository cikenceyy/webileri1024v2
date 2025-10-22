@php($assetKey = 'contact')
@extends('cms::site.layout')

@section('content')
    @php($blocks = $data['blocks'] ?? [])
    <section class="container py-5" data-analytics-section="contact">
        <div class="row g-5">
            <div class="col-md-5">
                <h1>{{ $locale === 'en' ? 'Contact' : 'İletişim' }}</h1>
                <div class="mb-3">
                    <h5>{{ $locale === 'en' ? 'Address' : 'Adres' }}</h5>
                    <p class="text-muted white-space-pre-wrap">{{ $blocks['coords']['address'] ?? '' }}</p>
                </div>
                <div class="mb-3">
                    <h5>Telefon</h5>
                    <a href="tel:{{ $blocks['coords']['phone'] ?? '' }}">{{ $blocks['coords']['phone'] ?? '' }}</a>
                </div>
                <div class="mb-3">
                    <h5>E-posta</h5>
                    <a href="mailto:{{ $blocks['coords']['email'] ?? '' }}">{{ $blocks['coords']['email'] ?? '' }}</a>
                </div>
                @if(!empty($blocks['coords']['map_embed']))
                    <button class="btn btn-outline-primary" id="loadMap" data-analytics-click="load-map">{{ $locale === 'en' ? 'Load Map' : 'Haritayı Yükle' }}</button>
                    <div class="ratio ratio-16x9 mt-3 d-none" id="mapContainer"></div>
                @endif
            </div>
            <div class="col-md-7">
                @if(session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ $locale === 'en' ? url('/en/contact') : url('/iletisim') }}" data-analytics-section="contact-form">
                    @csrf
                    <input type="hidden" name="submitted_at" value="{{ time() }}">
                    <div class="mb-3">
                        <label class="form-label">{{ $locale === 'en' ? 'Name' : 'Ad Soyad' }}</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ $locale === 'en' ? 'E-mail' : 'E-posta' }}</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ $locale === 'en' ? 'Subject' : 'Konu' }}</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ $locale === 'en' ? 'Message' : 'Mesaj' }}</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="d-none">
                        <label>Leave empty</label>
                        <input type="text" name="website" autocomplete="off">
                    </div>
                    <button class="btn btn-primary" type="submit" data-analytics-click="contact-submit">{{ $locale === 'en' ? 'Send' : 'Gönder' }}</button>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const loadMapButton = document.getElementById('loadMap');
        if (loadMapButton) {
            loadMapButton.addEventListener('click', function () {
                const container = document.getElementById('mapContainer');
                if (!container.classList.contains('d-none')) return;
                container.classList.remove('d-none');
                container.innerHTML = `{!! addslashes($blocks['coords']['map_embed'] ?? '') !!}`;
                this.remove();
            });
        }
    </script>
@endpush
