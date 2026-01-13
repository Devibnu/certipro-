<!-- FAQ Section Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
            <h6 class="section-title bg-white text-center text-primary px-3">FAQ</h6>
            <h1 class="mb-5">{{ $bagian->judul }}</h1>
        </div>
        
        @if($bagian->isi)
            <div class="row justify-content-center mb-4">
                <div class="col-lg-8 text-center">
                    <p class="mb-0">{!! nl2br(e($bagian->isi)) !!}</p>
                </div>
            </div>
        @endif
        
        @if($bagian->itemBagianHalaman && $bagian->itemBagianHalaman->count() > 0)
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion{{ $bagian->id }}">
                        @foreach($bagian->itemBagianHalaman->sortBy('urutan') as $index => $item)
                            <div class="accordion-item wow fadeInUp" data-wow-delay="{{ ($index * 0.1) + 0.1 }}s">
                                <h2 class="accordion-header" id="heading{{ $item->id }}">
                                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse{{ $item->id }}" 
                                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                            aria-controls="collapse{{ $item->id }}">
                                        <i class="fa fa-question-circle text-primary me-3"></i>
                                        {{ $item->judul }}
                                    </button>
                                </h2>
                                <div id="collapse{{ $item->id }}" 
                                     class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                                     aria-labelledby="heading{{ $item->id }}" 
                                     data-bs-parent="#faqAccordion{{ $bagian->id }}">
                                    <div class="accordion-body">
                                        @if($item->deskripsi)
                                            {!! nl2br(e($item->deskripsi)) !!}
                                        @else
                                            <em>Jawaban belum tersedia.</em>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- FAQ Section End -->
