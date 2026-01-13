@extends('frontend.layouts.app')

@php $systemName = systemCompanyName(); @endphp
@section('title', 'Blog' . ($systemName ? ' - ' . $systemName . ' Sertifikasi' : ' - Sertifikasi'))

@section('content')
    <section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('website/images/bg_1.jpg') }}');">
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
          <div class="col-md-9 ftco-animate text-center">
            <h1 class="mb-2 bread">Blog</h1>
            <p class="breadcrumbs"><span class="mr-2"><a href="{{ url('/') }}">Home <i class="ion-ios-arrow-forward"></i></a></span> <span>Blog <i class="ion-ios-arrow-forward"></i></span></p>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section bg-light">
        <div class="container">
            @if($artikels->count() > 0)
                <div class="row">
                    @foreach($artikels as $artikel)
                        <div class="col-md-6 col-lg-4 ftco-animate">
                            <div class="blog-entry">
                                <a href="{{ route('blog.detail', $artikel->slug) }}" class="block-20 d-flex align-items-end" style="background-image: url('{{ $artikel->gambar ? Storage::url($artikel->gambar) : asset('website/images/image_1.jpg') }}');">
                                    <div class="meta-date text-center p-2">
                                        <span class="day">{{ $artikel->tanggal_dibuat->format('d') }}</span>
                                        <span class="mos">{{ $artikel->tanggal_dibuat->format('M') }}</span>
                                        <span class="yr">{{ $artikel->tanggal_dibuat->format('Y') }}</span>
                                    </div>
                                </a>
                                <div class="text bg-white p-4">
                                    <h3 class="heading"><a href="{{ route('blog.detail', $artikel->slug) }}">{{ $artikel->judul }}</a></h3>
                                    <p>{{ Str::limit($artikel->ringkasan ?: strip_tags($artikel->isi), 150) }}</p>
                                    <div class="d-flex align-items-center mt-4">
                                        <p class="mb-0"><a href="{{ route('blog.detail', $artikel->slug) }}" class="btn btn-primary">Read More <span class="ion-ios-arrow-round-forward"></span></a></p>
                                        <p class="ml-auto mb-0">
                                            <a href="#" class="mr-2">Admin</a>
                                            <a href="{{ route('blog.detail', $artikel->slug) }}#comments" class="meta-chat"><span class="icon-chat"></span> {{ $artikel->komentars()->where('status', 'approved')->count() }}</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row mt-5">
                    <div class="col text-center">
                        <div class="block-27">
                            {{ $artikels->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-newspaper text-muted mb-3" style="font-size: 4rem;"></i>
                        <h3 class="text-muted">Belum Ada Artikel</h3>
                        <p class="text-muted">Artikel blog akan segera hadir. Pantau terus untuk tips dan insights terbaru!</p>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
