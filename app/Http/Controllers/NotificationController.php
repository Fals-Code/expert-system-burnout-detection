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
}
