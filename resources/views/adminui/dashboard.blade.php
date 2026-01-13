@extends('adminui.layouts.auth')

@section('title', 'Dashboard - Admin UI')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Users</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($totalUsers) }}
                                    @if($recentUsers > 0)
                                        <span class="text-success text-sm font-weight-bolder">+{{ $recentUsers }}</span>
                                    @endif
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="fas fa-users text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Projects</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($totalProjects) }}
                                    @if($recentProjects > 0)
                                        <span class="text-success text-sm font-weight-bolder">+{{ $recentProjects }}</span>
                                    @endif
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="fas fa-briefcase text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Blog Posts</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($totalPosts) }}
                                    @if($recentPosts > 0)
                                        <span class="text-success text-sm font-weight-bolder">+{{ $recentPosts }}</span>
                                    @endif
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="fas fa-newspaper text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Testimonials</p>
                                <h5 class="font-weight-bolder mb-0">{{ number_format($totalTestimonials) }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                <i class="fas fa-comment-dots text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Messages</p>
                                <h5 class="font-weight-bolder mb-0">{{ number_format($totalMessages) }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                <i class="fas fa-envelope text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Welcome Section --}}
    <div class="row mt-4">
        <div class="col-lg-12 mb-lg-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="d-flex flex-column h-100">
                                <p class="mb-1 pt-2 text-bold">Selamat Datang di</p>
                                <h5 class="font-weight-bolder">Admin Dashboard @if(systemCompanyName()){{ systemCompanyName() }}@endif</h5>
                                <p class="mb-4">Kelola semua konten sistem sertifikasi kompetensi dengan mudah. Dari halaman, users, skema, artikel hingga data sertifikasi.</p>
                                <div class="mb-3">
                                    <p class="text-sm mb-2">
                                        <i class="fas fa-check text-success me-2"></i> 
                                        <strong>{{ number_format($totalUsers) }}</strong> Users terdaftar
                                    </p>
                                    <p class="text-sm mb-2">
                                        <i class="fas fa-check text-success me-2"></i> 
                                        <strong>{{ number_format($totalProjects) }}</strong> Skema sertifikasi aktif
                                    </p>
                                    <p class="text-sm mb-2">
                                        <i class="fas fa-check text-success me-2"></i> 
                                        <strong>{{ number_format($totalPosts) }}</strong> Artikel dipublikasikan
                                    </p>
                                    <p class="text-sm mb-0">
                                        <i class="fas fa-check text-success me-2"></i> 
                                        <strong>{{ number_format($totalTestimonials) }}</strong> Testimonial klien
                                    </p>
                                </div>
                                @if($recentUsers > 0 || $recentProjects > 0 || $recentPosts > 0)
                                <div class="alert alert-success mb-0" role="alert">
                                    <strong>Update Terbaru (7 Hari Terakhir):</strong><br>
                                    @if($recentUsers > 0)
                                        • {{ $recentUsers }} User baru<br>
                                    @endif
                                    @if($recentProjects > 0)
                                        • {{ $recentProjects }} Project baru<br>
                                    @endif
                                    @if($recentPosts > 0)
                                        • {{ $recentPosts }} Blog Post baru
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-5 ms-auto text-center mt-5 mt-lg-0">
                            <div class="bg-gradient-primary border-radius-lg h-100">
                                <img src="{{ asset('assets/img/shapes/waves-white.svg') }}" class="position-absolute h-100 w-50 top-0 d-lg-block d-none" alt="waves">
                                <div class="position-relative d-flex align-items-center justify-content-center h-100">
                                    <img class="w-100 position-relative z-index-2 pt-4" src="{{ asset('assets/img/illustrations/rocket-white.png') }}" alt="rocket">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Access Section --}}
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Quick Access</h6>
                    <p class="text-sm mb-0">Akses cepat ke menu yang sering digunakan</p>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('adminui.users.index') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-users me-2"></i>
                                Manage Users
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('adminui.projects.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-briefcase me-2"></i>
                                Manage Projects
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('adminui.blog.index') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-newspaper me-2"></i>
                                Manage Blog
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('adminui.contact') }}" class="btn btn-outline-danger w-100">
                                <i class="fas fa-envelope me-2"></i>
                                Contact Page
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
