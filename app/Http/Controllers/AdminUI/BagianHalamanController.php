<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\BagianHalaman;
use App\Models\Halaman;
use App\Models\ItemBagianHalaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Store a new item for bagian halaman (hero slide, faq item, etc)
     */
    public function storeItem(Request $request, BagianHalaman $bagianHalaman)
    {
        $rules = [
            'judul' => 'required|string|max:255',
            'subjudul' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer|min:1',
            'aktif' => 'nullable',
        ];

        // Tambahan validasi untuk hero
        if ($bagianHalaman->tipe == 'hero') {
            $rules['gambar'] = 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
            $rules['tombol_text'] = 'nullable|string|max:100';
            $rules['tombol_link'] = 'nullable|string|max:255';
            $rules['tombol_text_2'] = 'nullable|string|max:100';
            $rules['tombol_link_2'] = 'nullable|string|max:255';
        } else {
            $rules['ikon'] = 'nullable|string|max:100';
        }

        $validated = $request->validate($rules);

        $data = [
            'bagian_halaman_id' => $bagianHalaman->id,
            'judul' => $validated['judul'],
            'subjudul' => $validated['subjudul'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'urutan' => $validated['urutan'] ?? ($bagianHalaman->itemBagianHalaman()->count() + 1),
            'aktif' => $request->has('aktif'),
        ];

        // Handle gambar upload untuk hero
        if ($bagianHalaman->tipe == 'hero' && $request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('hero-slides', $filename, 'public');
            $data['gambar'] = $path;
        }

        // Handle tombol untuk hero
        if ($bagianHalaman->tipe == 'hero') {
            $data['tombol_text'] = $validated['tombol_text'] ?? null;
            $data['tombol_link'] = $validated['tombol_link'] ?? null;
            $data['tombol_text_2'] = $validated['tombol_text_2'] ?? null;
            $data['tombol_link_2'] = $validated['tombol_link_2'] ?? null;
        } else {
            $data['ikon'] = $validated['ikon'] ?? null;
        }

        ItemBagianHalaman::create($data);

        return redirect()->route('adminui.bagian-halaman.edit', $bagianHalaman->id)
            ->with('success', ($bagianHalaman->tipe == 'hero' ? 'Slide hero' : 'Item') . ' berhasil ditambahkan');
    }

    /**
     * Get item edit form (for AJAX modal)
     */
    public function editItem(ItemBagianHalaman $item)
    {
        $bagianHalaman = $item->bagianHalaman;
        
        return view('adminui.bagian-halaman.partials.edit-item-form', compact('item', 'bagianHalaman'));
    }

    /**
     * Update an item
     */
    public function updateItem(Request $request, ItemBagianHalaman $item)
    {
        $bagianHalaman = $item->bagianHalaman;

        $rules = [
            'judul' => 'required|string|max:255',
            'subjudul' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer|min:1',
            'aktif' => 'nullable',
        ];

        if ($bagianHalaman->tipe == 'hero') {
            $rules['gambar'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
            $rules['tombol_text'] = 'nullable|string|max:100';
            $rules['tombol_link'] = 'nullable|string|max:255';
            $rules['tombol_text_2'] = 'nullable|string|max:100';
            $rules['tombol_link_2'] = 'nullable|string|max:255';
        } else {
            $rules['ikon'] = 'nullable|string|max:100';
        }

        $validated = $request->validate($rules);

        $data = [
            'judul' => $validated['judul'],
            'subjudul' => $validated['subjudul'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'urutan' => $validated['urutan'] ?? $item->urutan,
            'aktif' => $request->has('aktif'),
        ];

        // Handle gambar upload untuk hero
        if ($bagianHalaman->tipe == 'hero' && $request->hasFile('gambar')) {
            // Hapus gambar lama
            if ($item->gambar && Storage::disk('public')->exists($item->gambar)) {
                Storage::disk('public')->delete($item->gambar);
            }
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('hero-slides', $filename, 'public');
            $data['gambar'] = $path;
        }

        // Handle tombol untuk hero
        if ($bagianHalaman->tipe == 'hero') {
            $data['tombol_text'] = $validated['tombol_text'] ?? null;
            $data['tombol_link'] = $validated['tombol_link'] ?? null;
            $data['tombol_text_2'] = $validated['tombol_text_2'] ?? null;
            $data['tombol_link_2'] = $validated['tombol_link_2'] ?? null;
        } else {
            $data['ikon'] = $validated['ikon'] ?? null;
        }

        $item->update($data);

        return redirect()->route('adminui.bagian-halaman.edit', $bagianHalaman->id)
            ->with('success', ($bagianHalaman->tipe == 'hero' ? 'Slide hero' : 'Item') . ' berhasil diperbarui');
    }

    /**
     * Delete an item
     */
    public function deleteItem(ItemBagianHalaman $item)
    {
        $bagianHalaman = $item->bagianHalaman;

        // Hapus gambar jika ada
        if ($item->gambar && Storage::disk('public')->exists($item->gambar)) {
            Storage::disk('public')->delete($item->gambar);
        }

        $item->delete();

        return redirect()->route('adminui.bagian-halaman.edit', $bagianHalaman->id)
            ->with('success', 'Item berhasil dihapus');
    }
}
