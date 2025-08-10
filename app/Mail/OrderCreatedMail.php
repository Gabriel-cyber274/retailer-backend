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
        $subject = $this->recipientType === 'admin'
            ? "New Order #{$this->order->id} placed"
            : "Order confirmation — #{$this->order->id}";

        return $this->subject($subject)
            ->view('emails.order-created')
            ->with([
                'order' => $this->order,
                'recipientType' => $this->recipientType,
            ]);
    }
}
