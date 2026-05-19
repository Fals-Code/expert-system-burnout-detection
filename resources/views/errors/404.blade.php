@extends('layouts.error')

@section('title', '404 – Halaman Tidak Ditemukan')
@section('code', '404')
@section('icon')
<svg viewBox="0 0 24 24" width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/>
</svg>
@endsection
@section('heading', 'Halaman Tidak Ditemukan')
@section('message', 'Halaman yang Anda cari tidak ada atau telah dipindahkan. Periksa URL atau kembali ke halaman utama.')
