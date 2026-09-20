<?php

namespace App\Html;

class FormBuilder
{
    public function open(array $options = []): string
    {
        $method = strtoupper($options['method'] ?? 'POST');
        $spoofed = null;
        $htmlMethod = $method;

        if (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
            $spoofed = $method;
            $htmlMethod = 'POST';
        }

        if (isset($options['route'])) {
            $route = (array) $options['route'];
            $name = array_shift($route);
            $action = route($name, $route);
        } elseif (isset($options['url'])) {
            $action = $options['url'];
        } elseif (isset($options['action'])) {
            $action = action($options['action']);
        } else {
            $action = request()->url();
        }

        $attrs = ['method' => $htmlMethod, 'action' => $action];

        foreach (['class', 'id', 'role', 'autocomplete', 'novalidate'] as $key) {
            if (isset($options[$key])) {
                $attrs[$key] = $options[$key];
            }
        }

        if (! empty($options['files']) || ($options['enctype'] ?? '') === 'multipart/form-data') {
            $attrs['enctype'] = 'multipart/form-data';
        }

        $html = '<form'.$this->buildAttributes($attrs).'>';
        $html .= csrf_field();

        if ($spoofed) {
            $html .= method_field($spoofed);
        }

        return $html;
    }

    public function close(): string
    {
        return '</form>';
    }

    public function select(string $name, $list = [], $selected = null, array $attributes = []): string
    {
        $attrs = array_merge(['name' => $name, 'id' => $name], $attributes);
        $html = '<select'.$this->buildAttributes($attrs).'>';

        // $selected llega como array en los <select multiple> (p. ej. cursos/paquetes
        // de un cupón): castear un array a string revienta con "Array to string
        // conversion", así que se normaliza a una lista de strings comparables.
        $selectedValues = match (true) {
            is_array($selected) => array_map('strval', $selected),
            $selected !== null => [(string) $selected],
            default => [],
        };

        foreach ($list as $value => $label) {
            $isSelected = in_array((string) $value, $selectedValues, true) ? ' selected' : '';
            $html .= '<option value="'.e($value).'"'.$isSelected.'>'.e($label).'</option>';
        }

        $html .= '</select>';

        return $html;
    }

    public function text(string $name, $value = null, array $attributes = []): string
    {
        $attrs = array_merge(['type' => 'text', 'name' => $name, 'id' => $name], $attributes);

        if ($value !== null) {
            $attrs['value'] = $value;
        }

        return '<input'.$this->buildAttributes($attrs).'>';
    }

    public function submit(?string $value = null, array $attributes = []): string
    {
        $attrs = array_merge(['type' => 'submit'], $attributes);

        if ($value !== null) {
            $attrs['value'] = $value;
        }

        return '<input'.$this->buildAttributes($attrs).'>';
    }

    private function buildAttributes(array $attrs): string
    {
        $html = '';

        foreach ($attrs as $key => $value) {
            if (is_int($key)) {
                // Boolean attribute: ['disabled'] → disabled
                $html .= ' '.e($value);
            } elseif ($value !== null) {
                $html .= ' '.$key.'="'.e($value).'"';
            }
        }

        return $html;
    }
}
