{{--
================================================================================
CertiPro LSP - Email Preview Modal Component
================================================================================
File: resources/views/adminui/components/email-preview-modal.blade.php
Usage: @include('adminui.components.email-preview-modal')
================================================================================
--}}

<!-- Email Preview Modal -->
<div class="modal fade" id="emailPreviewModal" tabindex="-1" aria-labelledby="emailPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title mb-0" id="emailPreviewModalLabel">
                        <i class="fas fa-envelope me-2"></i>Preview Email Resmi LSP
                    </h5>
                    <small class="opacity-75" id="emailPreviewSubject">-</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-0">
                <!-- Loading State -->
                <div id="emailPreviewLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat preview email...</p>
                </div>

                <!-- Error State -->
                <div id="emailPreviewError" class="text-center py-5 d-none">
                    <i class="fas fa-exclamation-circle text-danger fa-3x mb-3"></i>
                    <p class="text-danger mb-0" id="emailPreviewErrorMessage">Gagal memuat preview.</p>
                </div>

                <!-- Email Info Bar -->
                <div id="emailPreviewInfo" class="bg-light border-bottom px-4 py-3 d-none">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Penerima</small>
                            <strong id="emailPreviewTo" class="text-dark">-</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Referensi</small>
                            <strong id="emailPreviewRef" class="text-primary font-monospace">-</strong>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <small class="text-muted d-block">Terakhir Dikirim</small>
                            <span id="emailPreviewLastSent" class="badge bg-secondary">Belum pernah dikirim</span>
                        </div>
                    </div>
                </div>

                <!-- Email Content Frame -->
                <div id="emailPreviewContent" class="d-none">
                    <iframe id="emailPreviewFrame" 
                            style="width: 100%; height: 500px; border: none;" 
                            sandbox="allow-same-origin">
                    </iframe>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer justify-content-between">
                <div>
                    <span class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Resend: <span id="emailResendCount">0</span>/<span id="emailResendMax">3</span>
                    </span>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-primary" id="btnResendEmail" onclick="confirmResendEmail()">
                        <i class="fas fa-paper-plane me-1"></i>Kirim Ulang Email
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resend Confirmation Modal -->
<div class="modal fade" id="resendConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Kirim Ulang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Apakah Anda yakin ingin mengirim ulang email ini?</p>
                
                <div class="alert alert-info mb-3">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        Email akan dikirim ke: <strong id="resendEmailTo">-</strong>
                    </small>
                </div>

                <div class="mb-3">
                    <label for="resendReason" class="form-label">Alasan Pengiriman Ulang (opsional)</label>
                    <textarea id="resendReason" class="form-control" rows="2" 
                              placeholder="Contoh: Peserta mengklaim belum menerima email"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnConfirmResend" onclick="executeResendEmail()">
                    <i class="fas fa-paper-plane me-1"></i>Ya, Kirim Ulang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Email History Modal -->
<div class="modal fade" id="emailHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-history me-2"></i>Riwayat Email
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="emailHistoryLoading" class="text-center py-4">
                    <div class="spinner-border text-info" role="status"></div>
                </div>
                <div id="emailHistoryContent" class="d-none">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Event</th>
                                <th>Template</th>
                                <th>Penerima</th>
                                <th>Waktu</th>
                                <th>Oleh</th>
                            </tr>
                        </thead>
                        <tbody id="emailHistoryTable"></tbody>
                    </table>
                    <div id="emailHistoryEmpty" class="text-center text-muted py-4 d-none">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Belum ada riwayat email.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    #emailPreviewModal .modal-dialog {
        max-width: 900px;
    }
    #emailPreviewFrame {
        background: #f5f7fa;
    }
    .email-event-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>

