<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class DispatchNumberUpdatedMail extends Mailable
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
        $subject = 'Dispatch Number Updated for Order #' . $this->order->id;

        return $this->subject($subject)
            ->view('emails.dispatch_number_updated')
            ->with([
                'order' => $this->order,
                'recipientType' => $this->recipientType,
            ]);
    }
}
