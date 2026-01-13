@extends('adminui.layouts.guest')

@php
    $logoAdminForTitle = \App\Models\LogoAdmin::where('status', true)->first();
    $systemTagline = $logoAdminForTitle && $logoAdminForTitle->tagline && trim($logoAdminForTitle->tagline) !== '' ? $logoAdminForTitle->tagline : null;
@endphp
@section('title', 'Reset Password' . ($systemTagline ? ' - ' . $systemTagline : ''))

@section('content')
<main class="main-content mt-0">
    <div class="page-header align-items-start min-vh-100" style="background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1950&q=80');">
        <span class="mask bg-gradient-dark opacity-6"></span>
        <div class="container my-auto">
            <div class="row signin-margin">
                <div class="col-lg-4 col-md-8 col-12 mx-auto">
                    <div class="card z-index-0 fadeIn3 fadeInBottom">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                                <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Reset Password</h4>
                                <p class="text-white text-center text-sm mt-2 mb-0 px-3">
                                    Masukkan password baru Anda
                                </p>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Error Messages --}}
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible text-white" role="alert">
                                    <span class="text-sm">
                                        @foreach ($errors->all() as $error)
                                            {{ $error }}<br>
                                        @endforeach
                                    </span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <form role="form" method="POST" action="{{ route('password.store') }}" class="text-start">
                                @csrf

                                {{-- Password Reset Token --}}
                                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                                
                                {{-- Email --}}
                                <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

                                {{-- Password Requirements Info --}}
                                <div class="alert alert-info text-white text-sm mb-3" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Ketentuan Password:</strong>
                                    <ul class="mb-0 mt-1 ps-3">
                                        <li>Minimal 8 karakter</li>
                                        <li>Mengandung huruf besar (A-Z)</li>
                                        <li>Mengandung huruf kecil (a-z)</li>
                                        <li>Mengandung angka (0-9)</li>
                                    </ul>
                                </div>
                                
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" name="password" required autocomplete="new-password">
                                </div>
                                
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Konfirmasi Password</label>
                                    <input type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">
                                        <i class="fas fa-key me-2"></i>Reset Password
                                    </button>
                                </div>
                                
                                <p class="mt-4 text-sm text-center">
                                    Ingat password Anda?
                                    <a href="{{ route('login') }}" class="text-primary text-gradient font-weight-bold">Kembali ke Login</a>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
