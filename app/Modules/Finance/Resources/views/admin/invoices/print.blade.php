@extends('layouts.print')

@section('title', __('Fatura Yazdır'))

@section('content')
    @include($templateView, ['invoice' => $invoice, 'requestedTemplate' => $requestedTemplate ?? null])
@endsection
