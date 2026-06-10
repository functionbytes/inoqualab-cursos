<?php

namespace App\Mail\Customers\Orders;

use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApprovedMails extends Mailable
{
    use Queueable, SerializesModels;

    public string $slack;

    public string $email;

    public string $firstname;

    public string $lastname;

    public string $payment;

    public string $method;

    public string $total;

    public function __construct($order)
    {
        $this->slack = $order->slack;
        $this->email = $order->user->email;
        $this->firstname = $order->user->firstname ?? '';
        $this->lastname = $order->user->lastname ?? '';
        $this->payment = humanize_date($order->payment_at);
        $this->method = $order->method->title;
        $this->total = '$'.number_format((float) $order->total_order_amount, 0, ',', '.').' COP';
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('orders.approved', [
            'CUSTOMER_FIRSTNAME' => $this->firstname,
            'CUSTOMER_LASTNAME' => $this->lastname,
            'ORDER_NUMBER' => $this->slack,
            'PAYMENT_METHOD' => $this->method,
            'PAYMENT_DATE' => $this->payment,
            'ORDER_TOTAL' => $this->total,
            'COURSES_URL' => route('customers.courses'),
        ]);

        return $this->to($this->email)
            ->subject($data['subject'])
            ->html($data['html']);
    }
}
