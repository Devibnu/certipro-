@props([
    'canPerform' => true,
    'reason' => null,
    'action',
    'route',
    'method' => 'POST',
    'confirmMessage' => null,
    'icon' => null,
    'class' => '',
])

@php
    $baseClass = 'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest transition ease-in-out duration-150';
    
    $enabledClass = match(true) {
        str_contains($class, 'danger') => 'bg-red-600 hover:bg-red-700 text-white',
        str_contains($class, 'success') => 'bg-green-600 hover:bg-green-700 text-white',
        str_contains($class, 'warning') => 'bg-yellow-500 hover:bg-yellow-600 text-white',
        str_contains($class, 'secondary') => 'bg-gray-500 hover:bg-gray-600 text-white',
        default => 'bg-blue-600 hover:bg-blue-700 text-white',
    };
    
    $disabledClass = 'bg-gray-300 text-gray-500 cursor-not-allowed opacity-60';
    
    $buttonClass = $baseClass . ' ' . ($canPerform ? $enabledClass : $disabledClass) . ' ' . $class;
@endphp

<div class="relative inline-block group">
    @if($canPerform)
        <form action="{{ $route }}" method="POST" class="inline" onsubmit="return {{ $confirmMessage ? "confirm('" . addslashes($confirmMessage) . "')" : 'true' }}">
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif
            
            <button 
                type="submit"
                class="{{ $buttonClass }}"
                {{ $attributes }}
            >
                @if($icon)
                    <span class="mr-2">{{ $icon }}</span>
                @endif
                {{ $slot }}
            </button>
        </form>
    @else
        {{-- Disabled button with tooltip --}}
        <button 
            type="button"
            disabled
            class="{{ $buttonClass }}"
            title="{{ $reason }}"
            {{ $attributes }}
        >
            @if($icon)
                <span class="mr-2">{{ $icon }}</span>
            @endif
            {{ $slot }}
        </button>
        
        {{-- Tooltip on hover --}}
        @if($reason)
            <div class="absolute z-50 invisible group-hover:visible bg-gray-900 text-white text-xs rounded-lg py-3 px-4 bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-72 shadow-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $reason }}</span>
                </div>
                {{-- Tooltip arrow --}}
                <svg class="absolute text-gray-900 h-2 w-full left-0 top-full" viewBox="0 0 255 255">
                    <polygon class="fill-current" points="0,0 127.5,127.5 255,0"/>
                </svg>
            </div>
        @endif
    @endif
</div>
