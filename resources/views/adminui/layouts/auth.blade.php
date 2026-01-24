<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale-1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="notification-route" content="{{ route('adminui.notifications.recent') }}">
  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
  @php
    // Get logo from branding system (LogoAdmin)
    $logoAdmin = \App\Models\LogoAdmin::where('status', true)->first();
    $faviconUrl = $logoAdmin && $logoAdmin->gambar ? asset('storage/' . $logoAdmin->gambar) : null;
    $faviconTimestamp = $logoAdmin && $logoAdmin->updated_at ? $logoAdmin->updated_at->timestamp : time();
    $systemTagline = $logoAdmin && $logoAdmin->tagline && trim($logoAdmin->tagline) !== '' ? $logoAdmin->tagline : null;
  @endphp
  @if($faviconUrl)
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}?v={{ $faviconTimestamp }}">
  @else
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
  @endif
  <title>
    @if($systemTagline){{ $systemTagline }} - @endif Admin Dashboard
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
  <!-- CSS Files -->
  <link id="pagestyle" href="{{ asset('assets/css/soft-ui-dashboard.css?v=1.0.3') }}" rel="stylesheet" />
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    /* Submenu active state - matching Blog submenu style */
    .navbar-vertical .navbar-nav .nav .nav-item .nav-link.active {
      background-color: transparent;
      font-weight: 600;
    }
    .navbar-vertical .navbar-nav .nav .nav-item .nav-link.active .sidenav-mini-icon,
    .navbar-vertical .navbar-nav .nav .nav-item .nav-link.active .sidenav-normal {
      color: #344767;
      font-weight: 600;
    }
  </style>
  @stack('styles')
</head>

<body class="g-sidenav-show bg-gray-100 {{ (\Request::is('adminui/rtl') ? 'rtl' : (Request::is('adminui/virtual-reality') ? 'virtual-reality' : '')) }}">
  
  <!-- Success/Error Notifications -->
  @if (session('success'))
  <script>
  document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: "{{ session('success') }}",
          confirmButtonColor: '#3085d6',
          timer: 3000,
          showConfirmButton: false
      });
  });
  </script>
  @endif

  @if (session('error'))
  <script>
  document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
          icon: 'error',
          title: 'Terjadi Kesalahan!',
          text: "{{ session('error') }}",
          confirmButtonColor: '#d33'
      });
  });
  </script>
  @endif
  
  @if (\Request::is('adminui/rtl'))  
    @include('adminui.layouts.sidebar-rtl')
    <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg overflow-hidden">
      @include('adminui.layouts.nav-rtl')
      <div class="container-fluid py-4">
        @yield('content')
        @include('adminui.layouts.footer')
      </div>
    </main>

  @elseif (\Request::is('adminui/profile'))  
    @include('adminui.layouts.sidebar')
    <div class="main-content position-relative bg-gray-100 max-height-vh-100 h-100">
      @include('adminui.layouts.nav')
      @yield('content')
    </div>

  @elseif (\Request::is('adminui/virtual-reality')) 
    @include('adminui.layouts.nav')
    <div class="border-radius-xl mt-3 mx-3 position-relative" style="background-image: url('{{ asset('assets/img/vr-bg.jpg') }}') ; background-size: cover;">
      @include('adminui.layouts.sidebar')
      <main class="main-content mt-1 border-radius-lg">
        @yield('content')
      </main>
    </div>
    @include('adminui.layouts.footer')

  @else
    @include('adminui.layouts.sidebar')
    <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg {{ (Request::is('adminui/rtl') ? 'overflow-hidden' : '') }}">
      @include('adminui.layouts.nav')
      <div class="container-fluid py-4">
        @yield('content')
        @include('adminui.layouts.footer')
      </div>
    </main>
  @endif

  @include('adminui.layouts.fixed-plugin')
  @include('adminui.layouts.scripts')
  
  <!-- Logout Confirmation Script -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
      const btnLogout = document.getElementById('btn-logout');
      const formLogout = document.getElementById('nav-logout-form');
      
      if (btnLogout && formLogout) {
          btnLogout.addEventListener('click', function(e) {
              e.preventDefault();
              
              Swal.fire({
                  title: 'Konfirmasi Keluar',
                  html: `
                      <div class="text-center">
                          <div class="mb-3">
                              <i class="fas fa-sign-out-alt fa-3x text-danger"></i>
                          </div>
                          <p class="mb-0">Apakah Anda yakin ingin keluar dari sistem?</p>
                      </div>
                  `,
                  icon: null,
                  showCancelButton: true,
                  confirmButtonColor: '#f5365c',
                  cancelButtonColor: '#6c757d',
                  confirmButtonText: '<i class="fas fa-sign-out-alt me-1"></i> Ya, Keluar',
                  cancelButtonText: '<i class="fas fa-times me-1"></i> Batal',
                  reverseButtons: true,
                  customClass: {
                      popup: 'swal-logout-popup'
                  }
              }).then((result) => {
                  if (result.isConfirmed) {
                      Swal.fire({
                          title: 'Logging out...',
                          text: 'Mohon tunggu sebentar',
                          allowOutsideClick: false,
                          allowEscapeKey: false,
                          showConfirmButton: false,
                          didOpen: () => {
                              Swal.showLoading();
                          }
                      });
                      formLogout.submit();
                  }
              });
          });
      }
  });
  </script>
  <style>
  .swal-logout-popup {
      max-width: 380px !important;
  }
  </style>
  
  <!-- Notification Bell System -->
  <script src="{{ asset('assets/js/notification-bell.js') }}"></script>
  
  @stack('scripts')
</body>

</html>