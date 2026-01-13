@extends('adminui.layouts.auth')

@section('title', 'Upload Logo Admin')

@push('styles')
<style>
.preview-container {
    width: 200px;
    height: 200px;
    border: 2px dashed #d2d6da;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    overflow: hidden;
}
.preview-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: #4CAF50;
}
input:checked + .slider:before {
    transform: translateX(26px);
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Edit Logo Admin</h6>
                    <a href="{{ route('adminui.logo-admin.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('adminui.logo-admin.update', $logoAdmin) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Current Logo -->
                                @if($logoAdmin->gambar)
                                <div class="form-group mb-3">
                                    <label class="form-label">Logo Saat Ini</label>
                                    <div>
                                        <img src="{{ asset('storage/' . $logoAdmin->gambar) }}" 
                                             alt="Current Logo" 
                                             style="max-height: 100px; object-fit: contain; background: #f8f9fa; padding: 10px; border-radius: 8px;">
                                    </div>
                                </div>
                                @endif

                                <!-- Upload Gambar -->
                                <div class="form-group mb-3">
                                    <label for="gambar" class="form-label">Upload Logo Baru</label>
                                    <input type="file" 
                                           class="form-control @error('gambar') is-invalid @enderror" 
                                           id="gambar" 
                                           name="gambar" 
                                           accept="image/png,image/jpg,image/jpeg,image/svg+xml">
                                    @error('gambar')
                                        <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Format: PNG, JPG, JPEG, SVG | Max: 2MB (Kosongkan jika tidak ingin ganti)</small>
                                </div>

                                <!-- Nama Perusahaan -->
                                <div class="form-group mb-3">
                                    <label for="nama_perusahaan" class="form-label">Nama Perusahaan (Opsional)</label>
                                    <input type="text" 
                                           class="form-control @error('nama_perusahaan') is-invalid @enderror" 
                                           id="nama_perusahaan" 
                                           name="nama_perusahaan" 
                                           value="{{ old('nama_perusahaan', $logoAdmin->nama_perusahaan) }}"
                                           placeholder="Kosongkan jika logo sudah ada nama">
                                    @error('nama_perusahaan')
                                        <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Nama perusahaan yang akan ditampilkan di sidebar. Kosongkan jika logo sudah memuat nama.</small>
                                </div>

                                <!-- Tagline -->
                                <div class="form-group mb-3">
                                    <label for="tagline" class="form-label">Tagline</label>
                                    <input type="text" 
                                           class="form-control @error('tagline') is-invalid @enderror" 
                                           id="tagline" 
                                           name="tagline" 
                                           value="{{ old('tagline', $logoAdmin->tagline ?? 'Leading The Way In It Excellence') }}"
                                           placeholder="e.g., Leading The Way In It Excellence">
                                    @error('tagline')
                                        <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Tagline perusahaan yang akan ditampilkan di sidebar</small>
                                </div>

                                <!-- Status Toggle -->
                                <div class="form-group mb-3">
                                    <label for="status" class="form-label d-block">Status</label>
                                    <label class="switch">
                                        <input type="checkbox" id="status" name="status" value="1" {{ $logoAdmin->status ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                    <span class="ms-2 {{ $logoAdmin->status ? 'text-success' : 'text-danger' }}" id="statusLabel">{{ $logoAdmin->status ? 'Aktif' : 'Non-Aktif' }}</span>
                                    <div>
                                        <small class="text-muted">Set sebagai logo aktif (hanya 1 logo yang bisa aktif)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Preview -->
                                <div class="form-group mb-3">
                                    <label class="form-label">Preview Logo Baru</label>
                                    <div class="preview-container" id="previewContainer">
                                        @if($logoAdmin->gambar)
                                            <img src="{{ asset('storage/' . $logoAdmin->gambar) }}" alt="Preview">
                                        @else
                                            <span class="text-muted">Preview akan muncul di sini</span>
                                        @endif
                                    </div>
                                    <small class="text-muted mt-2 d-block">Preview akan berubah saat Anda memilih file baru</small>
                                </div>
                            </div>
                        </div>

                        <!-- Info Alert -->
                        <div class="alert alert-info text-white mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Tips:</strong> Gunakan logo dengan background transparan (PNG/SVG) untuk hasil terbaik. Jika Anda set logo ini aktif, logo lain akan otomatis nonaktif.
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('adminui.logo-admin.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Logo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Preview image saat dipilih
document.getElementById('gambar').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const previewContainer = document.getElementById('previewContainer');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewContainer.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
        };
        reader.readAsDataURL(file);
    }
});

// Update status label
document.getElementById('status').addEventListener('change', function() {
    const label = document.getElementById('statusLabel');
    if (this.checked) {
        label.textContent = 'Aktif';
        label.classList.remove('text-danger');
        label.classList.add('text-success');
    } else {
        label.textContent = 'Non-Aktif';
        label.classList.remove('text-success');
        label.classList.add('text-danger');
    }
});
</script>
@endpush
