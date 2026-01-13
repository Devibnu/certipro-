<!DOCTYPE html>
<html lang="en">
<head>
    @php $systemName = systemCompanyName(); @endphp
    <title>@yield('title', $systemName ?? 'LSP')</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    @php
        // Get logo from LogoAdmin (branding system) as primary source
        $logoAdmin = \App\Models\LogoAdmin::where('status', true)->first();
        $logoUrl = $logoAdmin && $logoAdmin->gambar ? asset('storage/' . $logoAdmin->gambar) : null;
        $logoInfo = $logoAdmin ? [
            'nama' => $logoAdmin->nama_perusahaan ?: null,
            'tagline' => $logoAdmin->tagline,
            'timestamp' => $logoAdmin->updated_at->timestamp ?? time(),
        ] : [
            'nama' => null,
            'tagline' => null,
            'timestamp' => time(),
        ];
        
        // Favicon - use logo as favicon if no dedicated favicon
        try {
            $activeFavicon = \App\Models\FaviconWebsite::where('status', 1)->first();
        } catch (\Exception $e) {
            $activeFavicon = null;
        }
    @endphp
    @if($activeFavicon && $activeFavicon->favicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $activeFavicon->favicon) }}?v={{ $activeFavicon->updated_at->timestamp }}">
    @elseif($logoUrl)
        <link rel="icon" type="image/png" href="{{ $logoUrl }}?v={{ $logoInfo['timestamp'] }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    @endif
    
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,600,700,800,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('website/css/open-iconic-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/animate.css') }}">
    
    <link rel="stylesheet" href="{{ asset('website/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/magnific-popup.css') }}">

    <link rel="stylesheet" href="{{ asset('website/css/aos.css') }}">

    <link rel="stylesheet" href="{{ asset('website/css/ionicons.min.css') }}">
    
    <link rel="stylesheet" href="{{ asset('website/css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/icomoon.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/style.css') }}">

    @stack('styles')
