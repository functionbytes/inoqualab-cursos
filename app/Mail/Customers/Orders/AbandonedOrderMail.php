<?php

namespace App\Mail\Customers\Orders;

use App\Models\Order\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbandonedOrderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Completa tu compra en '.(setting('page_title') ?: 'INOQUALAB'));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.abandoned',
            with: [
                'brand' => setting('page_title') ?: 'INOQUALAB',
                'name' => $this->order->user?->firstname ?? 'estudiante',
                'reference' => $this->order->reference ?? $this->order->slack,
                'total' => (float) $this->order->total_order_amount,
                'payUrl' => route('payments.pay', $this->order->slack),
            ],
        );
    }
}
