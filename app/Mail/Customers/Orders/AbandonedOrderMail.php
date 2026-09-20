<?php

namespace App\Mail\Customers\Orders;

use App\Models\Order\Order;
use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AbandonedOrderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $email;

    public string $firstname;

    public string $reference;

    public string $total;

    public string $payUrl;

    public function __construct(Order $order)
    {
        $this->email = $order->user->email;
        $this->firstname = $order->user?->firstname ?? 'estudiante';
        $this->reference = $order->reference ?? $order->slack;
        $this->total = '$'.number_format((float) $order->total_order_amount, 0, ',', '.').' COP';
        $this->payUrl = route('payments.pay', $order->slack);
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('orders.abandoned', [
            'CUSTOMER_FIRSTNAME' => $this->firstname,
            'ORDER_NUMBER' => $this->reference,
            'ORDER_TOTAL' => $this->total,
            'PAY_URL' => $this->payUrl,
        ]);

        return $this->to($this->email)
            ->subject($data['subject'])
            ->html($data['html']);
    }
}
