<!-- Footer Start -->
<div class="container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-lg-3 col-md-6">
                <h4 class="text-white mb-3">Tautan Cepat</h4>
                <a class="btn btn-link" href="{{ url('/tentang') }}">Tentang Kami</a>
                <a class="btn btn-link" href="{{ url('/skema') }}">Skema Sertifikasi</a>
                <a class="btn btn-link" href="{{ url('/alur') }}">Alur Sertifikasi</a>
                <a class="btn btn-link" href="{{ url('/persyaratan') }}">Persyaratan</a>
                <a class="btn btn-link" href="{{ url('/kontak') }}">Hubungi Kami</a>
            </div>
            <div class="col-lg-3 col-md-6">
                <h4 class="text-white mb-3">Layanan Peserta</h4>
                <a class="btn btn-link" href="{{ route('daftar') }}"><i class="fa fa-angle-right me-2"></i>Daftar Sertifikasi</a>
                <a class="btn btn-link" href="{{ route('status-pra-pendaftaran.index') }}"><i class="fa fa-angle-right me-2"></i>Cek Status Pendaftaran</a>
                <a class="btn btn-link" href="{{ url('/verifikasi') }}"><i class="fa fa-angle-right me-2"></i>Verifikasi Sertifikat</a>
                <a class="btn btn-link" href="{{ url('/faq') }}"><i class="fa fa-angle-right me-2"></i>FAQ</a>
            </div>
            <div class="col-lg-3 col-md-6">
                <h4 class="text-white mb-3">Kontak</h4>
                <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>Jl. Sertifikasi No. 123, Jakarta</p>
                <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>(021) 1234-5678</p>
                <p class="mb-2"><i class="fa fa-envelope me-3"></i>info@lsp.id</p>
                <div class="d-flex pt-2">
                    <a class="btn btn-outline-light btn-social" href="#"><i class="fab fa-twitter"></i></a>
                    <a class="btn btn-outline-light btn-social" href="#"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-outline-light btn-social" href="#"><i class="fab fa-youtube"></i></a>
                    <a class="btn btn-outline-light btn-social" href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <h4 class="text-white mb-3">Newsletter</h4>
                <p>Dapatkan informasi terbaru tentang sertifikasi kompetensi.</p>
                <div class="position-relative mx-auto" style="max-width: 400px;">
                    <input class="form-control border-0 w-100 py-3 ps-4 pe-5" type="text" placeholder="Email Anda">
                    <button type="button" class="btn btn-primary py-2 position-absolute top-0 end-0 mt-2 me-2">Daftar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="copyright">
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    @php
                        $footerLogo = \App\Models\LogoAdmin::where('status', true)->first();
                        $footerName = $footerLogo && $footerLogo->nama_perusahaan && trim($footerLogo->nama_perusahaan) !== '' ? $footerLogo->nama_perusahaan : null;
                    @endphp
                    &copy; @if($footerName)<a class="border-bottom" href="{{ url('/') }}">{{ $footerName }}</a>@endif {{ date('Y') }}. All Rights Reserved.
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="footer-menu">
                        <a href="{{ url('/') }}">Beranda</a>
                        <a href="{{ url('/tentang') }}">Tentang</a>
                        <a href="{{ url('/kontak') }}">Kontak</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Footer End -->
