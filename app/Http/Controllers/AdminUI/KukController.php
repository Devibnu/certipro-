<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Kuk;
use App\Models\UnitKompetensi;
use App\Models\SkemaSertifikasi;
use Illuminate\Http\Request;

class KukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Kuk::with(['unitKompetensi.skemaSertifikasi']);
        
        // Filter by Skema Sertifikasi
        if ($request->filled('skema_sertifikasi_id')) {
            $query->whereHas('unitKompetensi', function ($q) use ($request) {
                $q->where('skema_sertifikasi_id', $request->skema_sertifikasi_id);
            });
        }
        
        // Filter by Unit Kompetensi
        if ($request->filled('unit_kompetensi_id')) {
            $query->where('unit_kompetensi_id', $request->unit_kompetensi_id);
        }
        
        // Filter by status aktif
        if ($request->filled('aktif')) {
            $query->where('aktif', $request->aktif === '1');
        }
        
        // Search by kode_kuk atau pernyataan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_kuk', 'like', "%{$search}%")
                  ->orWhere('pernyataan_unjuk_kerja', 'like', "%{$search}%");
            });
        }
        
        $kukList = $query->orderBy('unit_kompetensi_id')
                         ->orderBy('urutan')
                         ->paginate(15)
                         ->withQueryString();
        
        $skemaList = SkemaSertifikasi::aktif()->orderBy('nama_skema')->get();
        $unitList = UnitKompetensi::aktif()
            ->when($request->filled('skema_sertifikasi_id'), function ($q) use ($request) {
                $q->where('skema_sertifikasi_id', $request->skema_sertifikasi_id);
            })
            ->orderBy('kode_unit')
            ->get();
        
        return view('adminui.kuk.index', compact('kukList', 'skemaList', 'unitList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $skemaList = SkemaSertifikasi::aktif()->orderBy('nama_skema')->get();
        $unitList = UnitKompetensi::aktif()->orderBy('kode_unit')->get();
        
        $selectedSkemaId = $request->skema_sertifikasi_id;
        $selectedUnitId = $request->unit_kompetensi_id;
        
        // Get next urutan for selected unit
        $nextUrutan = 1;
        if ($selectedUnitId) {
            $nextUrutan = Kuk::where('unit_kompetensi_id', $selectedUnitId)->max('urutan') + 1;
        }
        
        return view('adminui.kuk.form', compact('skemaList', 'unitList', 'selectedSkemaId', 'selectedUnitId', 'nextUrutan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_kompetensi_id' => 'required|exists:unit_kompetensi,id',
            'kode_kuk' => 'required|string|max:50',
            'pernyataan_unjuk_kerja' => 'required|string',
            'urutan' => 'required|integer|min:1',
            'aktif' => 'boolean',
        ], [
            'unit_kompetensi_id.required' => 'Unit Kompetensi wajib dipilih.',
            'unit_kompetensi_id.exists' => 'Unit Kompetensi tidak valid.',
            'kode_kuk.required' => 'Kode KUK wajib diisi.',
            'pernyataan_unjuk_kerja.required' => 'Pernyataan Unjuk Kerja wajib diisi.',
            'urutan.required' => 'Urutan wajib diisi.',
        ]);
        
        $validated['aktif'] = $request->has('aktif');
        
        Kuk::create($validated);
        
        return redirect()
            ->route('adminui.kuk.index')
            ->with('success', 'KUK berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Kuk $kuk)
    {
        return redirect()->route('adminui.kuk.edit', $kuk);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kuk $kuk)
    {
        $kuk->load('unitKompetensi.skemaSertifikasi');
        
        $skemaList = SkemaSertifikasi::aktif()->orderBy('nama_skema')->get();
        $unitList = UnitKompetensi::aktif()->orderBy('kode_unit')->get();
        
        $selectedSkemaId = $kuk->unitKompetensi->skema_sertifikasi_id ?? null;
        
        return view('adminui.kuk.form', compact('kuk', 'skemaList', 'unitList', 'selectedSkemaId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kuk $kuk)
    {
        $validated = $request->validate([
            'unit_kompetensi_id' => 'required|exists:unit_kompetensi,id',
            'kode_kuk' => 'required|string|max:50',
            'pernyataan_unjuk_kerja' => 'required|string',
            'urutan' => 'required|integer|min:1',
            'aktif' => 'boolean',
        ], [
            'unit_kompetensi_id.required' => 'Unit Kompetensi wajib dipilih.',
            'unit_kompetensi_id.exists' => 'Unit Kompetensi tidak valid.',
            'kode_kuk.required' => 'Kode KUK wajib diisi.',
            'pernyataan_unjuk_kerja.required' => 'Pernyataan Unjuk Kerja wajib diisi.',
            'urutan.required' => 'Urutan wajib diisi.',
        ]);
        
        $validated['aktif'] = $request->has('aktif');
        
        $kuk->update($validated);
        
        return redirect()
            ->route('adminui.kuk.index')
            ->with('success', 'KUK berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kuk $kuk)
    {
        $kodeKuk = $kuk->kode_kuk;
        
        $kuk->delete();
        
        return redirect()
            ->route('adminui.kuk.index')
            ->with('success', "KUK {$kodeKuk} berhasil dihapus.");
    }

    /**
     * Get Unit Kompetensi by Skema Sertifikasi (for AJAX dropdown).
     */
    public function getUnitBySkema($skemaId)
    {
        $units = UnitKompetensi::aktif()
            ->where('skema_sertifikasi_id', $skemaId)
            ->orderBy('kode_unit')
            ->get(['id', 'kode_unit', 'nama_unit']);
        
        return response()->json($units);
    }
}
