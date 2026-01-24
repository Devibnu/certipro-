{{--
================================================================================
Evidence Upload Modals & JavaScript
================================================================================
File: resources/views/adminui/asesmen/partials/evidence-modals.blade.php
Purpose: Modals for file upload and link addition
Variables: $asesmenId
================================================================================
--}}

{{-- Upload File Modal --}}
<div class="modal fade" id="uploadEvidenceModal" tabindex="-1" aria-labelledby="uploadEvidenceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadEvidenceModalLabel">
                    <i class="fas fa-upload text-primary me-2"></i>Upload Evidence
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadEvidenceForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="asesmen_id" id="upload_asesmen_id">
                <input type="hidden" name="kuk_id" id="upload_kuk_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">KUK</label>
                        <div class="form-control-plaintext">
                            <span class="badge bg-gradient-info" id="upload_kuk_kode"></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="upload_file" class="form-label">File Evidence <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="upload_file" name="file" required
                               accept=".pdf,.jpg,.jpeg,.png,.gif,.zip,.doc,.docx,.xls,.xlsx">
                        <div class="form-text">
                            Format: PDF, JPG, PNG, GIF, ZIP, DOC, DOCX, XLS, XLSX. Maksimal 10 MB.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="upload_description" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control" id="upload_description" name="description" rows="2" 
                                  placeholder="Deskripsi bukti/evidence..."></textarea>
                    </div>
                    <div id="upload_error" class="alert alert-danger d-none"></div>
                    <div id="upload_progress" class="d-none">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                        <p class="text-center text-sm mt-2 mb-0">Mengupload...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn bg-gradient-primary" id="upload_submit_btn">
                        <i class="fas fa-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Link Modal --}}
