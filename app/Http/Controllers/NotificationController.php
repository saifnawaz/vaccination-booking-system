<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The signed-in user's in-app notifications. Every query goes through
 * $request->user()->notifications(), so nobody can read or change someone else's.
 */
class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate(['show' => ['nullable', 'in:all,unread']])['show'] ?? 'all';

        $notifications = $request->user()->notifications()
            ->when($filter === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->paginate(20)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark one as read and go to what it's about.
     */
    public function open(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        // Only follow links within this app.
        return redirect(is_string($url) && str_starts_with($url, '/') && ! str_starts_with($url, '//') ? $url : route('notifications.index'));
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($id)->markAsRead();

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
