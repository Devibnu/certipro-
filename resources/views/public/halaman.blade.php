@extends('layouts.elearning')

@php 
    $logoAdminForTitle = \App\Models\LogoAdmin::where('status', true)->first();
    $systemTagline = $logoAdminForTitle && $logoAdminForTitle->tagline && trim($logoAdminForTitle->tagline) !== '' ? $logoAdminForTitle->tagline : null;
@endphp
@section('title', $halaman->judul . ($systemTagline ? ' - ' . $systemTagline : ''))

@section('content')
    @foreach($halaman->bagianHalamanAktif->sortBy('urutan') as $bagian)
        @include('partials.elearning.sections.' . $bagian->tipe, ['bagian' => $bagian])
    @endforeach
@endsection
