@extends('layouts.admin')

@php($module = 'Marketing')
@php($page = 'Demo')
@section('module', $module)
@section('page', $page)

@section('title', 'Marketing Demo')

@section('content')
    <div class="container py-4">
        <section class="mb-4" data-marketing-hero>
            <h1 class="h4 mb-2">Marketing Modülü</h1>
            <p class="text-secondary mb-0">Module Loader çalıştı ve marketing::demo görünümü yüklendi.</p>
        </section>
        <x-ui-button icon="bi bi-megaphone" variant="primary">Yeni Kampanya</x-ui-button>
    </div>
@endsection
