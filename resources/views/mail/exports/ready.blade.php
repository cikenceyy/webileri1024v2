{{-- Export hazır olduğunda kullanıcıya gönderilen sade mail şablonu. --}}
@php($downloadUrl = $export->file_path ? route('admin.exports.download', $export) : null)
<p>{{ __('Merhaba,') }}</p>
<p>{{ __('":table" tablosu için başlattığınız veri dışa aktarma işlemi tamamlandı.', ['table' => $export->table_key]) }}</p>
@if($downloadUrl)
    <p>
        <a href="{{ $downloadUrl }}">{{ __('Dosyayı indirmek için buraya tıklayın.') }}</a>
    </p>
@endif
<p>{{ __('İşlem kimliği: :id', ['id' => $export->id]) }}</p>
<p>{{ __('Görüşmek üzere, Webileri 1024 ekibi') }}</p>
