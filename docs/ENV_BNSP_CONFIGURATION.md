# Konfigurasi BNSP untuk Production (.env)

## 📋 WAJIB DIISI - Tambahkan ke `.env` Production

```bash
# ================================================================
# KONFIGURASI LISENSI BNSP
# ================================================================

# Apakah LSP sudah memiliki lisensi resmi BNSP?
# true  = Logo BNSP akan muncul di sertifikat
# false = Logo BNSP TIDAK muncul (tidak ada placeholder)
CERTIPRO_IS_BNSP_LICENSED=false

# Nomor Lisensi BNSP (contoh: BNSP.LSP-123-IDN)
# Jika belum ada, kosongkan atau set null
CERTIPRO_NOMOR_LISENSI=

# ================================================================
# INFORMASI LSP (Wajib Diisi)
# ================================================================

CERTIPRO_NAMA_LSP="Lembaga Sertifikasi Profesi CertiPro"
CERTIPRO_KETUA_LSP="Dr. Ahmad Hidayat, M.Kom"
CERTIPRO_KOTA_TERBIT="Jakarta"
CERTIPRO_ALAMAT="Jl. Sudirman No. 123, Jakarta Pusat"
CERTIPRO_TELEPON="021-12345678"
CERTIPRO_EMAIL="info@lsp-certipro.id"
CERTIPRO_WEBSITE="https://lsp-certipro.id"

# ================================================================
# SERTIFIKAT SETTINGS
# ================================================================

# Masa berlaku sertifikat (dalam tahun)
CERTIPRO_MASA_BERLAKU=3

# Format nomor sertifikat
# Placeholders: {PREFIX}, {YEAR}, {NUMBER}
CERTIPRO_FORMAT_NOMOR="LSP-CP/{YEAR}/{NUMBER}"
CERTIPRO_PREFIX="LSP-CP"

# ================================================================
# LOGO FILES (Upload logo ke public/images/)
# ================================================================

CERTIPRO_LOGO_LSP="images/logo-lsp.png"
CERTIPRO_LOGO_BNSP="images/logo-bnsp.png"
```

---

## 🔧 Cara Konfigurasi Production

### 1️⃣ **Jika SUDAH memiliki Lisensi BNSP:**

```bash
CERTIPRO_IS_BNSP_LICENSED=true
CERTIPRO_NOMOR_LISENSI="BNSP.LSP-123-IDN"
```

✅ Logo BNSP akan muncul di sertifikat  
✅ Nomor lisensi akan tercetak di header

### 2️⃣ **Jika BELUM memiliki Lisensi BNSP:**

```bash
CERTIPRO_IS_BNSP_LICENSED=false
CERTIPRO_NOMOR_LISENSI=
```

✅ Logo BNSP TIDAK akan muncul  
✅ Tidak ada placeholder/dummy text  
✅ Sertifikat tetap profesional  
✅ Siap audit BNSP

---

## 📁 Upload Logo

Upload logo ke production:

```bash
# Logo LSP (Wajib)
scp public/images/logo-lsp.png root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/public/images/

# Logo BNSP (Optional - Hanya jika sudah berlisensi)
scp public/images/logo-bnsp.png root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/public/images/
```

---

## ✅ Verifikasi

Setelah update `.env`:

```bash
cd /var/www/lsp-ui.ibnuapps.cloud/current
php artisan config:cache
php artisan view:clear
```

Test dengan menerbitkan sertifikat di `/adminui/sertifikat`

---

## 🎯 BNSP Compliance Checklist

- ✅ Tidak ada placeholder "[LOGO BNSP]"
- ✅ Tidak ada hardcode lisensi palsu
- ✅ Tidak ada watermark teks "LSP-UI"
- ✅ Watermark hanya logo dengan opacity 5%
- ✅ QR code aman untuk cetak A4
- ✅ Kalimat "TELAH DINYATAKAN KOMPETEN" tidak berubah
- ✅ Margin A4 portrait aman
- ✅ Conditional rendering logo BNSP
- ✅ Dynamic nomor lisensi dari config

---

## 📝 Catatan Penting

1. **Jangan** tampilkan logo BNSP jika belum memiliki lisensi resmi
2. **Jangan** hardcode nomor lisensi palsu
3. **Pastikan** logo LSP selalu ada (wajib)
4. **Test** cetak A4 sebelum distribusi massal
5. **Backup** file PDF yang sudah terbit

---

**Template Version:** BNSP-Compliant v2.0  
**Last Updated:** {{ date('Y-m-d') }}  
**Status:** Production Ready ✅
