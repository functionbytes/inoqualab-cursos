<?php

namespace App\Models\Mailer;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailerTemplate extends Model
{
    use HasFactory, HasUid;

    protected $table = 'mailer_templates';

    protected $fillable = [
        'uid', 'key', 'name', 'layout_id', 'subject', 'preheader',
        'content', 'is_enabled', 'is_protected', 'variables', 'module', 'description',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_enabled' => 'boolean',
            'is_protected' => 'boolean',
        ];
    }

    public function layout(): BelongsTo
    {
        return $this->belongsTo(MailerLayout::class, 'layout_id', 'id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(MailerTemplateVersion::class, 'mailer_template_id')->latest();
    }

    public function scopeModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeSearch($query, $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', '%'.$keyword.'%')
                ->orWhere('key', 'like', '%'.$keyword.'%')
                ->orWhere('description', 'like', '%'.$keyword.'%');
        });
    }

    public function scopeInModules($query, array $modules)
    {
        return $query->whereIn('module', $modules);
    }

    public static function defaultVariables(string $module = 'core'): array
    {
        $variables = MailerVariable::query()
            ->where('is_enabled', true)
            ->where(function ($query) use ($module) {
                $query->where('module', $module)->orWhere('module', 'core');
            })
            ->orderBy('category')
            ->orderBy('key')
            ->get();

        $result = [];
        foreach ($variables as $variable) {
            $result[] = [
                'name' => $variable->key,
                'required' => $variable->is_system,
                'description' => $variable->description,
                'category' => $variable->category,
            ];
        }

        return $result;
    }

    public function getAvailableVariables(): array
    {
        if ($this->variables && is_array($this->variables)) {
            return $this->variables;
        }

        return self::defaultVariables($this->module);
    }

    public function isComplete(): bool
    {
        foreach ($this->getAvailableVariables() as $variable) {
            if ($variable['required'] && ! str_contains((string) $this->content, '{'.$variable['name'].'}')) {
                return false;
            }
        }

        return true;
    }

    public function getMissingVariables(): array
    {
        $missing = [];
        foreach ($this->getAvailableVariables() as $variable) {
            if ($variable['required'] && ! str_contains((string) $this->content, '{'.$variable['name'].'}')) {
                $missing[] = $variable;
            }
        }

        return $missing;
    }

    public static function getStructureForModule(string $module = 'core'): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hola {USER_NAME}</h1>
        <p>Escribe el contenido de tu plantilla aquí...</p>
    </div>
</body>
</html>
HTML;
    }
}
