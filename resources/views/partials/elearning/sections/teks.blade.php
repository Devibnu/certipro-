<!-- Text Section Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s" style="min-height: 400px;">
                <div class="position-relative h-100">
                    <img class="img-fluid position-absolute w-100 h-100" src="{{ asset('elearning-img/about.jpg') }}" alt="{{ $bagian->judul }}" style="object-fit: cover;">
                </div>
            </div>
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
                <h6 class="section-title bg-white text-start text-primary pe-3">{{ $bagian->judul }}</h6>
                <h1 class="mb-4">{{ $bagian->judul }}</h1>
                @if($bagian->isi)
                    <div class="mb-4">
                        {!! nl2br(e($bagian->isi)) !!}
                    </div>
                @endif
                
                @if($bagian->itemBagianHalaman && $bagian->itemBagianHalaman->count() > 0)
                    <div class="row gy-2 gx-4 mb-4">
                        @foreach($bagian->itemBagianHalaman->sortBy('urutan') as $item)
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="{{ $item->ikon ?: 'fa fa-arrow-right' }} text-primary me-2"></i>{{ $item->judul }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <a class="btn btn-primary py-3 px-5 mt-2" href="{{ url('/kontak') }}">Hubungi Kami</a>
            </div>
        </div>
    </div>
</div>
<!-- Text Section End -->
