<!-- Hero Section Start -->
<div class="container-fluid p-0 mb-5">
    <div class="owl-carousel header-carousel position-relative">
        <div class="owl-carousel-item position-relative">
            <img class="img-fluid" src="{{ asset('elearning-img/carousel-1.jpg') }}" alt="{{ $bagian->judul }}">
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: rgba(24, 29, 56, .7);">
                <div class="container">
                    <div class="row justify-content-start">
                        <div class="col-sm-10 col-lg-8">
                            <h5 class="text-primary text-uppercase mb-3 animated slideInDown">@if(systemCompanyName()){{ systemCompanyName() }} - @endif Lembaga Sertifikasi Profesi</h5>
                            <h1 class="display-3 text-white animated slideInDown">{{ $bagian->judul }}</h1>
                            @if($bagian->isi)
                                <p class="fs-5 text-white mb-4 pb-2">{{ $bagian->isi }}</p>
                            @endif
                            <a href="{{ url('/tentang') }}" class="btn btn-primary py-md-3 px-md-5 me-3 animated slideInLeft">Pelajari Lebih Lanjut</a>
                            <a href="{{ route('daftar') }}" class="btn btn-light py-md-3 px-md-5 animated slideInRight">Daftar Sekarang</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="owl-carousel-item position-relative">
            <img class="img-fluid" src="{{ asset('elearning-img/carousel-2.jpg') }}" alt="{{ $bagian->judul }}">
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: rgba(24, 29, 56, .7);">
                <div class="container">
                    <div class="row justify-content-start">
                        <div class="col-sm-10 col-lg-8">
                            <h5 class="text-primary text-uppercase mb-3 animated slideInDown">Sertifikasi Kompetensi Terpercaya</h5>
                            <h1 class="display-3 text-white animated slideInDown">Tingkatkan Karir Anda Bersama Kami</h1>
                            <p class="fs-5 text-white mb-4 pb-2">Dapatkan sertifikat kompetensi yang diakui secara nasional untuk membuka peluang karir yang lebih baik.</p>
                            <a href="{{ url('/skema') }}" class="btn btn-primary py-md-3 px-md-5 me-3 animated slideInLeft">Lihat Skema</a>
                            <a href="{{ url('/alur') }}" class="btn btn-light py-md-3 px-md-5 animated slideInRight">Alur Sertifikasi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Hero Section End -->
