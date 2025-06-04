<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SocialMediaEnquiryNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $SocialMediaEnquiry;

    public function __construct($SocialMediaEnquiry)
    {
        $this->SocialMediaEnquiry = $SocialMediaEnquiry;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */

    public function toMail(object $notifiable): MailMessage
    {

        $mail = (new MailMessage);
        $mail->subject('Booking Enquiry Created Success');

        $mail->view('emails.social-media-enquiry', ['SocialMediaEnquiry' => $this->SocialMediaEnquiry]);
        return $mail;
    }
}
