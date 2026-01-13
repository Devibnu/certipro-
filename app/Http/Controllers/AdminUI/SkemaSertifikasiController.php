<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\SkemaSertifikasi;
use Illuminate\Http\Request;

class SkemaSertifikasiController extends Controller
{
    /**
     * Tampilkan daftar skema sertifikasi
     */
    public function index(Request $request)
    {
        $query = SkemaSertifikasi::query()->orderBy('kode_skema', 'asc');

        // Filter by jenis
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        // Filter by status aktif
        if ($request->filled('aktif')) {
            $query->where('aktif', $request->aktif === '1');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_skema', 'like', "%{$search}%")
                  ->orWhere('nama_skema', 'like', "%{$search}%");
            });
        }

        $skema = $query->paginate(15)->withQueryString();
        $jenisLabels = SkemaSertifikasi::jenisLabels();

        return view('adminui.skema-sertifikasi.index', compact('skema', 'jenisLabels'));
    }

    /**
     * Form tambah skema baru
     */
    public function create()
    {
        $jenisLabels = SkemaSertifikasi::jenisLabels();
        return view('adminui.skema-sertifikasi.form', compact('jenisLabels'));
    }

    /**
     * Simpan skema baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_skema' => 'required|string|max:50|unique:skema_sertifikasi,kode_skema',
            'nama_skema' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis' => 'required|in:nasional,internasional,internal',
            'masa_berlaku' => 'nullable|integer|min:1|max:10',
            'aktif' => 'boolean',
        ], [
            'kode_skema.required' => 'Kode skema wajib diisi',
            'kode_skema.unique' => 'Kode skema sudah digunakan',
            'nama_skema.required' => 'Nama skema wajib diisi',
            'jenis.required' => 'Jenis skema wajib dipilih',
            'masa_berlaku.integer' => 'Masa berlaku harus berupa angka',
            'masa_berlaku.min' => 'Masa berlaku minimal 1 tahun',
        ]);

        $validated['aktif'] = $request->has('aktif');

        SkemaSertifikasi::create($validated);

        return redirect()->route('adminui.skema-sertifikasi.index')
            ->with('success', 'Skema sertifikasi berhasil ditambahkan');
    }

    /**
     * Form edit skema
     */
    public function edit(SkemaSertifikasi $skemaSertifikasi)
    {
        $jenisLabels = SkemaSertifikasi::jenisLabels();
        return view('adminui.skema-sertifikasi.form', [
            'skema' => $skemaSertifikasi,
            'jenisLabels' => $jenisLabels,
        ]);
    }

    /**
     * Update skema
     */
    public function update(Request $request, SkemaSertifikasi $skemaSertifikasi)
    {
        $validated = $request->validate([
            'kode_skema' => 'required|string|max:50|unique:skema_sertifikasi,kode_skema,' . $skemaSertifikasi->id,
            'nama_skema' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis' => 'required|in:nasional,internasional,internal',
            'masa_berlaku' => 'nullable|integer|min:1|max:10',
            'aktif' => 'boolean',
        ], [
            'kode_skema.required' => 'Kode skema wajib diisi',
            'kode_skema.unique' => 'Kode skema sudah digunakan',
            'nama_skema.required' => 'Nama skema wajib diisi',
            'jenis.required' => 'Jenis skema wajib dipilih',
            'masa_berlaku.integer' => 'Masa berlaku harus berupa angka',
            'masa_berlaku.min' => 'Masa berlaku minimal 1 tahun',
        ]);

        $validated['aktif'] = $request->has('aktif');

        $skemaSertifikasi->update($validated);

        return redirect()->route('adminui.skema-sertifikasi.index')
            ->with('success', 'Skema sertifikasi berhasil diperbarui');
    }

    /**
     * Hapus skema
     */
    public function destroy(SkemaSertifikasi $skemaSertifikasi)
    {
        $skemaSertifikasi->delete();

        return redirect()->route('adminui.skema-sertifikasi.index')
            ->with('success', 'Skema sertifikasi berhasil dihapus');
    }

    /**
     * Tampilkan detail skema (opsional)
     */
    public function show(SkemaSertifikasi $skemaSertifikasi)
    {
        return redirect()->route('adminui.skema-sertifikasi.edit', $skemaSertifikasi);
    }
}
