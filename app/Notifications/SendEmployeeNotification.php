<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEmployeeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $employee;
    public $message;

    public function __construct($employee, $message)
    {
        $this->employee = $employee;
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Task Assigned')
                    ->greeting("Hello {$notifiable->name},")
                    ->line($this->message)
                    ->action('View Task', url('/'))
                    ->line('Thank you for your attention!');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->message,
            'user_id' => $this->employee->id,
        ];
    }
}
