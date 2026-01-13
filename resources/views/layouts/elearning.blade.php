<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    @php
        $logoAdminForTitle = \App\Models\LogoAdmin::where('status', true)->first();
        $systemTagline = $logoAdminForTitle && $logoAdminForTitle->tagline && trim($logoAdminForTitle->tagline) !== '' ? $logoAdminForTitle->tagline : null;
        $defaultTitle = $systemTagline ? $systemTagline : 'Lembaga Sertifikasi Profesi';
    @endphp
    <title>@yield('title', $defaultTitle)</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="@yield('meta_keywords', 'sertifikasi, kompetensi, LSP')" name="keywords">
    <meta content="@yield('meta_description', 'Lembaga Sertifikasi Profesi terpercaya untuk pengembangan kompetensi profesional.')" name="description">

    @php
        $logoAdmin = \App\Models\LogoAdmin::where('status', true)->first();
        $logoUrl = $logoAdmin && $logoAdmin->gambar ? asset('storage/' . $logoAdmin->gambar) : null;
        $logoInfo = $logoAdmin ? [
            'nama' => $logoAdmin->nama_perusahaan && trim($logoAdmin->nama_perusahaan) !== '' ? $logoAdmin->nama_perusahaan : null,
            'tagline' => $logoAdmin->tagline,
            'timestamp' => $logoAdmin->updated_at->timestamp ?? time(),
        ] : [
            'nama' => null,
            'tagline' => null,
            'timestamp' => time(),
        ];
    @endphp

    <!-- Favicon -->
    @if($logoUrl)
    <link href="{{ $logoUrl }}?v={{ $logoInfo['timestamp'] }}" rel="icon" type="image/png">
    @else
    <link href="{{ asset('elearning-img/favicon.ico') }}" rel="icon">
    @endif

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('elearning-lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('elearning-lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('elearning-css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{ asset('elearning-css/style.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    @include('partials.elearning.header')

    @yield('content')

    @include('partials.elearning.footer')

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('elearning-lib/wow/wow.min.js') }}"></script>
    <script src="{{ asset('elearning-lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('elearning-lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('elearning-lib/owlcarousel/owl.carousel.min.js') }}"></script>

    <!-- Template Javascript -->
    <script src="{{ asset('elearning-js/main.js') }}"></script>
    
    @stack('scripts')
</body>

</html>
