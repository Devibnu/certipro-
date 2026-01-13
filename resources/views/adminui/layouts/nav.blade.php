<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Admin</a></li>
            <li class="breadcrumb-item text-sm text-dark active text-capitalize" aria-current="page">{{ str_replace(['adminui/', '-'], ['', ' '], Request::path()) }}</li>
            </ol>
            <h6 class="font-weight-bolder mb-0 text-capitalize">{{ str_replace(['adminui/', '-'], ['', ' '], Request::path()) }}</h6>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 d-flex justify-content-end" id="navbar"> 
            <ul class="navbar-nav justify-content-end">
                {{-- User Dropdown --}}
                <li class="nav-item dropdown pe-2 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0 d-flex align-items-center" id="dropdownUserButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar avatar-sm bg-gradient-primary me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <span class="text-white text-xs font-weight-bold">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
                        </div>
                        <div class="d-none d-sm-block">
                            <span class="font-weight-bold text-sm">{{ Auth::user()->name ?? 'Admin' }}</span>
                            <span class="badge badge-sm bg-gradient-{{ Auth::user()->role == 'Super Admin' ? 'dark' : (Auth::user()->role == 'Admin' ? 'info' : 'success') }} ms-1">{{ Auth::user()->role ?? 'User' }}</span>
                        </div>
                        <i class="fa fa-chevron-down ms-2 text-xs"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownUserButton" style="min-width: 230px;">
                        {{-- User Info Header --}}
                        <li class="mb-2">
                            <div class="dropdown-item border-radius-md bg-gradient-light">
                                <div class="d-flex py-1">
                                    <div class="avatar avatar-md bg-gradient-primary me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <span class="text-white font-weight-bold">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-bold mb-0">{{ Auth::user()->name ?? 'Admin' }}</h6>
                                        <p class="text-xs text-secondary mb-0">{{ Auth::user()->email ?? '' }}</p>
                                        <span class="badge badge-sm bg-gradient-{{ Auth::user()->role == 'Super Admin' ? 'dark' : (Auth::user()->role == 'Admin' ? 'info' : 'success') }} mt-1" style="width: fit-content;">{{ Auth::user()->role ?? 'User' }}</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        {{-- Profile Link --}}
                        <li class="mb-1">
                            <a class="dropdown-item border-radius-md d-flex align-items-center" href="{{ route('adminui.profile') }}">
                                <div class="icon icon-shape icon-xs bg-gradient-secondary shadow text-center border-radius-md me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                    <i class="fas fa-user text-white text-xs"></i>
                                </div>
                                <span class="text-sm">Profil Saya</span>
                            </a>
                        </li>
                        {{-- Settings Link --}}
                        <li class="mb-1">
                            <a class="dropdown-item border-radius-md d-flex align-items-center" href="{{ route('adminui.profile') }}">
                                <div class="icon icon-shape icon-xs bg-gradient-info shadow text-center border-radius-md me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                    <i class="fas fa-cog text-white text-xs"></i>
                                </div>
                                <span class="text-sm">Pengaturan</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        {{-- Logout --}}
                        <li>
                            <form method="POST" action="{{ route('adminui.logout') }}" id="nav-logout-form">
                                @csrf
                                <a class="dropdown-item border-radius-md d-flex align-items-center text-danger" href="#" id="btn-logout">
                                    <div class="icon icon-shape icon-xs bg-gradient-danger shadow text-center border-radius-md me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                        <i class="fas fa-sign-out-alt text-white text-xs"></i>
                                    </div>
                                    <span class="text-sm font-weight-bold">Keluar</span>
                                </a>
                            </form>
                        </li>
                    </ul>
                </li>

                {{-- Mobile Sidenav Toggle --}}
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                        </div>
                    </a>
                </li>

                {{-- Settings Button --}}
                <li class="nav-item px-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Pengaturan Tampilan">
                        <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
                    </a>
                </li>

                {{-- Notifications Dropdown --}}
                <li class="nav-item dropdown pe-2 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0 position-relative" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-bell cursor-pointer"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 8px;">
                            3
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton" style="min-width: 300px;">
                        <li class="mb-2">
                            <div class="dropdown-header d-flex justify-content-between align-items-center px-2">
                                <h6 class="text-sm font-weight-bold mb-0">Notifikasi</h6>
                                <span class="badge bg-gradient-primary">3 Baru</span>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="mb-2">
                            <a class="dropdown-item border-radius-md" href="javascript:;">
                                <div class="d-flex py-1">
                                    <div class="icon icon-shape icon-sm bg-gradient-success shadow text-center border-radius-md me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fas fa-certificate text-white text-xs"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            <span class="font-weight-bold">Pendaftaran Baru</span>
                                        </h6>
                                        <p class="text-xs text-secondary mb-0">
                                            <i class="fa fa-clock me-1"></i>
                                            13 menit yang lalu
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="mb-2">
                            <a class="dropdown-item border-radius-md" href="javascript:;">
                                <div class="d-flex py-1">
                                    <div class="icon icon-shape icon-sm bg-gradient-warning shadow text-center border-radius-md me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fas fa-clipboard-check text-white text-xs"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            <span class="font-weight-bold">Asesmen Selesai</span>
                                        </h6>
                                        <p class="text-xs text-secondary mb-0">
                                            <i class="fa fa-clock me-1"></i>
                                            1 jam yang lalu
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item border-radius-md" href="javascript:;">
                                <div class="d-flex py-1">
                                    <div class="icon icon-shape icon-sm bg-gradient-info shadow text-center border-radius-md me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fas fa-award text-white text-xs"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            <span class="font-weight-bold">Sertifikat Diterbitkan</span>
                                        </h6>
                                        <p class="text-xs text-secondary mb-0">
                                            <i class="fa fa-clock me-1"></i>
                                            2 hari yang lalu
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-center text-primary text-sm font-weight-bold" href="javascript:;">
                                Lihat Semua Notifikasi
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->
