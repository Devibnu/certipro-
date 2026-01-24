<!-- Hero Section Start -->
<div class="container-fluid p-0 mb-5">
    <div class="owl-carousel header-carousel position-relative">
        @if($bagian->itemBagianHalaman && $bagian->itemBagianHalaman->where('aktif', true)->count() > 0)
            @foreach($bagian->itemBagianHalaman->where('aktif', true)->sortBy('urutan') as $item)
            <div class="owl-carousel-item position-relative">
                @if($item->gambar)
                    <img class="img-fluid" src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->judul }}">
                @else
                    <img class="img-fluid" src="{{ asset('elearning-img/carousel-1.jpg') }}" alt="{{ $item->judul }}">
                @endif
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: rgba(24, 29, 56, .7);">
                    <div class="container">
                        <div class="row justify-content-start">
                            <div class="col-sm-10 col-lg-8">
                                @if($item->subjudul)
                                    <h5 class="text-primary text-uppercase mb-3 animated slideInDown">{{ $item->subjudul }}</h5>
                                @else
                                    <h5 class="text-primary text-uppercase mb-3 animated slideInDown">@if(systemCompanyName()){{ systemCompanyName() }} - @endif Lembaga Sertifikasi Profesi</h5>
                                @endif
                                <h1 class="display-3 text-white animated slideInDown">{{ $item->judul }}</h1>
                                @if($item->deskripsi)
                                    <p class="fs-5 text-white mb-4 pb-2">{{ $item->deskripsi }}</p>
                                @endif
                                @if($item->tombol_text && $item->tombol_link)
                                    <a href="{{ $item->tombol_link }}" class="btn btn-primary py-md-3 px-md-5 me-3 animated slideInLeft">{{ $item->tombol_text }}</a>
                                @endif
                                @if($item->tombol_text_2 && $item->tombol_link_2)
                                    <a href="{{ $item->tombol_link_2 }}" class="btn btn-light py-md-3 px-md-5 animated slideInRight">{{ $item->tombol_text_2 }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            {{-- Fallback jika belum ada item --}}
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
        @endif
    </div>
</div>
<!-- Hero Section End -->
