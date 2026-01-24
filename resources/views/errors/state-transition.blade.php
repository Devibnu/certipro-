<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Transisi Status Tidak Diizinkan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-2xl w-full">
        {{-- Error Card --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-8 text-white">
                <div class="flex items-center justify-center mb-4">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-center">Transisi Status Tidak Diizinkan</h1>
                <p class="text-center text-red-100 mt-2">Operasi yang Anda lakukan melanggar aturan State Machine</p>
            </div>

            {{-- Content --}}
            <div class="px-6 py-8">
                {{-- Error Message --}}
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-red-800 font-semibold text-lg mb-1">Error Detail</h3>
                            <p class="text-red-700">{{ $message ?? 'Transisi status tidak diizinkan oleh sistem' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Transition Info --}}
                @if(isset($fromStatus) && isset($toStatus))
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                    <h3 class="text-blue-800 font-semibold mb-2">Informasi Transisi</h3>
                    <div class="flex items-center space-x-4 text-blue-700">
                        <div class="flex-1">
                            <p class="text-sm text-blue-600">Status Saat Ini:</p>
                            <p class="font-bold">{{ $fromStatus }}</p>
                        </div>
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm text-blue-600">Status Tujuan:</p>
                            <p class="font-bold">{{ $toStatus }}</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Context Info --}}
                @if(isset($context) && !empty($context))
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                    <h3 class="text-gray-800 font-semibold mb-2">Informasi Tambahan</h3>
                    <ul class="space-y-1 text-sm text-gray-700">
                        @foreach($context as $key => $value)
                            <li class="flex items-start">
                                <span class="font-medium mr-2">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Explanation --}}
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-yellow-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-yellow-800 font-semibold mb-2">Mengapa Ini Terjadi?</h3>
                            <p class="text-yellow-700 text-sm leading-relaxed">
                                Sistem sertifikasi menggunakan <strong>State Machine</strong> untuk memastikan integritas data. 
                                Setiap perubahan status harus mengikuti alur yang telah ditentukan dan memenuhi semua persyaratan. 
                                Hal ini mencegah data tidak konsisten dan memastikan proses sertifikasi sesuai standar ISO 17024 / BNSP.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Solutions --}}
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                    <h3 class="text-green-800 font-semibold mb-3">Langkah yang Dapat Dilakukan</h3>
                    <ol class="list-decimal list-inside space-y-2 text-sm text-green-700">
                        <li>Periksa apakah semua data yang diperlukan sudah lengkap</li>
                        <li>Pastikan status saat ini memang bisa bertransisi ke status tujuan</li>
                        <li>Hubungi admin jika Anda yakin ini adalah error sistem</li>
                        <li>Lihat dokumentasi State Machine untuk memahami alur yang benar</li>
                    </ol>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <button 
                        onclick="window.history.back()" 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-150 flex items-center justify-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali
                    </button>
                    
                    <a 
                        href="{{ url('/') }}" 
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-lg transition duration-150 flex items-center justify-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-100 px-6 py-4 text-center text-xs text-gray-600">
                <p>
                    Jika masalah berlanjut, hubungi administrator sistem
                    <span class="mx-2">•</span>
                    <a href="mailto:support@lsp.example.com" class="text-blue-600 hover:underline">support@lsp.example.com</a>
                </p>
            </div>
        </div>

        {{-- Debug Info (Only in development) --}}
        @if(config('app.debug'))
        <div class="mt-4 bg-gray-800 text-gray-300 rounded-lg p-4 text-xs font-mono overflow-x-auto">
            <p class="text-red-400 font-bold mb-2">🔧 DEBUG MODE</p>
            <pre>{{ json_encode([
                'message' => $message ?? null,
                'fromStatus' => $fromStatus ?? null,
                'toStatus' => $toStatus ?? null,
                'context' => $context ?? [],
                'trace' => isset($exception) ? $exception->getTraceAsString() : 'N/A'
            ], JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif
    </div>
</body>
</html>
