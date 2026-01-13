<?php

namespace App\Http\Controllers;

use App\Models\Halaman;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    /**
     * Render homepage (slug = 'home')
     */
    public function home()
    {
        return $this->show('home');
    }

    /**
     * Render halaman by slug
     */
    public function show($slug)
    {
        // Get halaman by slug and check if active
        $halaman = Halaman::with(['bagianHalamanAktif' => function($query) {
                $query->orderBy('urutan', 'asc');
            }, 'bagianHalamanAktif.itemBagianHalaman' => function($query) {
                $query->orderBy('urutan', 'asc');
            }])
            ->where('slug', $slug)
            ->where('aktif', true)
            ->first();
        
        // Return 404 if halaman not found or not active
        if (!$halaman) {
            abort(404);
        }
        
        // Return view with halaman data using elearning template
        return view('public.halaman', compact('halaman'));
    }
}
