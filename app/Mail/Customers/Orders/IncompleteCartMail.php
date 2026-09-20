<?php

namespace App\Mail\Customers\Orders;

use App\Models\CartAbandonment;
use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IncompleteCartMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $email;

    public string $firstname;

    public string $itemsHtml;

    public string $total;

    public string $resumeUrl;

    public function __construct(CartAbandonment $abandonment)
    {
        $this->email = $abandonment->email;
        $this->firstname = $abandonment->user?->firstname ?: 'Hola';
        $this->itemsHtml = $this->buildItemsHtml($abandonment->items);
        $this->total = '$'.number_format((float) $abandonment->total, 0, ',', '.').' COP';
        $this->resumeUrl = route('cart.restore', $abandonment->slack);
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('orders.incomplete_cart', [
            'CUSTOMER_FIRSTNAME' => $this->firstname,
            'CART_ITEMS' => $this->itemsHtml,
            'CART_TOTAL' => $this->total,
            'RESUME_URL' => $this->resumeUrl,
        ]);

        return $this->to($this->email)
            ->subject($data['subject'])
            ->html($data['html']);
    }

    /**
     * Filas de ítems: título a la izquierda y precio a la derecha, con línea
     * divisoria en cada fila (incluida la última -- separa la lista del botón).
     * El precio va en gris-azulado, no en el azul de marca: en este diseño el
     * protagonismo del monto ya lo tiene el bloque TOTAL de arriba (ver
     * orders.incomplete_cart); repetirlo en azul aquí competiría por la mirada.
     */
    private function buildItemsHtml(array $lines): string
    {
        $rows = '';
        $count = count($lines);
        $i = 0;
        foreach ($lines as $line) {
            $i++;
            $title = e($line['title'] ?? '');
            $amount = '$'.number_format((float) ($line['amount'] ?? 0), 0, ',', '.');
            $padTop = $i === 1 ? '0' : '12px';
            $rows .= <<<HTML
<tr>
<td width="68%" valign="top" style="padding:{$padTop} 12px 12px 0;border-bottom:1px solid #eef2f6;font-family:'Figtree',Arial,Helvetica,sans-serif;font-size:14px;color:#081A28;line-height:1.45;">{$title}</td>
<td align="right" valign="top" style="padding:{$padTop} 0 12px;border-bottom:1px solid #eef2f6;font-family:'Figtree',Arial,Helvetica,sans-serif;font-size:14px;font-weight:600;color:#5A7093;white-space:nowrap;">{$amount}</td>
</tr>
HTML;
        }

        return <<<HTML
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">{$rows}</table>
HTML;
    }
}
