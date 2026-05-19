@extends('layouts.error')

@section('title', '429 – Terlalu Banyak Percobaan')
@section('code', '429')
@section('icon')
<svg viewBox="0 0 24 24" width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
</svg>
@endsection
@section('heading', 'Terlalu Banyak Percobaan')
@section('message', 'Anda telah melakukan terlalu banyak percobaan. Silakan tunggu beberapa menit sebelum mencoba lagi.')
