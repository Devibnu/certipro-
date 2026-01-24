@extends('adminui.layouts.auth')

@section('title', 'Edit Bagian Halaman - CMS Landing Page')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('adminui.bagian-halaman.index', ['halaman_id' => $bagianHalaman->halaman_id]) }}" 
                           class="btn btn-outline-secondary btn-sm me-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h6 class="mb-0">Edit Bagian: {{ $bagianHalaman->judul }}</h6>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('adminui.bagian-halaman.update', $bagianHalaman) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="halaman_id" class="form-control-label">Halaman <span class="text-danger">*</span></label>
                                    <select class="form-select @error('halaman_id') is-invalid @enderror" 
                                            id="halaman_id" name="halaman_id" required>
                                        <option value="">-- Pilih Halaman --</option>
                                        @foreach($halamanList as $h)
                                            <option value="{{ $h->id }}" {{ old('halaman_id', $bagianHalaman->halaman_id) == $h->id ? 'selected' : '' }}>
                                                {{ $h->judul }} ({{ $h->slug }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('halaman_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipe" class="form-control-label">Tipe Bagian <span class="text-danger">*</span></label>
                                    <select class="form-select @error('tipe') is-invalid @enderror" 
                                            id="tipe" name="tipe" required>
                                        <option value="">-- Pilih Tipe --</option>
                                        <option value="hero" {{ old('tipe', $bagianHalaman->tipe) == 'hero' ? 'selected' : '' }}>Hero (Banner utama)</option>
                                        <option value="teks" {{ old('tipe', $bagianHalaman->tipe) == 'teks' ? 'selected' : '' }}>Teks (Paragraf biasa)</option>
                                        <option value="daftar" {{ old('tipe', $bagianHalaman->tipe) == 'daftar' ? 'selected' : '' }}>Daftar (List dengan item)</option>
                                        <option value="faq" {{ old('tipe', $bagianHalaman->tipe) == 'faq' ? 'selected' : '' }}>FAQ (Pertanyaan & Jawaban)</option>
                                    </select>
                                    @error('tipe')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="judul" class="form-control-label">Judul Bagian <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                                           id="judul" name="judul" value="{{ old('judul', $bagianHalaman->judul) }}" 
                                           placeholder="Masukkan judul bagian" required>
                                    @error('judul')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="urutan" class="form-control-label">Urutan <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('urutan') is-invalid @enderror" 
                                           id="urutan" name="urutan" value="{{ old('urutan', $bagianHalaman->urutan) }}" 
                                           min="0" required>
                                    <small class="text-muted">Angka lebih kecil = tampil lebih atas</small>
                                    @error('urutan')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="isi" class="form-control-label">Isi / Konten</label>
                                    <textarea class="form-control @error('isi') is-invalid @enderror" 
                                              id="isi" name="isi" rows="8" 
                                              placeholder="Masukkan konten bagian ini.">{{ old('isi', $bagianHalaman->isi) }}</textarea>
                                    <small class="text-muted">
                                        Untuk tipe <strong>Hero</strong>: Masukkan deskripsi singkat.<br>
                                        Untuk tipe <strong>Teks</strong>: Masukkan paragraf konten (HTML diperbolehkan).<br>
                                        Untuk tipe <strong>Daftar/FAQ</strong>: Isi ini opsional, item dikelola terpisah.
                                    </small>
                                    @error('isi')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="aktif" name="aktif" value="1" 
                                           {{ old('aktif', $bagianHalaman->aktif) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="aktif">Aktif</label>
                                </div>
                                <small class="text-muted">Bagian yang tidak aktif tidak akan tampil di halaman publik</small>
                            </div>
                        </div>

                        <hr class="horizontal dark my-4">

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('adminui.bagian-halaman.index', ['halaman_id' => $bagianHalaman->halaman_id]) }}" 
                               class="btn btn-outline-secondary me-2">Batal</a>
                            <button type="submit" class="btn bg-gradient-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Info Item Bagian (jika tipe daftar/faq/hero) -->
    @if(in_array($bagianHalaman->tipe, ['daftar', 'faq', 'hero']))
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>
                        @if($bagianHalaman->tipe == 'hero')
                            Slide Hero ({{ $bagianHalaman->itemBagianHalaman()->count() }})
                        @else
                            Item Bagian ({{ $bagianHalaman->itemBagianHalaman()->count() }})
                        @endif
                    </h6>
                    <div>
                        <small class="text-muted me-3">Kelola item untuk tipe {{ $bagianHalaman->tipe }}</small>
                        <button type="button" class="btn btn-sm bg-gradient-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                            <i class="fas fa-plus me-1"></i> Tambah {{ $bagianHalaman->tipe == 'hero' ? 'Slide' : 'Item' }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($bagianHalaman->itemBagianHalaman()->count() > 0)
                        @if($bagianHalaman->tipe == 'hero')
                            {{-- Layout khusus untuk Hero Slides --}}
                            <div class="row">
                                @foreach($bagianHalaman->itemBagianHalaman()->orderBy('urutan')->get() as $item)
                                <div class="col-md-6 mb-3">
                                    <div class="card shadow-sm {{ $item->aktif ? '' : 'opacity-50' }}">
                                        @if($item->gambar)
                                            <img src="{{ asset('storage/' . $item->gambar) }}" class="card-img-top" alt="{{ $item->judul }}" style="height: 150px; object-fit: cover;">
                                        @else
                                            <div class="bg-gradient-secondary text-white text-center py-5">
                                                <i class="fas fa-image fa-3x opacity-5"></i>
                                                <p class="mb-0 mt-2">Belum ada gambar</p>
                                            </div>
                                        @endif
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">{{ $item->judul }}</h6>
                                                    @if($item->subjudul)
                                                        <small class="text-primary">{{ $item->subjudul }}</small>
                                                    @endif
                                                </div>
                                                <span class="badge bg-{{ $item->aktif ? 'success' : 'secondary' }}">
                                                    {{ $item->aktif ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </div>
                                            @if($item->deskripsi)
                                                <p class="text-sm text-muted mb-2">{{ Str::limit($item->deskripsi, 80) }}</p>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <span class="badge bg-secondary">Urutan: {{ $item->urutan }}</span>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-link text-info p-1" 
                                                            onclick="editItem({{ $item->id }})" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('adminui.bagian-halaman.delete-item', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus slide ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            {{-- Layout untuk daftar/faq --}}
                            <ul class="list-group">
                                @foreach($bagianHalaman->itemBagianHalaman()->orderBy('urutan')->get() as $item)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            @if($item->ikon)
                                                <i class="{{ $item->ikon }} me-2"></i>
                                            @endif
                                            <strong>{{ $item->judul }}</strong>
                                            @if($item->deskripsi)
                                                <br><small class="text-muted">{{ Str::limit($item->deskripsi, 100) }}</small>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="badge bg-secondary me-2">Urutan: {{ $item->urutan }}</span>
                                            <button type="button" class="btn btn-sm btn-link text-info p-1" onclick="editItem({{ $item->id }})" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('adminui.bagian-halaman.delete-item', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus item ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-{{ $bagianHalaman->tipe == 'hero' ? 'images' : 'list' }} fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Belum ada {{ $bagianHalaman->tipe == 'hero' ? 'slide' : 'item' }} untuk bagian ini.</p>
                            <button type="button" class="btn btn-sm bg-gradient-primary mt-3" data-bs-toggle="modal" data-bs-target="#addItemModal">
                                <i class="fas fa-plus me-1"></i> Tambah {{ $bagianHalaman->tipe == 'hero' ? 'Slide Pertama' : 'Item Pertama' }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Modal Tambah Item --}}
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('adminui.bagian-halaman.store-item', $bagianHalaman->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">
                        Tambah {{ $bagianHalaman->tipe == 'hero' ? 'Slide Hero' : 'Item Bagian' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="judul" class="form-label">Judul <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="judul" name="judul" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subjudul" class="form-label">Subjudul</label>
                                <input type="text" class="form-control" id="subjudul" name="subjudul" placeholder="Teks kecil di atas judul">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>

                    @if($bagianHalaman->tipe == 'hero')
                    <div class="mb-3">
                        <label for="gambar" class="form-label">Gambar Background <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" required>
                        <small class="text-muted">Ukuran rekomendasi: 1920x800 pixel. Format: JPG, PNG</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tombol_text" class="form-label">Teks Tombol 1</label>
                                <input type="text" class="form-control" id="tombol_text" name="tombol_text" placeholder="Contoh: Pelajari Lebih Lanjut">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tombol_link" class="form-label">Link Tombol 1</label>
                                <input type="text" class="form-control" id="tombol_link" name="tombol_link" placeholder="Contoh: /tentang">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tombol_text_2" class="form-label">Teks Tombol 2</label>
                                <input type="text" class="form-control" id="tombol_text_2" name="tombol_text_2" placeholder="Contoh: Daftar Sekarang">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tombol_link_2" class="form-label">Link Tombol 2</label>
                                <input type="text" class="form-control" id="tombol_link_2" name="tombol_link_2" placeholder="Contoh: /daftar">
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="mb-3">
                        <label for="ikon" class="form-label">Ikon (Font Awesome)</label>
                        <input type="text" class="form-control" id="ikon" name="ikon" placeholder="Contoh: fas fa-check">
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="urutan" class="form-label">Urutan</label>
                                <input type="number" class="form-control" id="urutan" name="urutan" value="{{ $bagianHalaman->itemBagianHalaman()->count() + 1 }}" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="aktif" name="aktif" value="1" checked>
                                    <label class="form-check-label" for="aktif">Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn bg-gradient-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Item --}}
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editItemForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editItemModalLabel">Edit {{ $bagianHalaman->tipe == 'hero' ? 'Slide Hero' : 'Item Bagian' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editItemModalBody">
                    {{-- Content loaded via JS --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn bg-gradient-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function editItem(itemId) {
    fetch(`{{ url('adminui/bagian-halaman/item') }}/${itemId}/edit`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editItemModalBody').innerHTML = html;
            document.getElementById('editItemForm').action = `{{ url('adminui/bagian-halaman/item') }}/${itemId}`;
            new bootstrap.Modal(document.getElementById('editItemModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data item');
        });
}
</script>
@endpush
