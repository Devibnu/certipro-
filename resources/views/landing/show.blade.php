<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $systemName = systemCompanyName(); @endphp
    <title>{{ $halaman->judul }}{{ $systemName ? ' - ' . $systemName : '' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <!-- Header Halaman -->
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold">{{ $halaman->judul }}</h1>
                <hr class="w-25 mx-auto">
            </div>
        </div>

        <!-- Render Bagian-Bagian Halaman -->
        @foreach($halaman->bagianHalamanAktif as $bagian)
            <div class="row mb-5">
                <div class="col-12">
                    @if($bagian->tipe === 'hero')
                        <!-- Hero Section -->
                        <div class="p-5 bg-primary text-white rounded">
                            <h2 class="display-5">{{ $bagian->judul }}</h2>
                            @if($bagian->isi)
                                <p class="lead">{!! nl2br(e($bagian->isi)) !!}</p>
                            @endif
                        </div>

                    @elseif($bagian->tipe === 'teks')
                        <!-- Text Section -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h3 class="h4 mb-3">{{ $bagian->judul }}</h3>
                                @if($bagian->isi)
                                    <div class="text-muted">{!! nl2br(e($bagian->isi)) !!}</div>
                                @endif
                            </div>
                        </div>

                    @elseif($bagian->tipe === 'daftar')
                        <!-- List Section with Items -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h3 class="h4 mb-4">{{ $bagian->judul }}</h3>
                                @if($bagian->itemBagianHalaman->count() > 0)
                                    <div class="row g-4">
                                        @foreach($bagian->itemBagianHalaman as $item)
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-start">
                                                    @if($item->ikon)
                                                        <div class="flex-shrink-0">
                                                            <i class="{{ $item->ikon }} fa-2x text-primary me-3"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h5 class="mb-2">{{ $item->judul }}</h5>
                                                        @if($item->deskripsi)
                                                            <p class="text-muted mb-0">{{ $item->deskripsi }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                    @elseif($bagian->tipe === 'faq')
                        <!-- FAQ Section -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h3 class="h4 mb-4">{{ $bagian->judul }}</h3>
                                @if($bagian->itemBagianHalaman->count() > 0)
                                    <div class="accordion" id="faq-{{ $bagian->id }}">
                                        @foreach($bagian->itemBagianHalaman as $index => $item)
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" 
                                                            type="button" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#faq-item-{{ $item->id }}">
                                                        {{ $item->judul }}
                                                    </button>
                                                </h2>
                                                <div id="faq-item-{{ $item->id }}" 
                                                     class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                                                     data-bs-parent="#faq-{{ $bagian->id }}">
                                                    <div class="accordion-body">
                                                        {{ $item->deskripsi }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <!-- Back to Home -->
        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="{{ route('home') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
