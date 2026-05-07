<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your order #' . $this->order->id . ' has been delivered!');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.orders.delivered');
    }
}