</head>
<body>
    @php
        // Get active header info from database
        \Illuminate\Support\Facades\DB::connection()->disableQueryLog();
        $headerInfo = \App\Models\HeaderInfo::where('status', true)->orderBy('updated_at', 'desc')->first();
    @endphp
    
    <!-- Top Header -->
    <div class="bg-top navbar-light">
        <div class="container">
            <div class="row no-gutters d-flex align-items-center align-items-stretch">
                <div class="col-md-4 d-flex align-items-center py-4">
                    <a class="navbar-brand" href="{{ route('home') }}" style="display: flex; align-items: center;">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}?v={{ $logoInfo['timestamp'] }}&t={{ time() }}" alt="{{ $logoInfo['nama'] }}" style="max-height: 50px; margin-right: 10px; object-fit: contain;">
                        @endif
                        <span>{{ $logoInfo['nama'] }}</span>
                    </a>
                </div>
                <div class="col-lg-8 d-block">
                    <div class="row d-flex">
                        <div class="col-md d-flex topper align-items-center align-items-stretch py-md-4">
                            <div class="icon d-flex justify-content-center align-items-center"><span class="icon-paper-plane"></span></div>
                            <div class="text">
                                <span>Email</span>
                                @if($headerInfo)
                                    <span>{{ $headerInfo->email }}</span>
                                @else
                                    <span>info@lsp.id</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md d-flex topper align-items-center align-items-stretch py-md-4">
                            <div class="icon d-flex justify-content-center align-items-center"><span class="icon-phone2"></span></div>
                            <div class="text">
                                <span>Call</span>
                                @if($headerInfo)
                                    <span>Call Us: {{ $headerInfo->telepon }}</span>
                                @else
                                    <span>Call Us: +62 812 3456 7890</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md topper d-flex align-items-center justify-content-end">
                            <p class="mb-0">
                                @if($headerInfo)
                                    <a href="{{ $headerInfo->cta_link }}" class="btn py-2 px-3 btn-primary d-flex align-items-center justify-content-center">
                                        <span>{{ $headerInfo->cta_text }}</span>
                                    </a>
                                @else
                                    <a href="{{ route('contact') }}" class="btn py-2 px-3 btn-primary d-flex align-items-center justify-content-center">
                                        <span>Free Consulting</span>
                                    </a>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark ftco-navbar-light" id="ftco-navbar">
        <div class="container d-flex align-items-center px-4">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="oi oi-menu"></span> Menu
            </button>
            <form action="#" class="searchform order-lg-last">
                <div class="form-group d-flex">
                    <input type="text" class="form-control pl-3" placeholder="Search">
                    <button type="submit" placeholder="" class="form-control search"><span class="ion-ios-search"></span></button>
                </div>
            </form>
            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                        <a href="{{ route('home') }}" class="nav-link pl-0">Home</a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('about') ? 'active' : '' }}">
                        <a href="{{ route('about') }}" class="nav-link">About</a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('project*') ? 'active' : '' }}">
                        <a href="{{ route('project') }}" class="nav-link">Projects</a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('services*') ? 'active' : '' }}">
                        <a href="{{ route('services') }}" class="nav-link">Services</a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('blog*') ? 'active' : '' }}">
                        <a href="{{ route('blog.index') }}" class="nav-link">Blog</a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('contact') ? 'active' : '' }}">
                        <a href="{{ route('contact') }}" class="nav-link">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- END nav -->

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    <footer class="ftco-footer ftco-bg-dark ftco-section">
        <div class="container">
            <div class="row mb-5">
                <div class="col-md-6 col-lg-3">
                    <div class="ftco-footer-widget mb-5">
                        <h2 class="ftco-heading-2">Have a Questions?</h2>
                        <div class="block-23 mb-3">
                            <ul>
                                <li><span class="icon icon-map-marker"></span><span class="text">Jakarta, Indonesia</span></li>
                                <li><a href="#"><span class="icon icon-phone"></span><span class="text">+62 812 3456 7890</span></a></li>
                                <li><a href="#"><span class="icon icon-envelope"></span><span class="text">info@jasaibnu.id</span></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="ftco-footer-widget mb-5 ml-md-4">
                        <h2 class="ftco-heading-2">Links</h2>
                        <ul class="list-unstyled">
                            <li><a href="{{ route('home') }}"><span class="ion-ios-arrow-round-forward mr-2"></span>Home</a></li>
                            <li><a href="{{ route('about') }}"><span class="ion-ios-arrow-round-forward mr-2"></span>About</a></li>
                            <li><a href="{{ route('services') }}"><span class="ion-ios-arrow-round-forward mr-2"></span>Services</a></li>
                            <li><a href="{{ route('project') }}"><span class="ion-ios-arrow-round-forward mr-2"></span>Projects</a></li>
                            <li><a href="{{ route('contact') }}"><span class="ion-ios-arrow-round-forward mr-2"></span>Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="ftco-footer-widget mb-5">
                        <h2 class="ftco-heading-2">Subscribe Us!</h2>
                        <form action="#" class="subscribe-form">
                            <div class="form-group">
                                <input type="text" class="form-control mb-2 text-center" placeholder="Enter email address">
                                <input type="submit" value="Subscribe" class="form-control submit px-3">
                            </div>
                        </form>
                    </div>
                    <div class="ftco-footer-widget mb-5">
                        <h2 class="ftco-heading-2 mb-0">Connect With Us</h2>
                        <ul class="ftco-footer-social list-unstyled float-md-left float-lft mt-3">
                            <li class="ftco-animate"><a href="#"><span class="icon-twitter"></span></a></li>
                            <li class="ftco-animate"><a href="#"><span class="icon-facebook"></span></a></li>
                            <li class="ftco-animate"><a href="#"><span class="icon-instagram"></span></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <p>Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | This template is made with <i class="icon-heart" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a></p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- loader -->
    <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>

    <!-- Scripts -->
    <script src="{{ asset('website/js/jquery.min.js') }}"></script>
    <script src="{{ asset('website/js/jquery-migrate-3.0.1.min.js') }}"></script>
    <script src="{{ asset('website/js/popper.min.js') }}"></script>
    <script src="{{ asset('website/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('website/js/jquery.easing.1.3.js') }}"></script>
    <script src="{{ asset('website/js/jquery.waypoints.min.js') }}"></script>
    <script src="{{ asset('website/js/jquery.stellar.min.js') }}"></script>
    <script src="{{ asset('website/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('website/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('website/js/aos.js') }}"></script>
    <script src="{{ asset('website/js/jquery.animateNumber.min.js') }}"></script>
    <script src="{{ asset('website/js/scrollax.min.js') }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
    <script src="{{ asset('website/js/google-map.js') }}"></script>
    <script src="{{ asset('website/js/main.js') }}"></script>

    @stack('scripts')
</body>
</html>