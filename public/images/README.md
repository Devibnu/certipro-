# Logo Sertifikat CertiPro

Folder ini berisi logo untuk template sertifikat PDF.

## File yang Dibutuhkan:

### 1. `logo-lsp.png`
- Logo Lembaga Sertifikasi Profesi CertiPro
- Ukuran rekomendasi: 300x120 pixel (landscape)
- Format: PNG dengan background transparan
- Resolusi: minimal 150 DPI untuk kualitas cetak

### 2. `logo-bnsp.png`
- Logo BNSP (Badan Nasional Sertifikasi Profesi)
- Ukuran rekomendasi: 120x120 pixel (square)
- Format: PNG dengan background transparan
- Resolusi: minimal 150 DPI untuk kualitas cetak

## Catatan:
- Jika file logo tidak ditemukan, template akan menampilkan placeholder
- Pastikan logo memiliki kualitas tinggi untuk hasil cetak yang baik
- Logo BNSP hanya boleh digunakan jika LSP sudah mendapat lisensi resmi dari BNSP

## Contoh Penggunaan di Template:
```php
@if(file_exists(public_path('images/logo-lsp.png')))
    <img src="{{ public_path('images/logo-lsp.png') }}" alt="Logo LSP">
@endif
```
