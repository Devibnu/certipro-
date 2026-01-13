<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\BagianHalaman;
use App\Models\Halaman;
use Illuminate\Http\Request;

class BagianHalamanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BagianHalaman::with('halaman')->orderBy('urutan');
        
        if ($request->has('halaman_id')) {
            $query->where('halaman_id', $request->halaman_id);
        }
        
        $bagianHalaman = $query->get();
        $halamanList = Halaman::all();
        
        return view('adminui.bagian-halaman.index', compact('bagianHalaman', 'halamanList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $halamanList = Halaman::all();
        $halamanId = $request->get('halaman_id');
        
        return view('adminui.bagian-halaman.create', compact('halamanList', 'halamanId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'halaman_id' => 'required|exists:halaman,id',
            'tipe' => 'required|in:hero,teks,daftar,faq',
            'judul' => 'required|string|max:255',
            'isi' => 'nullable|string',
            'urutan' => 'required|integer|min:0',
            'aktif' => 'boolean',
        ]);

        $validated['aktif'] = $request->has('aktif');

        BagianHalaman::create($validated);

        return redirect()->route('adminui.bagian-halaman.index', ['halaman_id' => $validated['halaman_id']])
            ->with('success', 'Bagian halaman berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(BagianHalaman $bagianHalaman)
    {
        $bagianHalaman->load(['halaman', 'itemBagianHalaman']);
        return view('adminui.bagian-halaman.show', compact('bagianHalaman'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BagianHalaman $bagianHalaman)
    {
        $halamanList = Halaman::all();
        return view('adminui.bagian-halaman.edit', compact('bagianHalaman', 'halamanList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BagianHalaman $bagianHalaman)
    {
        $validated = $request->validate([
            'halaman_id' => 'required|exists:halaman,id',
            'tipe' => 'required|in:hero,teks,daftar,faq',
            'judul' => 'required|string|max:255',
            'isi' => 'nullable|string',
            'urutan' => 'required|integer|min:0',
            'aktif' => 'boolean',
        ]);

        $validated['aktif'] = $request->has('aktif');

        $bagianHalaman->update($validated);

        return redirect()->route('adminui.bagian-halaman.index', ['halaman_id' => $validated['halaman_id']])
            ->with('success', 'Bagian halaman berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BagianHalaman $bagianHalaman)
    {
        $halamanId = $bagianHalaman->halaman_id;
        $bagianHalaman->delete();

        return redirect()->route('adminui.bagian-halaman.index', ['halaman_id' => $halamanId])
            ->with('success', 'Bagian halaman berhasil dihapus');
    }
}
