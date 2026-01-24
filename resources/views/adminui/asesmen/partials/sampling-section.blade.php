{{--
================================================================================
Sampling Audit Section Component
================================================================================
File: resources/views/adminui/asesmen/partials/sampling-section.blade.php
Purpose: Display and manage sampling audit status for asesmen
Variables: $asesmen
================================================================================
--}}

@php
    $canMark = auth()->user()->can('sampling.mark');
    $canView = auth()->user()->can('sampling.view');
    $isSampled = $asesmen->isSampled();
    $canModify = $asesmen->canModifySampling();
@endphp

@if($canView)
<div class="card mb-4" id="sampling-section">
    <div class="card-header pb-0">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0">
                    <i class="fas fa-clipboard-check me-2 text-info"></i>
                    Sampling Audit (Quality Control)
                </h6>
                <p class="text-sm text-secondary mb-0">
                    Pengendalian mutu oleh Komite Teknis
                </p>
            </div>
            <div>
                @if($isSampled)
                    <span class="badge bg-gradient-info badge-lg px-3 py-2">
                        <i class="fas fa-check-circle me-1"></i> SAMPLING AUDIT
                    </span>
                @else
                    <span class="badge bg-secondary badge-lg px-3 py-2">
                        <i class="fas fa-minus-circle me-1"></i> Tidak Disampling
                    </span>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        {{-- SOP Narrative --}}
        <div class="alert alert-light border mb-3" role="alert">
            <i class="fas fa-info-circle text-info me-2"></i>
            <span class="text-sm">
                Sebagai bagian dari pengendalian mutu, LSP melakukan sampling asesmen secara berkala
                untuk memastikan konsistensi dan objektivitas penilaian.
                <small class="text-muted d-block mt-1">(ISO 17024:2012 Clause 4.3 - Impartiality & Clause 9.4 - Internal Audits)</small>
            </span>
        </div>

        @if(!$asesmen->isSelesai())
            {{-- Asesmen belum selesai --}}
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span class="text-sm">Sampling hanya dapat dilakukan setelah asesmen selesai.</span>
            </div>
        @elseif(!$canModify)
            {{-- Keputusan sudah dikunci --}}
            <div class="alert alert-secondary" role="alert">
                <i class="fas fa-lock me-2"></i>
                <span class="text-sm">Keputusan sertifikasi sudah dikunci. Status sampling tidak dapat diubah.</span>
            </div>
        @endif

        @if($isSampled)
            {{-- Sampling Info --}}
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-secondary" width="150">Status</td>
                            <td><span class="badge bg-gradient-info">Sampling Audit</span></td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Tanggal Sampling</td>
                            <td><strong>{{ $asesmen->sampled_at?->format('d F Y H:i') ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Ditandai Oleh</td>
                            <td><strong>{{ $asesmen->sampledByUser?->name ?? '-' }}</strong></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-sm font-weight-bold">Catatan Sampling</label>
                    @if($canMark && $canModify)
                        <textarea class="form-control" id="sampling_note" rows="3" 
                                  placeholder="Catatan quality control...">{{ $asesmen->sampling_note }}</textarea>
                        <button type="button" class="btn btn-sm bg-gradient-info mt-2" onclick="updateSamplingNote()">
                            <i class="fas fa-save me-1"></i> Simpan Catatan
                        </button>
                    @else
                        <p class="text-sm bg-light p-3 rounded mb-0">{{ $asesmen->sampling_note ?: '-' }}</p>
                    @endif
                </div>
            </div>

            @if($canMark && $canModify)
            <div class="mt-3 pt-3 border-top">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="unmarkSampling()">
                    <i class="fas fa-times me-1"></i> Batalkan Sampling
                </button>
            </div>
            @endif
        @else
            {{-- Not Sampled --}}
            @if($canMark && $canModify)
            <div class="row">
                <div class="col-md-8">
                    <label class="form-label text-sm">Catatan Sampling (Opsional)</label>
                    <textarea class="form-control" id="sampling_note_new" rows="3" 
                              placeholder="Alasan pemilihan untuk sampling, catatan quality control..."></textarea>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn bg-gradient-info w-100" onclick="markSampling()">
                        <i class="fas fa-clipboard-check me-1"></i> Tandai Sampling Audit
                    </button>
                </div>
            </div>
            @endif
        @endif
    </div>
</div>

@push('js')
<script>
(function() {
    'use strict';

    const asesmenId = {{ $asesmen->id }};

    // Mark as sampling
    window.markSampling = async function() {
        const note = document.getElementById('sampling_note_new')?.value || '';
        
        if (!confirm('Tandai asesmen ini untuk Sampling Audit?')) {
            return;
        }

        try {
            const response = await fetch(`/adminui/sampling/${asesmenId}/mark`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ sampling_note: note })
            });

            const result = await response.json();

            if (result.success) {
                showToast('success', result.message);
                location.reload();
            } else {
                showToast('error', result.message || 'Gagal menandai sampling.');
            }
        } catch (error) {
            showToast('error', 'Terjadi kesalahan. Silakan coba lagi.');
            console.error('Mark sampling error:', error);
        }
    };

    // Unmark sampling
    window.unmarkSampling = async function() {
        if (!confirm('Batalkan Sampling Audit untuk asesmen ini?')) {
            return;
        }

        try {
            const response = await fetch(`/adminui/sampling/${asesmenId}/unmark`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const result = await response.json();

            if (result.success) {
                showToast('success', result.message);
                location.reload();
            } else {
                showToast('error', result.message || 'Gagal membatalkan sampling.');
            }
        } catch (error) {
            showToast('error', 'Terjadi kesalahan. Silakan coba lagi.');
            console.error('Unmark sampling error:', error);
        }
    };

    // Update sampling note
    window.updateSamplingNote = async function() {
        const note = document.getElementById('sampling_note')?.value || '';

        if (!note.trim()) {
            showToast('error', 'Catatan sampling tidak boleh kosong.');
            return;
        }

        try {
            const response = await fetch(`/adminui/sampling/${asesmenId}/note`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ sampling_note: note })
            });

            const result = await response.json();

            if (result.success) {
                showToast('success', result.message);
            } else {
                showToast('error', result.message || 'Gagal menyimpan catatan.');
            }
        } catch (error) {
            showToast('error', 'Terjadi kesalahan. Silakan coba lagi.');
            console.error('Update note error:', error);
        }
    };

    // Toast helper
    function showToast(type, message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type === 'success' ? 'success' : 'error',
                title: type === 'success' ? 'Berhasil' : 'Error',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }
})();
</script>
@endpush
@endif
