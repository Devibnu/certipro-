<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\InAppNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Get unread notification count (for badge)
     */
    public function getUnreadCount(): JsonResponse
    {
        $count = InAppNotification::where('user_id', auth()->id())
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications (for dropdown)
     */
    public function getRecent(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 5);

        $notifications = InAppNotification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'icon' => $notification->icon ?? 'fas fa-bell',
                    'icon_color' => $notification->icon_color,
                    'url' => $notification->getUrl(),
                    'is_unread' => $notification->isUnread(),
                    'relative_time' => $notification->getRelativeTime(),
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        $unreadCount = InAppNotification::where('user_id', auth()->id())
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(InAppNotification $notification): JsonResponse
    {
        // Ensure user owns this notification
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        $unreadCount = InAppNotification::where('user_id', auth()->id())
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $updated = InAppNotification::markAllAsReadForUser(auth()->id());

        return response()->json([
            'success' => true,
            'marked_count' => $updated,
            'unread_count' => 0,
        ]);
    }

    /**
     * Show all notifications page
     */
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'all'); // all, unread, read

        $query = InAppNotification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc');

        if ($filter === 'unread') {
            $query->unread();
        } elseif ($filter === 'read') {
            $query->read();
        }

        $notifications = $query->paginate(20);

        $unreadCount = InAppNotification::where('user_id', auth()->id())
            ->unread()
            ->count();

        return view('adminui.notifications.index', compact('notifications', 'filter', 'unreadCount'));
    }

    /**
     * Delete notification
     */
    public function destroy(InAppNotification $notification): JsonResponse
    {
        // Ensure user owns this notification
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }
}
