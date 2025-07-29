<?php

namespace App\Notifications;

use App\Http\Controllers\OrderController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderReceipt extends Notification implements ShouldQueue
{
    use Queueable;
    protected $orderId = 0;

    /**
     * Create a new notification instance.
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
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

        $orderContoller = new OrderController();
        $order = $orderContoller->getOrderDetails($this->orderId);

        return (new MailMessage)
            ->from(env("MAIL_FROM_ADDRESS"))
            ->subject("Order receipt - " . env("APP_NAME"))
            ->view('mail.receipt', ['order' => $order]);
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
