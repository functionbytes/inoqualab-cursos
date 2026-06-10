<?php

namespace App\Mail\Distributors\Invoices;

use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportsMails extends Mailable
{
    use Queueable, SerializesModels;

    public string $email;

    public string $reference;

    public string $distributor;

    public string $nit;

    public string $from_at;

    public string $to_at;

    public function __construct($invoice, $accounting)
    {
        $this->email = $accounting->email;
        $this->reference = $invoice->reference;
        $this->distributor = $invoice->distributor->title;
        $this->nit = $invoice->distributor->nit ?? '';
        $this->from_at = $invoice->from_at;
        $this->to_at = $invoice->to_at;
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('invoices.report', [
            'REFERENCE' => $this->reference,
            'DISTRIBUTOR_NAME' => $this->distributor,
            'NIT' => $this->nit,
            'PERIOD_FROM' => $this->from_at,
            'PERIOD_TO' => $this->to_at,
        ]);

        return $this->to($this->email)
            ->subject($data['subject'])
            ->html($data['html']);
    }
}
