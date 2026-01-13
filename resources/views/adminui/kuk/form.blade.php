@extends('adminui.layouts.auth')

@section('title', (isset($kuk) ? 'Edit' : 'Tambah') . ' KUK' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('adminui.kuk.index', request()->only(['skema_sertifikasi_id', 'unit_kompetensi_id'])) }}" class="btn btn-sm btn-outline-secondary me-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div>
                            <h6 class="mb-0">{{ isset($kuk) ? 'Edit KUK' : 'Tambah KUK' }}</h6>
                            <p class="text-sm mb-0">{{ isset($kuk) ? 'Ubah data kriteria unjuk kerja' : 'Isi data kriteria unjuk kerja baru' }}</p>
                        </div>
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

                    <form action="{{ isset($kuk) ? route('adminui.kuk.update', $kuk) : route('adminui.kuk.store') }}" method="POST">
                        @csrf
                        @if(isset($kuk))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="skema_sertifikasi_id" class="form-control-label">Skema Sertifikasi <span class="text-danger">*</span></label>
                                    <select class="form-select" id="skema_sertifikasi_id" required>
                                        <option value="">Pilih Skema Sertifikasi</option>
                                        @foreach($skemaList as $skema)
                                            <option value="{{ $skema->id }}" 
                                                {{ old('skema_sertifikasi_id', $selectedSkemaId ?? '') == $skema->id ? 'selected' : '' }}>
                                                {{ $skema->kode_skema }} - {{ $skema->nama_skema }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Pilih skema untuk memfilter unit kompetensi</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_kompetensi_id" class="form-control-label">Unit Kompetensi <span class="text-danger">*</span></label>
                                    <select class="form-select @error('unit_kompetensi_id') is-invalid @enderror" id="unit_kompetensi_id" name="unit_kompetensi_id" required>
                                        <option value="">Pilih Unit Kompetensi</option>
                                        @foreach($unitList as $unit)
                                            <option value="{{ $unit->id }}" 
                                                data-skema="{{ $unit->skema_sertifikasi_id }}"
                                                {{ old('unit_kompetensi_id', $kuk->unit_kompetensi_id ?? ($selectedUnitId ?? '')) == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->kode_unit }} - {{ $unit->nama_unit }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit_kompetensi_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kode_kuk" class="form-control-label">Kode KUK <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('kode_kuk') is-invalid @enderror" 
                                           id="kode_kuk" name="kode_kuk" 
                                           value="{{ old('kode_kuk', $kuk->kode_kuk ?? '') }}" 
                                           placeholder="Contoh: 1.1"
                                           required>
                                    @error('kode_kuk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="urutan" class="form-control-label">Urutan <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('urutan') is-invalid @enderror" 
                                           id="urutan" name="urutan" 
                                           value="{{ old('urutan', $kuk->urutan ?? ($nextUrutan ?? 1)) }}" 
                                           min="1" required>
                                    @error('urutan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label d-block">Status</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="aktif" name="aktif" value="1"
                                               {{ old('aktif', $kuk->aktif ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="aktif">Aktif</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="pernyataan_unjuk_kerja" class="form-control-label">Pernyataan Unjuk Kerja <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('pernyataan_unjuk_kerja') is-invalid @enderror" 
                                      id="pernyataan_unjuk_kerja" name="pernyataan_unjuk_kerja" rows="4" 
                                      placeholder="Tuliskan pernyataan unjuk kerja yang harus dibuktikan oleh asesi"
                                      required>{{ old('pernyataan_unjuk_kerja', $kuk->pernyataan_unjuk_kerja ?? '') }}</textarea>
                            @error('pernyataan_unjuk_kerja')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="horizontal dark mt-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('adminui.kuk.index', request()->only(['skema_sertifikasi_id', 'unit_kompetensi_id'])) }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn bg-gradient-primary">
                                <i class="fas fa-save me-1"></i> {{ isset($kuk) ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi</h6>
                </div>
                <div class="card-body">
                    <p class="text-sm mb-3">
                        <strong>KUK (Kriteria Unjuk Kerja)</strong><br>
                        KUK adalah pernyataan yang menjelaskan apa yang harus ditunjukkan oleh asesi untuk membuktikan kompetensinya.
                    </p>
                    <p class="text-sm mb-3">
                        <strong>Kode KUK</strong><br>
                        Kode unik untuk mengidentifikasi KUK dalam unit kompetensi. Format: 1.1, 1.2, 2.1, dst.
                    </p>
                    <p class="text-sm mb-3">
                        <strong>Urutan</strong><br>
                        Nomor urut tampilan KUK dalam daftar unit kompetensi.
                    </p>
                    <p class="text-sm mb-0">
                        <strong>Status Aktif</strong><br>
                        Hanya KUK aktif yang akan digunakan pada modul asesmen.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const skemaSelect = document.getElementById('skema_sertifikasi_id');
    const unitSelect = document.getElementById('unit_kompetensi_id');
    const allUnitOptions = Array.from(unitSelect.querySelectorAll('option[data-skema]'));
    
    function filterUnits() {
        const selectedSkema = skemaSelect.value;
        
        // Show/hide options based on selected skema
        allUnitOptions.forEach(option => {
            if (!selectedSkema || option.dataset.skema === selectedSkema) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
                if (option.selected) {
                    option.selected = false;
                    unitSelect.querySelector('option[value=""]').selected = true;
                }
            }
        });
    }
    
    skemaSelect.addEventListener('change', filterUnits);
    
    // Initial filter on page load
    filterUnits();
});
</script>
@endpush
