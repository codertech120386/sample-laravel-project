<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;

use App\AppNotification;
use App\User;

class NewNotification extends Notification
{
    use Queueable;

    public $notification;
    public $user;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(AppNotification $notification, User $user)
    {
        $this->notification = $notification;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $notification = $this->notification;
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'description' => $notification->description,
            'button_text' => $notification->button_text,
            'is_read' => $notification->is_read,
            'created_at' => $notification->created_at,
            [
                'notification_for' => $notification->notifiable
            ]
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastOn()
    {
        return new PrivateChannel('Notifications.' . $this->user->id);
    }
}
