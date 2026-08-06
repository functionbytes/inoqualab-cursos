<?php

namespace App\Models;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Lista/segmento de suscriptores para campañas. Las listas con `trigger`
 * distinto de 'manual' son dinámicas: se pueblan solas por eventos de ciclo de
 * vida (curso completado, certificado por vencer) y se vacían cuando el
 * suscriptor compra (convierte).
 */
class NewsletterList extends Model
{
    use HasFactory, HasFinders;

    protected $fillable = [
        'slack', 'name', 'description', 'trigger', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(Newsletter::class, 'newsletter_list_subscriber')
            ->withPivot('added_reason')
            ->withTimestamps();
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(NewsletterCampaign::class, 'newsletter_list_id');
    }

    /** Listas dinámicas (gestionadas por eventos), excluye las manuales. */
    public function scopeDynamic($query)
    {
        return $query->where('trigger', '!=', 'manual');
    }

    /**
     * Busca la lista dinámica activa para un trigger. Deliberadamente NO es
     * un scope Eloquent (scopeX): Builder::callScope() hace
     * `return $scope(...) ?? $this` -- si esto terminara en ->first() como
     * scope y no hubiera ninguna lista para el trigger (->first() = null),
     * Eloquent reemplaza ese null por el propio Builder en silencio.
     * NewsletterList::trigger('x') devolvía entonces un Builder, no null, y
     * $list?->addByEmail(...) explotaba con BadMethodCallException porque el
     * operador null-safe no detecta un Builder -- abortando el comando
     * entero, no solo el paso de lista.
     */
    public static function forTrigger(string $trigger): ?self
    {
        return static::query()->where('trigger', $trigger)->where('is_active', true)->first();
    }

    /**
     * Da de alta un email en la lista. Reutiliza (o crea) el registro de
     * suscriptor para respetar el opt-out y la infraestructura de campañas.
     * Idempotente.
     */
    public function addByEmail(string $email, ?string $name = null, ?string $reason = null): void
    {
        $email = trim(mb_strtolower($email));
        if ($email === '') {
            return;
        }

        $subscriber = Newsletter::firstOrCreate(
            ['email' => $email],
            [
                'slack' => bin2hex(random_bytes(8)),
                'name' => $name,
                'source' => 'lifecycle',
                'is_active' => true,
                'subscribed_at' => now(),
            ]
        );

        $this->subscribers()->syncWithoutDetaching([
            $subscriber->id => ['added_reason' => $reason],
        ]);
    }
}
