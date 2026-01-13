<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Halaman;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HalamanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $halaman = Halaman::withCount('bagianHalaman')->orderBy('created_at', 'desc')->get();
        return view('adminui.halaman.index', compact('halaman'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('adminui.halaman.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:halaman,slug',
            'aktif' => 'boolean',
        ]);

        $validated['aktif'] = $request->has('aktif');

        Halaman::create($validated);

        return redirect()->route('adminui.halaman.index')
            ->with('success', 'Halaman berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Halaman $halaman)
    {
        $halaman->load('bagianHalaman');
        return view('adminui.halaman.show', compact('halaman'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Halaman $halaman)
    {
        return view('adminui.halaman.edit', compact('halaman'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Halaman $halaman)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:halaman,slug,' . $halaman->id,
            'aktif' => 'boolean',
        ]);

        $validated['aktif'] = $request->has('aktif');

        $halaman->update($validated);

        return redirect()->route('adminui.halaman.index')
            ->with('success', 'Halaman berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Halaman $halaman)
    {
        $halaman->delete();

        return redirect()->route('adminui.halaman.index')
            ->with('success', 'Halaman berhasil dihapus');
    }
}
