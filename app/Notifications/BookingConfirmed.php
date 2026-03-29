<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmed extends Notification
{
    public $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct($booking)
    {
        $this->booking = $booking;
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
        $venueName = $this->booking->turf->name;
        $date = \Carbon\Carbon::parse($this->booking->start_time)->format('d M Y');
        $time = \Carbon\Carbon::parse($this->booking->start_time)->format('H:i');

        return (new MailMessage)
            ->subject('Booking Confirmed - ' . $venueName)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking for ' . $venueName . ' has been confirmed.')
            ->line('Date: ' . $date)
            ->line('Time Slot: ' . $time)
            ->line('Total Amount: ₹' . $this->booking->total_price)
            ->action('View Bookings', url('/bookings'))
            ->line('Thank you for using ' . config('app.name') . '!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
