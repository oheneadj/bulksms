<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Notifications extends Component
{
    public $showNotifications = false;

    public function getListeners()
    {
        return [
            'notificationReceived' => '$refresh',
            "echo-private:App.Models.User.{$this->userId},.Illuminate.Notifications.Events.BroadcastNotificationCreated" => 'refreshNotifications',
        ];
    }

    public function getUserIdProperty()
    {
        return Auth::id();
    }

    public function refreshNotifications()
    {
        $this->dispatch('$refresh');
    }

    public function toggleNotifications()
    {
        $this->showNotifications = !$this->showNotifications;
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->dispatch('notificationsMarkedAsRead'); // Optional: Generic event
    }

    public function render()
    {
        $user = Auth::user();
        $unreadCount = $user->unreadNotifications()->count();
        // Get last 10 notifications
        $notifications = $user->notifications()->take(10)->get();

        return view('livewire.notifications', [
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }
}
