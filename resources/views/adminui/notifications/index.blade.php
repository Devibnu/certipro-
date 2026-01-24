@extends('adminui.layouts.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Semua Notifikasi</h6>
                    <div>
                        @if($unreadCount > 0)
                        <form action="{{ route('adminui.notifications.mark-all-read') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-check-double me-1"></i>
                                Tandai Semua Dibaca ({{ $unreadCount }})
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                <div class="card-header pb-0 pt-3">
                    <div class="btn-group" role="group">
                        <a href="{{ route('adminui.notifications.index', ['filter' => 'all']) }}" 
                           class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                            Semua
                        </a>
                        <a href="{{ route('adminui.notifications.index', ['filter' => 'unread']) }}" 
                           class="btn btn-sm {{ $filter === 'unread' ? 'btn-primary' : 'btn-outline-primary' }}">
                            Belum Dibaca @if($unreadCount > 0)({{ $unreadCount }})@endif
                        </a>
                        <a href="{{ route('adminui.notifications.index', ['filter' => 'read']) }}" 
                           class="btn btn-sm {{ $filter === 'read' ? 'btn-primary' : 'btn-outline-primary' }}">
                            Sudah Dibaca
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if($notifications->count() > 0)
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <tbody>
                                @foreach($notifications as $notification)
                                <tr class="{{ $notification->isUnread() ? 'bg-light' : '' }}">
                                    <td class="text-center" style="width: 60px;">
                                        <div class="icon icon-shape icon-sm bg-gradient-{{ $notification->icon_color }} shadow text-center border-radius-md d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="{{ $notification->icon ?? 'fas fa-bell' }} text-white"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm {{ $notification->isUnread() ? 'font-weight-bold' : '' }}">
                                                {{ $notification->title }}
                                            </h6>
                                            @if($notification->message)
                                            <p class="text-xs text-secondary mb-0">
                                                {{ $notification->message }}
                                            </p>
                                            @endif
                                            <p class="text-xs text-secondary mb-0">
                                                <i class="fa fa-clock me-1"></i>
                                                {{ $notification->getRelativeTime() }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="text-end" style="width: 200px;">
                                        <div class="btn-group" role="group">
                                            @if($notification->getUrl())
                                            <a href="{{ $notification->getUrl() }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               onclick="markAsReadAndRedirect(event, {{ $notification->id }}, '{{ $notification->getUrl() }}')">
                                                <i class="fas fa-external-link-alt"></i>
                                                Lihat
                                            </a>
                                            @endif
                                            
                                            @if($notification->isUnread())
                                            <form action="{{ route('adminui.notifications.mark-as-read', $notification) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Tandai Sudah Dibaca">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            @endif
                                            
                                            <form action="{{ route('adminui.notifications.destroy', $notification) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus notifikasi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="px-4 pt-3">
                        {{ $notifications->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash text-secondary" style="font-size: 48px;"></i>
                        <p class="text-secondary mt-3 mb-0">
                            @if($filter === 'unread')
                                Tidak ada notifikasi yang belum dibaca
                            @elseif($filter === 'read')
                                Tidak ada notifikasi yang sudah dibaca
                            @else
                                Tidak ada notifikasi
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function markAsReadAndRedirect(event, notificationId, url) {
    event.preventDefault();
    
    fetch(`/adminui/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(() => {
        window.location.href = url;
    })
    .catch(() => {
        window.location.href = url;
    });
}
</script>
@endpush
@endsection
