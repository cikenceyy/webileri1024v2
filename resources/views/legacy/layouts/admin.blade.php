{{-- Legacy shim to keep existing extends('legacy.layouts.admin') working. --}}
@extends('layouts.admin')

@section('title')
    @yield('title')
@endsection

@section('module')
    @yield('module')
@endsection

@section('section')
    @yield('section')
@endsection

@section('content')
    @yield('content')
@endsection
