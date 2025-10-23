@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        <h1 class="mb-4">CMS Sayfaları</h1>
        <div class="card">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach($pages as $key => $page)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $page['label'] }}</strong>
                                <div class="text-muted small">{{ $key }}</div>
                            </div>
                            <a href="{{ route('cms.admin.pages.edit', $key) }}" class="btn btn-sm btn-primary">Düzenle</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
