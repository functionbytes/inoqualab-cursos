<?php

namespace App\Mail\Customers\Orders;

use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingMails extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $slack;

    public string $email;

    public string $firstname;

    public string $lastname;

    public string $method;

    public string $total;

    public function __construct($order)
    {
        $this->slack = $order->slack;
        $this->email = $order->user->email;
        $this->firstname = $order->user->firstname ?? '';
        $this->lastname = $order->user->lastname ?? '';
        $this->method = $order->method->title;
        $this->total = '$'.number_format((float) $order->total_order_amount, 0, ',', '.').' COP';
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('orders.pending', [
            'CUSTOMER_FIRSTNAME' => $this->firstname,
            'CUSTOMER_LASTNAME' => $this->lastname,
            'ORDER_NUMBER' => $this->slack,
            'PAYMENT_METHOD' => $this->method,
            'ORDER_TOTAL' => $this->total,
            'ORDERS_URL' => route('customers.orders'),
        ]);

        return $this->to($this->email)
            ->subject($data['subject'])
            ->html($data['html']);
    }
}
