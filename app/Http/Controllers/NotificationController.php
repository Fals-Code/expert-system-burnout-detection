<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

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
        if ($notification->user_id === Auth::id()) {
            $notification->is_read = true;
            $notification->save();
        }
        return redirect()->back();
    }

    public function destroy(Notification $notification)
    {
        if ($notification->user_id === Auth::id()) {
            $notification->delete();
        }
        return redirect()->back()->with('success', 'Notifikasi dihapus.');
    }

    public function getUnread()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unreadCount' => 0, 'notifications' => []]);
        }

        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $notificationsData = $unreadNotifications->take(5)->map(function ($notif) {
            // Parse Markdown bold and italics
            $parsedMsg = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $notif->message ?? 'Notifikasi Baru');
            $parsedMsg = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $parsedMsg);

            return [
                'id' => $notif->id,
                'title' => $notif->title,
                'message' => $parsedMsg,
                'time_ago' => $notif->created_at->diffForHumans(),
                'redirect_url' => route('notifications.read_redirect', $notif->id),
            ];
        });

        return response()->json([
            'unreadCount' => $unreadNotifications->count(),
            'notifications' => $notificationsData
        ]);
    }

    public function readAndRedirect(Notification $notification)
    {
        if ($notification->user_id === Auth::id()) {
            $notification->is_read = true;
            $notification->save();
        }
        return redirect()->route('notifications');
    }
}
