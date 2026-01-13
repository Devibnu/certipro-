<!-- List Section Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
            <h6 class="section-title bg-white text-center text-primary px-3">{{ $bagian->judul }}</h6>
            <h1 class="mb-5">{{ $bagian->judul }}</h1>
        </div>
        
        @if($bagian->isi)
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <p class="mb-0">{!! nl2br(e($bagian->isi)) !!}</p>
                </div>
            </div>
        @endif
        
        @if($bagian->itemBagianHalaman && $bagian->itemBagianHalaman->count() > 0)
            <div class="row g-4 justify-content-center">
                @foreach($bagian->itemBagianHalaman->sortBy('urutan') as $index => $item)
                    @php
                        $delays = ['0.1s', '0.3s', '0.5s', '0.7s'];
                        $delay = $delays[$index % 4];
                        $colors = ['bg-primary', 'bg-secondary', 'bg-success', 'bg-warning'];
                        $color = $colors[$index % 4];
                    @endphp
                    <div class="col-lg-4 col-sm-6 wow fadeInUp" data-wow-delay="{{ $delay }}">
                        <div class="service-item text-center pt-3">
                            <div class="p-4">
                                <i class="{{ $item->ikon ?: 'fa fa-3x fa-check-circle' }} text-primary mb-4" style="font-size: 3rem;"></i>
                                <h5 class="mb-3">{{ $item->judul }}</h5>
                                @if($item->deskripsi)
                                    <p>{{ $item->deskripsi }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
<!-- List Section End -->
