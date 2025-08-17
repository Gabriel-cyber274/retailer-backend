<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $recipientType;

    public function __construct(Order $order, string $recipientType = 'user')
    {
        $this->order = $order;
        $this->recipientType = $recipientType;
    }

    public function build()
    {
        switch ($this->recipientType) {
            case 'admin':
                $subject = "📦 New Order #{$this->order->id} Received";
                break;
            case 'customer':
                $subject = "🧾 Order Receipt — #{$this->order->id}";
                break;
            case 'customer_user':
                $subject = "👥 Your Customer Placed an Order — #{$this->order->id}";
                break;
            default:
                $subject = "✅ Your Order Confirmation — #{$this->order->id}";
                break;
        }

        return $this->subject($subject)
            ->view('emails.order-created')
            ->with([
                'order' => $this->order,
                'recipientType' => $this->recipientType,
            ]);
    }
}