<script>
    // Current email context
    let currentEmailType = null;
    let currentEmailId = null;
    let currentEmailTo = null;

    /**
     * Open email preview modal
     * @param {string} type - Email type (pra-diterima, pra-ditolak, etc.)
     * @param {int} id - Model ID
     */
    function openEmailPreview(type, id) {
        currentEmailType = type;
        currentEmailId = id;

        // Reset modal state
        document.getElementById('emailPreviewLoading').classList.remove('d-none');
        document.getElementById('emailPreviewError').classList.add('d-none');
        document.getElementById('emailPreviewInfo').classList.add('d-none');
        document.getElementById('emailPreviewContent').classList.add('d-none');
        document.getElementById('btnResendEmail').disabled = true;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('emailPreviewModal'));
        modal.show();

        // Fetch preview
        fetch(`/adminui/email/preview/${type}/${id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('emailPreviewLoading').classList.add('d-none');

            if (data.success) {
                // Update info
                document.getElementById('emailPreviewSubject').textContent = data.data.subject;
                document.getElementById('emailPreviewTo').textContent = data.data.email_to;
                document.getElementById('emailPreviewRef').textContent = data.data.reference;
                document.getElementById('emailResendCount').textContent = data.data.resend_count;
                document.getElementById('emailResendMax').textContent = data.data.max_resend;

                // Last sent
                if (data.data.last_sent) {
                    document.getElementById('emailPreviewLastSent').textContent = data.data.last_sent;
                    document.getElementById('emailPreviewLastSent').className = 'badge bg-success';
                } else {
                    document.getElementById('emailPreviewLastSent').textContent = 'Belum pernah dikirim';
                    document.getElementById('emailPreviewLastSent').className = 'badge bg-secondary';
                }

                // Render email in iframe
                const iframe = document.getElementById('emailPreviewFrame');
                iframe.srcdoc = data.data.html;

                // Show content
                document.getElementById('emailPreviewInfo').classList.remove('d-none');
                document.getElementById('emailPreviewContent').classList.remove('d-none');

                // Enable resend button if allowed
                if (data.data.can_resend) {
                    document.getElementById('btnResendEmail').disabled = false;
                    currentEmailTo = data.data.email_to;
                }
            } else {
                document.getElementById('emailPreviewError').classList.remove('d-none');
                document.getElementById('emailPreviewErrorMessage').textContent = data.message || 'Gagal memuat preview.';
            }
        })
        .catch(error => {
            console.error('Preview error:', error);
            document.getElementById('emailPreviewLoading').classList.add('d-none');
            document.getElementById('emailPreviewError').classList.remove('d-none');
            document.getElementById('emailPreviewErrorMessage').textContent = 'Terjadi kesalahan saat memuat preview.';
        });
    }

    /**
     * Open resend confirmation modal
     */
    function confirmResendEmail() {
        document.getElementById('resendEmailTo').textContent = currentEmailTo || '-';
        document.getElementById('resendReason').value = '';
        
        const confirmModal = new bootstrap.Modal(document.getElementById('resendConfirmModal'));
        confirmModal.show();
    }

    /**
     * Execute email resend
     */
    function executeResendEmail() {
        const reason = document.getElementById('resendReason').value;
        const btnConfirm = document.getElementById('btnConfirmResend');
        
        // Disable button
        btnConfirm.disabled = true;
        btnConfirm.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengirim...';

        fetch(`/adminui/email/resend/${currentEmailType}/${currentEmailId}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            // Close confirm modal
            bootstrap.Modal.getInstance(document.getElementById('resendConfirmModal')).hide();
            
            // Reset button
            btnConfirm.disabled = false;
            btnConfirm.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Ya, Kirim Ulang';

            if (data.success) {
                // Show success alert
                showToast('success', data.message);
                
                // Close preview modal
                bootstrap.Modal.getInstance(document.getElementById('emailPreviewModal')).hide();
            } else {
                showToast('error', data.message || 'Gagal mengirim email.');
            }
        })
        .catch(error => {
            console.error('Resend error:', error);
            btnConfirm.disabled = false;
            btnConfirm.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Ya, Kirim Ulang';
            showToast('error', 'Terjadi kesalahan saat mengirim email.');
        });
    }

    /**
     * Open email history modal
     * @param {string} type - Email type
     * @param {int} id - Model ID
     */
    function openEmailHistory(type, id) {
        document.getElementById('emailHistoryLoading').classList.remove('d-none');
        document.getElementById('emailHistoryContent').classList.add('d-none');
        document.getElementById('emailHistoryEmpty').classList.add('d-none');

        const modal = new bootstrap.Modal(document.getElementById('emailHistoryModal'));
        modal.show();

        fetch(`/adminui/email/history/${type}/${id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('emailHistoryLoading').classList.add('d-none');
            document.getElementById('emailHistoryContent').classList.remove('d-none');

            if (data.success && data.data.length > 0) {
                const tbody = document.getElementById('emailHistoryTable');
                tbody.innerHTML = '';

                data.data.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${getEventBadge(item.event)}</td>
                        <td><code>${item.template}</code></td>
                        <td>${item.email_to}</td>
                        <td><small>${item.timestamp}</small></td>
                        <td>${item.user}</td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                document.getElementById('emailHistoryEmpty').classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('History error:', error);
            document.getElementById('emailHistoryLoading').classList.add('d-none');
        });
    }

    /**
     * Get event badge HTML
     */
    function getEventBadge(event) {
        const badges = {
            'email_sent': '<span class="badge bg-success email-event-badge">Terkirim</span>',
            'email_resent': '<span class="badge bg-info email-event-badge">Dikirim Ulang</span>',
            'email_failed': '<span class="badge bg-danger email-event-badge">Gagal</span>',
            'email_previewed': '<span class="badge bg-secondary email-event-badge">Preview</span>',
        };
        return badges[event] || `<span class="badge bg-secondary email-event-badge">${event}</span>`;
    }

    /**
     * Show toast notification
     */
    function showToast(type, message) {
        // Use existing toast system or create simple alert
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type === 'success' ? 'success' : 'error',
                title: type === 'success' ? 'Berhasil' : 'Error',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        } else if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            alert(message);
        }
    }
</script>
