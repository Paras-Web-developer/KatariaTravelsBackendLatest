<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CallReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $enquiry;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($enquiry)
    {
        $this->enquiry = $enquiry;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
        //  return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reminder: Call Customer')
            ->line('You have been assigned an enquiry to call the customer.')
            ->line('Customer Name: ' . $this->enquiry->customer_name)
            ->line('Phone Number: ' . $this->enquiry->phone_number)
            ->line('Please make the call at your earliest convenience.')
            ->action('View Enquiry', url('/enquiries/' . $this->enquiry->id))
            ->line('Thank you!');
    }
    public function toDatabase($notifiable)
    {
        try {
            return [
                'enquiry_id' => $this->enquiry->id,
                'customer_name' => $this->enquiry->customer_name,
                'phone_number' => $this->enquiry->phone_number,
                'followed_up_at' => $this->enquiry->followed_up_at,
                'message' => 'You have a follow-up scheduled with a customer.',
            ];
        } catch (\Exception $e) {
            \Log::error('Database notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'enquiry_id' => $this->enquiry->id,
            'customer_name' => $this->enquiry->customer_name,
            'phone_number' => $this->enquiry->phone_number,
        ];
    }
}