<div class="modal fade" id="addLinkModal" tabindex="-1" aria-labelledby="addLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLinkModalLabel">
                    <i class="fas fa-link text-info me-2"></i>Tambah Link Evidence
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addLinkForm">
                @csrf
                <input type="hidden" name="asesmen_id" id="link_asesmen_id">
                <input type="hidden" name="kuk_id" id="link_kuk_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">KUK</label>
                        <div class="form-control-plaintext">
                            <span class="badge bg-gradient-info" id="link_kuk_kode"></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="link_url" class="form-label">URL Link <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="link_url" name="link_url" required
                               placeholder="https://example.com/evidence">
                        <div class="form-text">
                            Masukkan URL lengkap (contoh: https://drive.google.com/...)
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="link_description" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control" id="link_description" name="description" rows="2" 
                                  placeholder="Deskripsi link evidence..."></textarea>
                    </div>
                    <div id="link_error" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn bg-gradient-info" id="link_submit_btn">
                        <i class="fas fa-plus me-1"></i> Tambah Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteEvidenceModal" tabindex="-1" aria-labelledby="deleteEvidenceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEvidenceModalLabel">
                    <i class="fas fa-trash text-danger me-2"></i>Hapus Evidence
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus evidence:</p>
                <p class="font-weight-bold" id="delete_evidence_name"></p>
                <input type="hidden" id="delete_evidence_id">
                <div id="delete_error" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirm_delete_btn" onclick="confirmDeleteEvidence()">
                    <i class="fas fa-trash me-1"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
// Evidence Upload/Link/Delete JavaScript
(function() {
    'use strict';

    // Modal instances
    let uploadModal, linkModal, deleteModal;

    document.addEventListener('DOMContentLoaded', function() {
        uploadModal = new bootstrap.Modal(document.getElementById('uploadEvidenceModal'));
        linkModal = new bootstrap.Modal(document.getElementById('addLinkModal'));
        deleteModal = new bootstrap.Modal(document.getElementById('deleteEvidenceModal'));

        // Form handlers
        document.getElementById('uploadEvidenceForm').addEventListener('submit', handleUpload);
        document.getElementById('addLinkForm').addEventListener('submit', handleAddLink);
    });

    // Show upload modal
    window.showUploadModal = function(asesmenId, kukId, kukKode) {
        document.getElementById('upload_asesmen_id').value = asesmenId;
        document.getElementById('upload_kuk_id').value = kukId;
        document.getElementById('upload_kuk_kode').textContent = kukKode;
        document.getElementById('upload_file').value = '';
        document.getElementById('upload_description').value = '';
        document.getElementById('upload_error').classList.add('d-none');
        document.getElementById('upload_progress').classList.add('d-none');
        uploadModal.show();
    };

    // Show link modal
    window.showLinkModal = function(asesmenId, kukId, kukKode) {
        document.getElementById('link_asesmen_id').value = asesmenId;
        document.getElementById('link_kuk_id').value = kukId;
        document.getElementById('link_kuk_kode').textContent = kukKode;
        document.getElementById('link_url').value = '';
        document.getElementById('link_description').value = '';
        document.getElementById('link_error').classList.add('d-none');
        linkModal.show();
    };

    // Show delete modal
    window.deleteEvidence = function(evidenceId, evidenceName) {
        document.getElementById('delete_evidence_id').value = evidenceId;
        document.getElementById('delete_evidence_name').textContent = evidenceName;
        document.getElementById('delete_error').classList.add('d-none');
        deleteModal.show();
    };

    // Handle file upload
    async function handleUpload(e) {
        e.preventDefault();
        
        const asesmenId = document.getElementById('upload_asesmen_id').value;
        const kukId = document.getElementById('upload_kuk_id').value;
        const file = document.getElementById('upload_file').files[0];
        const description = document.getElementById('upload_description').value;
        const submitBtn = document.getElementById('upload_submit_btn');
        const errorDiv = document.getElementById('upload_error');
        const progressDiv = document.getElementById('upload_progress');

        if (!file) {
            showError(errorDiv, 'Pilih file untuk diupload.');
            return;
        }

        // Check file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            showError(errorDiv, 'Ukuran file maksimal 10 MB.');
            return;
        }

        submitBtn.disabled = true;
        errorDiv.classList.add('d-none');
        progressDiv.classList.remove('d-none');

        const formData = new FormData();
        formData.append('file', file);
        formData.append('description', description);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        try {
            const response = await fetch(`/adminui/asesmen/${asesmenId}/evidence/kuk/${kukId}/upload`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const result = await response.json();

            if (result.success) {
                uploadModal.hide();
                addEvidenceToList(kukId, result.data);
                showToast('success', result.message);
            } else {
                showError(errorDiv, result.message || 'Gagal mengupload file.');
            }
        } catch (error) {
            showError(errorDiv, 'Terjadi kesalahan. Silakan coba lagi.');
            console.error('Upload error:', error);
        } finally {
            submitBtn.disabled = false;
            progressDiv.classList.add('d-none');
        }
    }

    // Handle add link
    async function handleAddLink(e) {
        e.preventDefault();
        
        const asesmenId = document.getElementById('link_asesmen_id').value;
        const kukId = document.getElementById('link_kuk_id').value;
        const linkUrl = document.getElementById('link_url').value;
        const description = document.getElementById('link_description').value;
        const submitBtn = document.getElementById('link_submit_btn');
        const errorDiv = document.getElementById('link_error');

        if (!linkUrl) {
            showError(errorDiv, 'URL link wajib diisi.');
            return;
        }

        submitBtn.disabled = true;
        errorDiv.classList.add('d-none');

        try {
            const response = await fetch(`/adminui/asesmen/${asesmenId}/evidence/kuk/${kukId}/link`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ link_url: linkUrl, description: description })
            });

            const result = await response.json();

            if (result.success) {
                linkModal.hide();
                addEvidenceToList(kukId, result.data);
                showToast('success', result.message);
            } else {
                showError(errorDiv, result.message || 'Gagal menambahkan link.');
            }
        } catch (error) {
            showError(errorDiv, 'Terjadi kesalahan. Silakan coba lagi.');
            console.error('Add link error:', error);
        } finally {
            submitBtn.disabled = false;
        }
    }

    // Confirm delete
    window.confirmDeleteEvidence = async function() {
        const evidenceId = document.getElementById('delete_evidence_id').value;
        const confirmBtn = document.getElementById('confirm_delete_btn');
        const errorDiv = document.getElementById('delete_error');

        confirmBtn.disabled = true;
        errorDiv.classList.add('d-none');

        try {
            const response = await fetch(`/adminui/evidence/${evidenceId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const result = await response.json();

            if (result.success) {
                deleteModal.hide();
                document.getElementById(`evidence-item-${evidenceId}`)?.remove();
                showToast('success', result.message);
            } else {
                showError(errorDiv, result.message || 'Gagal menghapus evidence.');
            }
        } catch (error) {
            showError(errorDiv, 'Terjadi kesalahan. Silakan coba lagi.');
            console.error('Delete error:', error);
        } finally {
            confirmBtn.disabled = false;
        }
    };

    // Add evidence item to list
    function addEvidenceToList(kukId, evidence) {
        const listContainer = document.getElementById(`evidence-list-${kukId}`);
        const noEvidenceDiv = document.getElementById(`no-evidence-${kukId}`);
        
        if (noEvidenceDiv) {
            noEvidenceDiv.remove();
        }

        const canDelete = document.querySelector('[data-can-delete]')?.dataset.canDelete === 'true';
        const isLocked = document.querySelector('[data-is-locked]')?.dataset.isLocked === 'true';

        const itemHtml = `
            <div class="evidence-item d-flex align-items-center p-2 border rounded mb-2" id="evidence-item-${evidence.id}">
                <div class="evidence-icon me-2">
                    <i class="${evidence.file_icon} fa-lg"></i>
                </div>
                <div class="evidence-info flex-grow-1">
                    ${evidence.type === 'file' 
                        ? `<a href="/adminui/evidence/${evidence.id}/download" class="text-sm text-primary text-decoration-none">${evidence.display_name}</a>
                           <span class="text-xs text-secondary ms-2">(${evidence.file_size_human || ''})</span>`
                        : `<a href="${evidence.link_url}" target="_blank" rel="noopener noreferrer" class="text-sm text-primary text-decoration-none">
                               ${evidence.display_name.substring(0, 50)}${evidence.display_name.length > 50 ? '...' : ''}
                               <i class="fas fa-external-link-alt text-xs ms-1"></i>
                           </a>`
                    }
                    ${evidence.description ? `<p class="text-xs text-secondary mb-0 mt-1">${evidence.description}</p>` : ''}
                    <span class="text-xs text-muted">Oleh ${evidence.uploader_name} • ${evidence.created_at}</span>
                </div>
                ${!isLocked && canDelete ? `
                <div class="evidence-actions ms-2">
                    <button type="button" class="btn btn-link text-danger btn-sm p-0" 
                            onclick="deleteEvidence(${evidence.id}, '${evidence.display_name.replace(/'/g, "\\'")}')"
                            title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>` : ''}
            </div>
        `;

        listContainer.insertAdjacentHTML('beforeend', itemHtml);
    }

    // Show error message
    function showError(element, message) {
        element.textContent = message;
        element.classList.remove('d-none');
    }

    // Show toast notification
    function showToast(type, message) {
        // Use SweetAlert if available, otherwise alert
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
