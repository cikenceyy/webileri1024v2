@extends('layouts.admin')

@section('title', 'Today Board')
@section('page', 'TodayBoard')

@section('content')
    <div class="container py-3">
        <h1 class="h4 mb-3">Günlük Kontrol Kulesi</h1>
        <x-ui.table>
            @slot('thead')
                <tr>
                    <th scope="col">Blok</th>
                    <th scope="col">Değer</th>
                </tr>
            @endslot
            @slot('tbody')
                <tr>
                    <td>Açık Siparişler</td>
                    <td>{{ $summary['orders'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td>Sevkiyat</td>
                    <td>{{ $summary['shipments'] ?? 0 }}</td>
                </tr>
            @endslot
        </x-ui.table>
    </div>
@endsection
