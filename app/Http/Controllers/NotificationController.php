<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        abort_unless((int) $notification->user_id === (int) Auth::id(), 403);

        $notification->is_read = true;
        $notification->save();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back();
    }

    public function destroy(Notification $notification)
    {
        abort_unless((int) $notification->user_id === (int) Auth::id(), 403);

        $notification->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notifikasi dihapus.');
    }

    public function getUnread()
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['unreadCount' => 0, 'notifications' => []]);
        }

        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $notificationsData = $unreadNotifications->take(5)->map(function ($notif) {
            return [
                'id' => $notif->id,
                'title' => $notif->title,
                'message' => $notif->message ?? 'Notifikasi Baru',
                'time_ago' => $notif->created_at->diffForHumans(),
                'redirect_url' => route('notifications.read_redirect', $notif->id),
            ];
        });

        return response()->json([
            'unreadCount' => $unreadNotifications->count(),
            'notifications' => $notificationsData,
        ]);
    }

    public function readAndRedirect(Notification $notification)
    {
        abort_unless((int) $notification->user_id === (int) Auth::id(), 403);

        $notification->is_read = true;
        $notification->save();

        return redirect()->route('notifications');
    }
}
