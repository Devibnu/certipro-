{{--
================================================================================
Evidence per KUK - Partial Component
================================================================================
File: resources/views/adminui/asesmen/partials/evidence-kuk.blade.php
Purpose: Upload & display evidence files/links per KUK
Compliance: ISO 17024, BNSP
Variables: $asesmenId, $kukId, $kukKode, $isLocked
================================================================================
--}}

<div class="evidence-kuk-container mt-3" 
     id="evidence-container-{{ $kukId }}" 
     data-asesmen-id="{{ $asesmenId }}" 
     data-kuk-id="{{ $kukId }}">
    
    {{-- Evidence Header --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-xs text-uppercase text-secondary font-weight-bold">
            <i class="fas fa-paperclip me-1"></i> Evidence / Bukti
        </span>
        @if(!$isLocked && auth()->check() && auth()->user()->hasPermission('evidence.upload'))
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-primary btn-xs" 
                    onclick="showUploadModal({{ $asesmenId }}, {{ $kukId }}, '{{ $kukKode }}')"
                    title="Upload File">
                <i class="fas fa-upload"></i>
            </button>
            <button type="button" class="btn btn-outline-info btn-xs" 
                    onclick="showLinkModal({{ $asesmenId }}, {{ $kukId }}, '{{ $kukKode }}')"
                    title="Tambah Link">
                <i class="fas fa-link"></i>
            </button>
        </div>
        @endif
    </div>
    
    {{-- Evidence List --}}
    <div class="evidence-list" id="evidence-list-{{ $kukId }}">
        @forelse($evidences ?? [] as $evidence)
        <div class="evidence-item d-flex align-items-center p-2 border rounded mb-2" 
             id="evidence-item-{{ $evidence->id }}">
            <div class="evidence-icon me-2">
                <i class="{{ $evidence->file_icon }} fa-lg"></i>
            </div>
            <div class="evidence-info flex-grow-1">
                @if($evidence->isFile())
                <a href="{{ route('adminui.evidence.download', $evidence->id) }}" 
                   class="text-sm text-primary text-decoration-none"
                   title="Download">
                    {{ $evidence->display_name }}
                </a>
                <span class="text-xs text-secondary ms-2">({{ $evidence->file_size_human }})</span>
                @else
                <a href="{{ $evidence->link_url }}" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="text-sm text-primary text-decoration-none">
                    {{ Str::limit($evidence->link_url, 50) }}
                    <i class="fas fa-external-link-alt text-xs ms-1"></i>
                </a>
                @endif
                @if($evidence->description)
                <p class="text-xs text-secondary mb-0 mt-1">{{ $evidence->description }}</p>
                @endif
                <span class="text-xs text-muted">
                    Oleh {{ $evidence->uploader?->name ?? 'Unknown' }} • {{ $evidence->created_at->format('d M Y H:i') }}
                </span>
            </div>
            @if(!$isLocked && auth()->check() && auth()->user()->hasPermission('evidence.delete'))
            <div class="evidence-actions ms-2">
                <button type="button" class="btn btn-link text-danger btn-sm p-0" 
                        onclick="deleteEvidence({{ $evidence->id }}, '{{ $evidence->display_name }}')"
                        title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-2" id="no-evidence-{{ $kukId }}">
            <span class="text-xs text-secondary">
                <i class="fas fa-info-circle me-1"></i>
                Belum ada evidence
            </span>
        </div>
        @endforelse
    </div>
</div>
