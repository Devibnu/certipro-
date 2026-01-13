<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\UnitKompetensi;
use App\Models\SkemaSertifikasi;
use Illuminate\Http\Request;

class UnitKompetensiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = UnitKompetensi::with('skemaSertifikasi');
        
        // Filter by Skema Sertifikasi
        if ($request->filled('skema_sertifikasi_id')) {
            $query->where('skema_sertifikasi_id', $request->skema_sertifikasi_id);
        }
        
        // Filter by status aktif
        if ($request->filled('aktif')) {
            $query->where('aktif', $request->aktif === '1');
        }
        
        // Search by kode atau nama unit
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_unit', 'like', "%{$search}%")
                  ->orWhere('nama_unit', 'like', "%{$search}%");
            });
        }
        
        $unitKompetensi = $query->orderBy('skema_sertifikasi_id')
                                 ->orderBy('kode_unit')
                                 ->paginate(15)
                                 ->withQueryString();
        
        $skemaList = SkemaSertifikasi::aktif()->orderBy('nama_skema')->get();
        
        return view('adminui.unit-kompetensi.index', compact('unitKompetensi', 'skemaList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $skemaList = SkemaSertifikasi::aktif()->orderBy('nama_skema')->get();
        $selectedSkemaId = $request->skema_sertifikasi_id;
        
        return view('adminui.unit-kompetensi.form', compact('skemaList', 'selectedSkemaId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'skema_sertifikasi_id' => 'required|exists:skema_sertifikasi,id',
            'kode_unit' => 'required|string|max:50',
            'nama_unit' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'aktif' => 'boolean',
        ], [
            'skema_sertifikasi_id.required' => 'Skema Sertifikasi wajib dipilih.',
            'skema_sertifikasi_id.exists' => 'Skema Sertifikasi tidak valid.',
            'kode_unit.required' => 'Kode Unit wajib diisi.',
            'nama_unit.required' => 'Nama Unit wajib diisi.',
        ]);
        
        $validated['aktif'] = $request->has('aktif');
        
        UnitKompetensi::create($validated);
        
        return redirect()
            ->route('adminui.unit-kompetensi.index', ['skema_sertifikasi_id' => $validated['skema_sertifikasi_id']])
            ->with('success', 'Unit Kompetensi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(UnitKompetensi $unitKompetensi)
    {
        return redirect()->route('adminui.unit-kompetensi.edit', $unitKompetensi);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnitKompetensi $unitKompetensi)
    {
        $skemaList = SkemaSertifikasi::aktif()->orderBy('nama_skema')->get();
        
        return view('adminui.unit-kompetensi.form', compact('unitKompetensi', 'skemaList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnitKompetensi $unitKompetensi)
    {
        $validated = $request->validate([
            'skema_sertifikasi_id' => 'required|exists:skema_sertifikasi,id',
            'kode_unit' => 'required|string|max:50',
            'nama_unit' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'aktif' => 'boolean',
        ], [
            'skema_sertifikasi_id.required' => 'Skema Sertifikasi wajib dipilih.',
            'skema_sertifikasi_id.exists' => 'Skema Sertifikasi tidak valid.',
            'kode_unit.required' => 'Kode Unit wajib diisi.',
            'nama_unit.required' => 'Nama Unit wajib diisi.',
        ]);
        
        $validated['aktif'] = $request->has('aktif');
        
        $unitKompetensi->update($validated);
        
        return redirect()
            ->route('adminui.unit-kompetensi.index', ['skema_sertifikasi_id' => $validated['skema_sertifikasi_id']])
            ->with('success', 'Unit Kompetensi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnitKompetensi $unitKompetensi)
    {
        $skemaId = $unitKompetensi->skema_sertifikasi_id;
        $kodeUnit = $unitKompetensi->kode_unit;
        
        $unitKompetensi->delete();
        
        return redirect()
            ->route('adminui.unit-kompetensi.index', ['skema_sertifikasi_id' => $skemaId])
            ->with('success', "Unit Kompetensi {$kodeUnit} berhasil dihapus.");
    }
}
