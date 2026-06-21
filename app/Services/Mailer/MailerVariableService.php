<?php

namespace App\Services\Mailer;

use App\Models\Mailer\MailerVariable;

class MailerVariableService
{
    public static function getVariablesByModule(string $module): array
    {
        return MailerVariable::enabled()
            ->where(function ($q) use ($module) {
                $q->where('module', $module)->orWhere('module', 'core');
            })
            ->orderBy('category')
            ->orderBy('key')
            ->get()
            ->toArray();
    }

    public static function getAllVariables(): array
    {
        return MailerVariable::enabled()
            ->orderBy('module')
            ->orderBy('category')
            ->orderBy('key')
            ->get()
            ->toArray();
    }

    public static function getVariable(string $key): ?MailerVariable
    {
        return MailerVariable::where('key', $key)->first();
    }

    public static function variableExists(string $key): bool
    {
        return MailerVariable::where('key', $key)->exists();
    }

    public static function getAllVariableKeys(): array
    {
        return MailerVariable::enabled()->pluck('key')->toArray();
    }

    public static function getVariablesGroupedByCategory(string $module): array
    {
        $variables = MailerVariable::enabled()
            ->where(function ($q) use ($module) {
                $q->where('module', $module)->orWhere('module', 'core');
            })
            ->orderBy('category')
            ->orderBy('key')
            ->get();

        $grouped = [];
        foreach ($variables as $var) {
            $grouped[$var->category][] = $var;
        }

        return $grouped;
    }

    public static function getCategoryLabel(string $category): string
    {
        $labels = config('mailer-module.category_labels', []);

        return $labels[$category] ?? ucfirst($category);
    }

    public static function getGroupedForModule(string $module): array
    {
        $grouped = self::getVariablesGroupedByCategory($module);
        $result = [];

        foreach ($grouped as $category => $variables) {
            $result[] = [
                'label' => self::getCategoryLabel($category),
                'category' => $category,
                'variables' => array_map(fn ($v) => [
                    'key' => $v->key,
                    'name' => $v->name,
                    'description' => $v->description,
                    'example_value' => $v->example_value,
                    'is_system' => $v->is_system,
                ], $variables),
            ];
        }

        return $result;
    }
}
