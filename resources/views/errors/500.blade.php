@extends('layouts.error')

@section('title', '500 – Kesalahan Server')
@section('code', '500')
@section('icon')
<svg viewBox="0 0 24 24" width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
</svg>
@endsection
@section('heading', 'Terjadi Kesalahan')
@section('message', 'Maaf, terjadi kesalahan pada server. Tim teknis telah diberitahu. Silakan coba lagi dalam beberapa saat.')
