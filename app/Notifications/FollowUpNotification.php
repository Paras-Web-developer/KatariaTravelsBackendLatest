<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowUpNotification extends Notification
{
    use Queueable;

    private $enquiry;

    public function __construct($enquiry)
    {
        $this->enquiry = $enquiry;
    }

    public function via($notifiable)
    {
        return ['mail']; // Use 'database', 'sms', etc., as needed
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Follow-Up Reminder')
            ->line("This is a follow-up for enquiry: {$this->enquiry->title}")
            ->action('View Enquiry', url("/enquiries/{$this->enquiry->id}"));
    }
}
