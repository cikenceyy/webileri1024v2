@extends('layouts.admin')

@section('title', 'İş Emirleri')
@section('module', 'Production')

@section('content')



    @php
        $tableKitConfig    = $tableKitConfig    ?? ['columns' => []];
        $tableKitRows      = $tableKitRows      ?? [];
        $tableKitPaginator = $tableKitPaginator ?? null;
    @endphp
    <div class="card">
        <x-table :config="$tableKitConfig" :rows="$tableKitRows" :paginator="$tableKitPaginator">
            <x-slot name="toolbar">
                <x-table:toolbar :config="$tableKitConfig" :search-placeholder="__('İş emri ara…')" />
            </x-slot>
        </x-table>
    </div>
@endsection
