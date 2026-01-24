<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="edit_judul" class="form-label">Judul <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_judul" name="judul" value="{{ $item->judul }}" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="edit_subjudul" class="form-label">Subjudul</label>
            <input type="text" class="form-control" id="edit_subjudul" name="subjudul" value="{{ $item->subjudul }}" placeholder="Teks kecil di atas judul">
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="edit_deskripsi" class="form-label">Deskripsi</label>
    <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3">{{ $item->deskripsi }}</textarea>
</div>

@if($bagianHalaman->tipe == 'hero')
<div class="mb-3">
    <label for="edit_gambar" class="form-label">Gambar Background</label>
    @if($item->gambar)
        <div class="mb-2">
            <img src="{{ asset('storage/' . $item->gambar) }}" alt="Current image" class="img-thumbnail" style="max-height: 100px;">
            <small class="d-block text-muted">Gambar saat ini</small>
        </div>
    @endif
    <input type="file" class="form-control" id="edit_gambar" name="gambar" accept="image/*">
    <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar. Ukuran rekomendasi: 1920x800 pixel</small>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="edit_tombol_text" class="form-label">Teks Tombol 1</label>
            <input type="text" class="form-control" id="edit_tombol_text" name="tombol_text" value="{{ $item->tombol_text }}" placeholder="Contoh: Pelajari Lebih Lanjut">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="edit_tombol_link" class="form-label">Link Tombol 1</label>
            <input type="text" class="form-control" id="edit_tombol_link" name="tombol_link" value="{{ $item->tombol_link }}" placeholder="Contoh: /tentang">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="edit_tombol_text_2" class="form-label">Teks Tombol 2</label>
            <input type="text" class="form-control" id="edit_tombol_text_2" name="tombol_text_2" value="{{ $item->tombol_text_2 }}" placeholder="Contoh: Daftar Sekarang">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="edit_tombol_link_2" class="form-label">Link Tombol 2</label>
            <input type="text" class="form-control" id="edit_tombol_link_2" name="tombol_link_2" value="{{ $item->tombol_link_2 }}" placeholder="Contoh: /daftar">
        </div>
    </div>
</div>
@else
<div class="mb-3">
    <label for="edit_ikon" class="form-label">Ikon (Font Awesome)</label>
    <input type="text" class="form-control" id="edit_ikon" name="ikon" value="{{ $item->ikon }}" placeholder="Contoh: fas fa-check">
</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="edit_urutan" class="form-label">Urutan</label>
            <input type="number" class="form-control" id="edit_urutan" name="urutan" value="{{ $item->urutan }}" min="1">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Status</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="edit_aktif" name="aktif" value="1" {{ $item->aktif ? 'checked' : '' }}>
                <label class="form-check-label" for="edit_aktif">Aktif</label>
            </div>
        </div>
    </div>
</div>
