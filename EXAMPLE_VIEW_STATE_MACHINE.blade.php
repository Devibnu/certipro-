{{-- 
    EXAMPLE VIEW IMPLEMENTATION
    File: resources/views/pendaftaran/show.blade.php
    
    This demonstrates how to use state-guard-button and status-badge components
--}}

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header with Status Badge --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Pendaftaran Sertifikasi #{{ $pendaftaran->nomor_pendaftaran }}
            </h1>
            <p class="text-gray-600 mt-1">Dibuat: {{ $pendaftaran->created_at->format('d/m/Y H:i') }}</p>
        </div>
        
        {{-- Status Badge --}}
        <x-status-badge :status="$pendaftaran->status" size="lg" />
    </div>

    {{-- Lock Warning (if locked) --}}
    @if($pendaftaran->is_locked)
    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h3 class="text-yellow-800 font-semibold">🔒 Data Terkunci</h3>
                <p class="text-yellow-700 text-sm mt-1">
                    {{ $pendaftaran->locked_reason ?? 'Data telah terkunci dan tidak dapat diubah' }}
                </p>
                @if($pendaftaran->locked_at)
                <p class="text-yellow-600 text-xs mt-1">
                    Dikunci pada: {{ $pendaftaran->locked_at->format('d/m/Y H:i') }}
                </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Pendaftaran Details Card --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Data Pendaftaran</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-gray-600">Nama Lengkap</label>
                <p class="text-gray-900">{{ $pendaftaran->nama_lengkap ?? '-' }}</p>
            </div>
            
            <div>
                <label class="text-sm font-medium text-gray-600">Email</label>
                <p class="text-gray-900">{{ $pendaftaran->email ?? '-' }}</p>
            </div>
            
            <div>
                <label class="text-sm font-medium text-gray-600">NIK</label>
                <p class="text-gray-900">{{ $pendaftaran->nik ?? '-' }}</p>
            </div>
            
            <div>
                <label class="text-sm font-medium text-gray-600">Skema Sertifikasi</label>
                <p class="text-gray-900">{{ $pendaftaran->skemaSertifikasi->nama_skema ?? '-' }}</p>
            </div>
        </div>

        @if($pendaftaran->catatan_admin)
        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
            <label class="text-sm font-medium text-blue-800">Catatan Admin</label>
            <p class="text-blue-700 text-sm mt-1">{{ $pendaftaran->catatan_admin }}</p>
        </div>
        @endif
    </div>

    {{-- Action Buttons with State Guards --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Aksi</h2>
        
        <div class="flex flex-wrap gap-3">
            {{-- Edit Button --}}
            <x-state-guard-button
                :can-perform="$guards['can_edit']['can']"
                :reason="$guards['can_edit']['reason']"
                action="edit"
                :route="route('pendaftaran.edit', $pendaftaran)"
                method="GET"
                icon="✏️"
                class="secondary"
            >
                Edit Data
            </x-state-guard-button>

            {{-- Submit Button --}}
            <x-state-guard-button
                :can-perform="$guards['can_submit']['can']"
                :reason="$guards['can_submit']['reason']"
                action="submit"
                :route="route('pendaftaran.submit', $pendaftaran)"
                method="POST"
                confirm-message="Yakin mengajukan pendaftaran ini? Data tidak bisa diubah setelah diajukan."
                icon="📤"
                class="success"
            >
                Ajukan Pendaftaran
            </x-state-guard-button>

            @can('verify-pendaftaran')
                {{-- Verify Button --}}
                <x-state-guard-button
                    :can-perform="$guards['can_verify']['can']"
                    :reason="$guards['can_verify']['reason']"
                    action="verify"
                    :route="route('pendaftaran.verify', $pendaftaran)"
                    method="POST"
                    confirm-message="Verifikasi pendaftaran ini?"
                    icon="✓"
                    class="primary"
                >
                    Verifikasi
                </x-state-guard-button>

                {{-- Reject Button --}}
                <x-state-guard-button
                    :can-perform="$guards['can_verify']['can']"
                    :reason="$guards['can_verify']['reason']"
                    action="reject"
                    :route="route('pendaftaran.reject', $pendaftaran)"
                    method="POST"
                    confirm-message="Tolak pendaftaran ini? Tindakan tidak dapat dibatalkan."
                    icon="❌"
                    class="danger"
                >
                    Tolak
                </x-state-guard-button>
            @endcan

            @can('lock-pendaftaran')
                {{-- Lock Button --}}
                <x-state-guard-button
                    :can-perform="$guards['can_lock']['can']"
                    :reason="$guards['can_lock']['reason']"
                    action="lock"
                    :route="route('pendaftaran.lock', $pendaftaran)"
                    method="POST"
                    confirm-message="Kunci pendaftaran dan buat asesmen? Data tidak bisa diubah lagi."
                    icon="🔒"
                    class="warning"
                >
                    Kunci & Buat Asesmen
                </x-state-guard-button>
            @endcan
        </div>
    </div>

    {{-- Status History Timeline --}}
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h2 class="text-xl font-semibold mb-4">Riwayat Status</h2>
        
        <div class="space-y-4">
            @foreach($pendaftaran->statusHistory ?? [] as $history)
            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                    <span class="text-blue-600 text-sm">●</span>
                </div>
                <div class="ml-4 flex-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <x-status-badge :status="$history->status" size="sm" />
                            <p class="text-sm text-gray-600 mt-1">
                                oleh {{ $history->user->name ?? 'System' }}
                            </p>
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $history->created_at->diffForHumans() }}
                        </span>
                    </div>
                    @if($history->reason)
                    <p class="text-sm text-gray-700 mt-2 bg-gray-50 p-2 rounded">
                        {{ $history->reason }}
                    </p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

{{-- 
===========================================
USAGE IN CONTROLLER:
===========================================

public function show(PendaftaranSertifikasi $pendaftaran)
{
    $guards = [
        'can_edit' => [
            'can' => !$this->stateService->isLocked($pendaftaran) && 
                     $pendaftaran->status === PendaftaranStatus::DRAFT->value,
            'reason' => $this->getEditBlockReason($pendaftaran),
        ],
        'can_submit' => [
            'can' => $this->canSubmit($pendaftaran),
            'reason' => $this->getSubmitBlockReason($pendaftaran),
        ],
        'can_verify' => [
            'can' => $pendaftaran->status === PendaftaranStatus::DIAJUKAN->value,
            'reason' => $this->getVerifyBlockReason($pendaftaran),
        ],
        'can_lock' => [
            'can' => $pendaftaran->status === PendaftaranStatus::DIVERIFIKASI->value,
            'reason' => $this->getLockBlockReason($pendaftaran),
        ],
    ];

    return view('pendaftaran.show', compact('pendaftaran', 'guards'));
}

===========================================
--}}
