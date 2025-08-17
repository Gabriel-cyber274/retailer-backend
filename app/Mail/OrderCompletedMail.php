<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $recipientType;

    public function __construct(Order $order, $recipientType)
    {
        $this->order = $order;
        $this->recipientType = $recipientType;
    }

    public function build()
    {
        $subject = 'Order #' . $this->order->id . ' Completed';

        return $this->subject($subject)
            ->view('emails.order_completed')
            ->with([
                'order' => $this->order,
                'recipientType' => $this->recipientType,
            ]);
    }
}
