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
                    <h6>Upload Logo Admin</h6>
                    <a href="{{ route('adminui.logo-admin.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Terjadi kesalahan:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('adminui.logo-admin.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Upload Gambar -->
                                <div class="form-group mb-3">
                                    <label for="gambar" class="form-label">Upload Logo <span class="text-danger">*</span></label>
                                    <input type="file" 
                                           class="form-control @error('gambar') is-invalid @enderror" 
                                           id="gambar" 
                                           name="gambar" 
                                           accept="image/png,image/jpg,image/jpeg,image/svg+xml"
                                           required>
                                    @error('gambar')
                                        <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Format: PNG, JPG, JPEG, SVG | Max: 2MB</small>
                                </div>

                                <!-- Nama Perusahaan -->
                                <div class="form-group mb-3">
                                    <label for="nama_perusahaan" class="form-label">Nama Perusahaan (Opsional)</label>
                                    <input type="text" 
                                           class="form-control @error('nama_perusahaan') is-invalid @enderror" 
                                           id="nama_perusahaan" 
                                           name="nama_perusahaan" 
                                           value="{{ old('nama_perusahaan') }}"
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
                                           value="{{ old('tagline', 'Leading The Way In It Excellence') }}"
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
                                        <input type="checkbox" id="status" name="status" value="1" checked>
                                        <span class="slider"></span>
                                    </label>
                                    <span class="ms-2 text-success" id="statusLabel">Aktif</span>
                                    <div>
                                        <small class="text-muted">Set sebagai logo aktif (hanya 1 logo yang bisa aktif)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Preview -->
                                <div class="form-group mb-3">
                                    <label class="form-label">Preview Logo</label>
                                    <div class="preview-container" id="previewContainer">
                                        <span class="text-muted">Preview akan muncul di sini</span>
                                    </div>
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
                                <i class="fas fa-upload me-1"></i> Upload Logo
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
    
            // Validasi tipe file
            const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/svg+xml'];
            if (!allowedTypes.includes(file.type)) {
                alert('Format file tidak didukung! Gunakan PNG, JPG, JPEG, atau SVG');
                this.value = '';
                previewContainer.innerHTML = '<span class="text-muted">Preview akan muncul di sini</span>';
                return;
            }
            
            // Tampilkan loading
            previewContainer.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin"></i> Loading...</span>';
            
            const reader = new FileReader();
            reader.onload = function(e) {
                console.log('Preview loaded successfully');
                previewContainer.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-width: 100%; max-height: 100%; object-fit: contain;">';
            };
            reader.onerror = function(e) {
                console.error('Error reading file:', e);
                alert('Gagal membaca file!');
                previewContainer.innerHTML = '<span class="text-danger">Gagal memuat preview</span>';
            };
            reader.readAsDataURL(file);
        } else {
            console.log('Tidak ada file dipilih');
            previewContainer.innerHTML = '<span class="text-muted">Preview akan muncul di sini</span>';
        }
    });
    
    // Update status label
    if (statusToggle) {
        statusToggle.addEventListener('change', function() {
            if (statusLabel) {
                if (this.checked) {
                    statusLabel.textContent = 'Aktif';
                    statusLabel.classList.remove('text-danger');
                    statusLabel.classList.add('text-success');
                } else {
                    statusLabel.textContent = 'Non-Aktif';
                    statusLabel.classList.remove('text-success');
                    statusLabel.classList.add('text-danger');
                }
            }
        });
    }
    
    console.log('Event listeners attached successfully');
});
</script>
@endpush
