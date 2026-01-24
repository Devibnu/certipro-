@props([
    'status',
    'showIcon' => true,
    'showLabel' => true,
    'size' => 'md', // sm, md, lg
])

@php
    use App\Enums\PendaftaranStatus;
    use App\Enums\AsesmenStatus;
    use App\Enums\KeputusanStatus;
    use App\Enums\SertifikatStatus;
    use App\Enums\PraPendaftaranStatus;
    
    // Try to parse status as enum
    $statusEnum = null;
    $badgeClass = '';
    $icon = '';
    $label = '';
    
    try {
        if ($status instanceof PendaftaranStatus) {
            $statusEnum = $status;
        } elseif ($status instanceof AsesmenStatus) {
            $statusEnum = $status;
        } elseif ($status instanceof KeputusanStatus) {
            $statusEnum = $status;
        } elseif ($status instanceof SertifikatStatus) {
            $statusEnum = $status;
        } elseif ($status instanceof PraPendaftaranStatus) {
            $statusEnum = $status;
        } elseif (is_string($status)) {
            // Try each enum
            try { $statusEnum = PendaftaranStatus::from($status); } catch (\Exception $e) {}
            if (!$statusEnum) try { $statusEnum = AsesmenStatus::from($status); } catch (\Exception $e) {}
            if (!$statusEnum) try { $statusEnum = KeputusanStatus::from($status); } catch (\Exception $e) {}
            if (!$statusEnum) try { $statusEnum = SertifikatStatus::from($status); } catch (\Exception $e) {}
            if (!$statusEnum) try { $statusEnum = PraPendaftaranStatus::from($status); } catch (\Exception $e) {}
        }
        
        if ($statusEnum) {
            $badgeClass = $statusEnum->badge();
            $icon = $statusEnum->icon();
            $label = $statusEnum->label();
        } else {
            $badgeClass = 'bg-gray-100 text-gray-800';
            $icon = '❓';
            $label = ucfirst(str_replace('_', ' ', $status));
        }
    } catch (\Exception $e) {
        $badgeClass = 'bg-gray-100 text-gray-800';
        $icon = '❓';
        $label = 'Unknown';
    }
    
    $sizeClass = match($size) {
        'sm' => 'text-xs px-2 py-1',
        'lg' => 'text-base px-4 py-2',
        default => 'text-sm px-3 py-1.5',
    };
@endphp

<span class="inline-flex items-center rounded-full font-medium {{ $badgeClass }} {{ $sizeClass }}" {{ $attributes }}>
    @if($showIcon)
        <span class="mr-1">{{ $icon }}</span>
    @endif
    
    @if($showLabel)
        {{ $label }}
    @endif
</span>
