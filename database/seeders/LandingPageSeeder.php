<?php

namespace Database\Seeders;

use App\Models\BagianHalaman;
use App\Models\Halaman;
use App\Models\ItemBagianHalaman;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LandingPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Halaman Home
        $home = Halaman::create([
            'slug' => 'home',
            'judul' => 'Beranda',
            'aktif' => true,
        ]);

        BagianHalaman::create([
            'halaman_id' => $home->id,
            'tipe' => 'hero',
            'judul' => 'Selamat Datang di CertiPro',
            'isi' => 'Lembaga Sertifikasi Profesi terpercaya untuk pengembangan kompetensi profesional Anda. Dapatkan sertifikasi yang diakui secara nasional.',
            'urutan' => 0,
            'aktif' => true,
        ]);

        BagianHalaman::create([
            'halaman_id' => $home->id,
            'tipe' => 'teks',
            'judul' => 'Mengapa Memilih CertiPro?',
            'isi' => 'CertiPro adalah Lembaga Sertifikasi Profesi yang berkomitmen untuk meningkatkan kualitas sumber daya manusia Indonesia melalui proses sertifikasi yang terstandar dan profesional.',
            'urutan' => 1,
            'aktif' => true,
        ]);

        // 2. Halaman Tentang
        $tentang = Halaman::create([
            'slug' => 'tentang',
            'judul' => 'Tentang LSP',
            'aktif' => true,
        ]);

        BagianHalaman::create([
            'halaman_id' => $tentang->id,
            'tipe' => 'hero',
            'judul' => 'Tentang Lembaga Sertifikasi Profesi CertiPro',
            'isi' => 'Mengenal lebih dekat tentang visi, misi, dan komitmen kami dalam pengembangan kompetensi profesional.',
            'urutan' => 0,
            'aktif' => true,
        ]);

        $tentangTeks = BagianHalaman::create([
            'halaman_id' => $tentang->id,
            'tipe' => 'teks',
            'judul' => 'Sejarah dan Latar Belakang',
            'isi' => 'CertiPro didirikan dengan tujuan untuk membantu para profesional Indonesia dalam memperoleh pengakuan kompetensi melalui sertifikasi yang terstandar. Kami berkomitmen untuk memberikan layanan sertifikasi yang berkualitas, transparan, dan berintegritas.',
            'urutan' => 1,
            'aktif' => true,
        ]);

        // 3. Halaman Skema
        $skema = Halaman::create([
            'slug' => 'skema',
            'judul' => 'Skema Sertifikasi',
            'aktif' => true,
        ]);

        BagianHalaman::create([
            'halaman_id' => $skema->id,
            'tipe' => 'hero',
            'judul' => 'Skema Sertifikasi Kompetensi',
            'isi' => 'Temukan berbagai skema sertifikasi yang tersedia untuk meningkatkan kompetensi profesional Anda.',
            'urutan' => 0,
            'aktif' => true,
        ]);

        $skemaDaftar = BagianHalaman::create([
            'halaman_id' => $skema->id,
            'tipe' => 'daftar',
            'judul' => 'Daftar Skema Sertifikasi',
            'isi' => null,
            'urutan' => 1,
            'aktif' => true,
        ]);

        // Items untuk skema
        ItemBagianHalaman::create([
            'bagian_halaman_id' => $skemaDaftar->id,
            'judul' => 'Skema Administrasi Perkantoran',
            'deskripsi' => 'Sertifikasi kompetensi untuk tenaga administrasi perkantoran profesional.',
            'ikon' => 'fas fa-folder',
            'urutan' => 0,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $skemaDaftar->id,
            'judul' => 'Skema Manajemen SDM',
            'deskripsi' => 'Sertifikasi untuk praktisi sumber daya manusia.',
            'ikon' => 'fas fa-users',
            'urutan' => 1,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $skemaDaftar->id,
            'judul' => 'Skema Teknologi Informasi',
            'deskripsi' => 'Sertifikasi kompetensi di bidang teknologi informasi.',
            'ikon' => 'fas fa-laptop-code',
            'urutan' => 2,
        ]);

        // 4. Halaman Alur
        $alur = Halaman::create([
            'slug' => 'alur',
            'judul' => 'Alur Sertifikasi',
            'aktif' => true,
        ]);

        BagianHalaman::create([
            'halaman_id' => $alur->id,
            'tipe' => 'hero',
            'judul' => 'Alur Proses Sertifikasi',
            'isi' => 'Panduan lengkap tahapan proses sertifikasi dari awal hingga mendapatkan sertifikat kompetensi.',
            'urutan' => 0,
            'aktif' => true,
        ]);

        $alurDaftar = BagianHalaman::create([
            'halaman_id' => $alur->id,
            'tipe' => 'daftar',
            'judul' => 'Tahapan Sertifikasi',
            'isi' => null,
            'urutan' => 1,
            'aktif' => true,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $alurDaftar->id,
            'judul' => '1. Pendaftaran',
            'deskripsi' => 'Melakukan pendaftaran online dan mengisi formulir aplikasi.',
            'ikon' => 'fas fa-user-plus',
            'urutan' => 0,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $alurDaftar->id,
            'judul' => '2. Verifikasi Dokumen',
            'deskripsi' => 'Tim kami akan memverifikasi kelengkapan dokumen persyaratan.',
            'ikon' => 'fas fa-file-check',
            'urutan' => 1,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $alurDaftar->id,
            'judul' => '3. Uji Kompetensi',
            'deskripsi' => 'Mengikuti uji kompetensi sesuai dengan skema yang dipilih.',
            'ikon' => 'fas fa-clipboard-check',
            'urutan' => 2,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $alurDaftar->id,
            'judul' => '4. Penerbitan Sertifikat',
            'deskripsi' => 'Sertifikat diterbitkan bagi peserta yang dinyatakan kompeten.',
            'ikon' => 'fas fa-certificate',
            'urutan' => 3,
        ]);

        // 5. Halaman Persyaratan
        $persyaratan = Halaman::create([
            'slug' => 'persyaratan',
            'judul' => 'Persyaratan',
            'aktif' => true,
        ]);

        BagianHalaman::create([
            'halaman_id' => $persyaratan->id,
            'tipe' => 'hero',
            'judul' => 'Persyaratan Sertifikasi',
            'isi' => 'Ketahui persyaratan yang diperlukan untuk mengikuti proses sertifikasi kompetensi.',
            'urutan' => 0,
            'aktif' => true,
        ]);

        $persyaratanDaftar = BagianHalaman::create([
            'halaman_id' => $persyaratan->id,
            'tipe' => 'daftar',
            'judul' => 'Dokumen yang Diperlukan',
            'isi' => null,
            'urutan' => 1,
            'aktif' => true,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $persyaratanDaftar->id,
            'judul' => 'Fotokopi KTP',
            'deskripsi' => 'Kartu Tanda Penduduk yang masih berlaku.',
            'ikon' => 'fas fa-id-card',
            'urutan' => 0,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $persyaratanDaftar->id,
            'judul' => 'Pas Foto',
            'deskripsi' => 'Pas foto terbaru ukuran 3x4 dengan latar belakang merah.',
            'ikon' => 'fas fa-camera',
            'urutan' => 1,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $persyaratanDaftar->id,
            'judul' => 'Ijazah/Sertifikat',
            'deskripsi' => 'Fotokopi ijazah pendidikan terakhir atau sertifikat pelatihan terkait.',
            'ikon' => 'fas fa-graduation-cap',
            'urutan' => 2,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $persyaratanDaftar->id,
            'judul' => 'Surat Pengalaman Kerja',
            'deskripsi' => 'Surat keterangan pengalaman kerja di bidang terkait (jika ada).',
            'ikon' => 'fas fa-briefcase',
            'urutan' => 3,
        ]);

        // 6. Halaman Kontak
        $kontak = Halaman::create([
            'slug' => 'kontak',
            'judul' => 'Hubungi Kami',
            'aktif' => true,
        ]);

        BagianHalaman::create([
            'halaman_id' => $kontak->id,
            'tipe' => 'hero',
            'judul' => 'Hubungi Kami',
            'isi' => 'Jangan ragu untuk menghubungi kami jika Anda memiliki pertanyaan atau membutuhkan informasi lebih lanjut.',
            'urutan' => 0,
            'aktif' => true,
        ]);

        $kontakDaftar = BagianHalaman::create([
            'halaman_id' => $kontak->id,
            'tipe' => 'daftar',
            'judul' => 'Informasi Kontak',
            'isi' => null,
            'urutan' => 1,
            'aktif' => true,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $kontakDaftar->id,
            'judul' => 'Alamat Kantor',
            'deskripsi' => 'Jl. Sertifikasi No. 123, Jakarta Selatan 12345',
            'ikon' => 'fas fa-map-marker-alt',
            'urutan' => 0,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $kontakDaftar->id,
            'judul' => 'Telepon',
            'deskripsi' => '(021) 1234-5678',
            'ikon' => 'fas fa-phone',
            'urutan' => 1,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $kontakDaftar->id,
            'judul' => 'Email',
            'deskripsi' => 'info@certipro.id',
            'ikon' => 'fas fa-envelope',
            'urutan' => 2,
        ]);

        $faqBagian = BagianHalaman::create([
            'halaman_id' => $kontak->id,
            'tipe' => 'faq',
            'judul' => 'Pertanyaan yang Sering Diajukan',
            'isi' => null,
            'urutan' => 2,
            'aktif' => true,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $faqBagian->id,
            'judul' => 'Berapa lama proses sertifikasi?',
            'deskripsi' => 'Proses sertifikasi biasanya memakan waktu 2-4 minggu tergantung pada skema yang dipilih dan kelengkapan dokumen.',
            'ikon' => null,
            'urutan' => 0,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $faqBagian->id,
            'judul' => 'Berapa biaya sertifikasi?',
            'deskripsi' => 'Biaya sertifikasi bervariasi tergantung skema. Silakan hubungi kami untuk informasi biaya detail.',
            'ikon' => null,
            'urutan' => 1,
        ]);

        ItemBagianHalaman::create([
            'bagian_halaman_id' => $faqBagian->id,
            'judul' => 'Apakah sertifikat berlaku seumur hidup?',
            'deskripsi' => 'Sertifikat kompetensi umumnya berlaku selama 3 tahun dan dapat diperpanjang melalui proses re-sertifikasi.',
            'ikon' => null,
            'urutan' => 2,
        ]);

        $this->command->info('Landing Page CMS seeder completed!');
        $this->command->info('- 6 halaman dibuat (home, tentang, skema, alur, persyaratan, kontak)');
        $this->command->info('- Total bagian halaman: ' . BagianHalaman::count());
        $this->command->info('- Total item bagian: ' . ItemBagianHalaman::count());
    }
}
