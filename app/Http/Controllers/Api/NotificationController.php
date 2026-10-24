<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get latest 10 notifications
        $notifications = Activity::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'type' => $activity->type,
                    'action' => $activity->action,
                    'time_ago' => $activity->created_at->diffForHumans(),
                    'is_unread' => is_null($activity->read_at),
                ];
            });

        $unreadCount = Activity::where('user_id', $user->id)->whereNull('read_at')->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request)
    {
        $user = $request->user();
        
        // Mark all unread as read
        Activity::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifications marked as read.']);
    }
}