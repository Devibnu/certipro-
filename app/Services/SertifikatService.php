<?php

namespace App\Services;

use App\Jobs\SendEmailSertifikatJob;
use App\Jobs\SendWhatsAppSertifikatJob;
use App\Models\PendaftaranSertifikasi;
use App\Models\Sertifikat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * SertifikatService
 * 
 * Handles certificate issuance with robust validation and error handling.
 * Extracts business logic from controller for better testability and maintainability.
 */
class SertifikatService
{
    /**
     * Validate pendaftaran is ready for certificate issuance.
     * 
     * @param PendaftaranSertifikasi $pendaftaran
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validatePendaftaran(PendaftaranSertifikasi $pendaftaran): array
    {
        $errors = [];
        
        // ISO 17024 APPROACH: Keputusan = Single Source of Truth
        // Jika Komite Teknis sudah menetapkan keputusan KOMPETEN,
        // berarti semua requirement sudah divalidasi!
        
        // Validate: Keputusan exists (CRITICAL - ISO 17024 requirement)
        if (!$pendaftaran->keputusan) {
            $errors[] = 'Keputusan sertifikasi belum ditetapkan. Silakan tetapkan keputusan terlebih dahulu. Alur ISO 17024: Asesmen → Keputusan → Sertifikat.';
            Log::error('Certificate validation failed: keputusan not found', [
                'pendaftaran_id' => $pendaftaran->id,
                'status' => $pendaftaran->status,
            ]);
        } elseif ($pendaftaran->keputusan->keputusan !== 'kompeten') {
            $errors[] = 'Keputusan sertifikasi harus KOMPETEN. Keputusan saat ini: ' . $pendaftaran->keputusan->keputusan_label;
            Log::error('Certificate validation failed: keputusan not kompeten', [
                'pendaftaran_id' => $pendaftaran->id,
                'keputusan' => $pendaftaran->keputusan->keputusan,
            ]);
        }
        
        // Validate: Skema exists
        if (!$pendaftaran->skemaSertifikasi) {
            $errors[] = 'Data skema sertifikasi tidak ditemukan';
            Log::error('Certificate validation failed: skema is null', [
                'pendaftaran_id' => $pendaftaran->id,
                'skema_sertifikasi_id' => $pendaftaran->skema_sertifikasi_id,
            ]);
        }
        
        // Validate: Status is KOMPETEN_FINAL
        if ($pendaftaran->status !== PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL) {
            $errors[] = 'Status pendaftaran harus KOMPETEN_FINAL';
            Log::error('Certificate validation failed: invalid status', [
                'pendaftaran_id' => $pendaftaran->id,
                'current_status' => $pendaftaran->status,
                'required_status' => PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL,
            ]);
        }
        
        // Validate: Certificate not already issued
        if ($pendaftaran->sertifikat) {
            $errors[] = 'Sertifikat sudah diterbitkan sebelumnya';
            Log::warning('Certificate validation failed: already issued', [
                'pendaftaran_id' => $pendaftaran->id,
                'sertifikat_id' => $pendaftaran->sertifikat->id,
            ]);
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
    
    /**
     * Issue certificate for given pendaftaran.
     * 
     * @param PendaftaranSertifikasi $pendaftaran
     * @return array ['success' => bool, 'sertifikat' => Sertifikat|null, 'error' => string|null]
     */
    public function terbitkan(PendaftaranSertifikasi $pendaftaran): array
    {
        // Validate first
        $validation = $this->validatePendaftaran($pendaftaran);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'sertifikat' => null,
                'error' => 'Data sertifikasi belum lengkap. ' . implode(', ', $validation['errors']),
            ];
        }
        
        // Start transaction
        DB::beginTransaction();
        
        try {
            // Generate nomor sertifikat
            $nomorSertifikat = $this->generateNomorSertifikat();
            
            // Calculate validity period (3 years)
            $tanggalTerbit = now();
            $tanggalBerlakuSampai = now()->addYears(3);
            
            // Create sertifikat record
            // Get asesi name from multiple sources (flexible for legacy data)
            $namaPeserta = $pendaftaran->asesi_name 
                ?? $pendaftaran->user?->name 
                ?? $pendaftaran->praPendaftaran?->nama_lengkap 
                ?? $pendaftaran->nama_lengkap 
                ?? 'Peserta Sertifikasi';
            
            $sertifikat = Sertifikat::create([
                'pendaftaran_id' => $pendaftaran->id,
                'nomor_sertifikat' => $nomorSertifikat,
                'nama_peserta' => $namaPeserta,
                'skema_sertifikasi' => $pendaftaran->skemaSertifikasi->nama_skema,
                'tanggal_terbit' => $tanggalTerbit,
                'tanggal_berlaku_sampai' => $tanggalBerlakuSampai,
                'diterbitkan_oleh' => Auth::id(),
            ]);
            
            // Generate QR Code
            $qrCodePath = $this->generateQRCode($sertifikat);
            
            // Generate PDF
            $pdfPath = $this->generatePDF($sertifikat, $pendaftaran, $qrCodePath);
            
            // Update sertifikat with file paths
            $sertifikat->update([
                'qr_code' => $qrCodePath,
                'file_pdf' => $pdfPath,
            ]);
            
            DB::commit();
            
            // Success logging
            Log::info('Certificate successfully issued', [
                'pendaftaran_id' => $pendaftaran->id,
                'sertifikat_id' => $sertifikat->id,
                'nomor_sertifikat' => $nomorSertifikat,
                'user_id' => Auth::id(),
            ]);
            
            // Dispatch notification jobs (asynchronous)
            // These will NOT block the response or cause failures
            $this->dispatchNotifications($sertifikat);
            
            return [
                'success' => true,
                'sertifikat' => $sertifikat,
                'error' => null,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Error logging
            Log::error('Certificate issuance failed', [
                'pendaftaran_id' => $pendaftaran->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);
            
            return [
                'success' => false,
                'sertifikat' => null,
                'error' => 'Terjadi kesalahan saat menerbitkan sertifikat: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Generate unique certificate number.
     * 
     * Format: CERT/LSP/YYYY/NNN
     */
    private function generateNomorSertifikat(): string
    {
        $year = now()->year;
        $prefix = "CERT/LSP/{$year}/";
        
        // Get last certificate number for this year
        $lastCert = Sertifikat::where('nomor_sertifikat', 'like', "{$prefix}%")
            ->orderBy('nomor_sertifikat', 'desc')
            ->first();
        
        if ($lastCert) {
            // Extract number from last certificate
            $lastNumber = (int) substr($lastCert->nomor_sertifikat, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate QR Code for certificate verification.
     */
    private function generateQRCode(Sertifikat $sertifikat): string
    {
        $verificationUrl = route('public.sertifikat.verify', $sertifikat->id);
        
        $qrCodeFileName = 'qr_' . $sertifikat->id . '_' . time() . '.svg';
        $qrCodePath = 'sertifikat/qrcodes/' . $qrCodeFileName;
        
        $qrCode = QrCode::format('svg')
            ->size(200)
            ->margin(1)
            ->generate($verificationUrl);
        
        Storage::disk('public')->put($qrCodePath, $qrCode);
        
        return $qrCodePath;
    }
    
    /**
     * Generate PDF certificate.
     */
    private function generatePDF(Sertifikat $sertifikat, PendaftaranSertifikasi $pendaftaran, string $qrCodePath): string
    {
        $pdfFileName = 'sertifikat_' . $sertifikat->id . '_' . time() . '.pdf';
        $pdfPath = 'sertifikat/pdf/' . $pdfFileName;
        
        // Get QR code content
        $qrCodeContent = Storage::disk('public')->get($qrCodePath);
        $qrCodeBase64 = base64_encode($qrCodeContent);
        
        // Get ketua LSP from config
        $ketuaLsp = config('certipro.ketua_lsp', 'Dr. Ahmad Hidayat, M.Kom');
        
        // Generate PDF
        $pdf = Pdf::loadView('pdf.sertifikat-bnsp', [
            'sertifikat' => $sertifikat,
            'pendaftaran' => $pendaftaran,
            'qrCodeBase64' => $qrCodeBase64,
            'ketuaLsp' => $ketuaLsp,
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        
        // Save PDF
        Storage::disk('public')->put($pdfPath, $pdf->output());
        
        return $pdfPath;
    }
    
    /**
     * Dispatch notification jobs after certificate issuance.
     * Jobs are queued and will NOT block the main request.
     */
    private function dispatchNotifications(Sertifikat $sertifikat): void
    {
        try {
            // Load relations needed for notifications
            $sertifikat->load([
                'pendaftaran.user',
                'pendaftaran.skemaSertifikasi',
                'pendaftaran.keputusan'
            ]);
            
            // Dispatch Email notification (high priority)
            SendEmailSertifikatJob::dispatch($sertifikat)
                ->onQueue('notifications')
                ->delay(now()->addSeconds(5)); // Small delay to ensure file is ready
            
            // Dispatch WhatsApp notification (lower priority)
            SendWhatsAppSertifikatJob::dispatch($sertifikat)
                ->onQueue('notifications')
                ->delay(now()->addSeconds(10));
            
            Log::info('Notification jobs dispatched', [
                'sertifikat_id' => $sertifikat->id,
                'email_job' => SendEmailSertifikatJob::class,
                'whatsapp_job' => SendWhatsAppSertifikatJob::class,
            ]);
            
        } catch (\Exception $e) {
            // Log error but DON'T throw - notifications are non-critical
            Log::error('Failed to dispatch notification jobs', [
                'sertifikat_id' => $sertifikat->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
