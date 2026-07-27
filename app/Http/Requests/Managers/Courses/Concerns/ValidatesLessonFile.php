<?php

namespace App\Http\Requests\Managers\Courses\Concerns;

trait ValidatesLessonFile
{
    /**
     * Extensiones aceptadas por cada tipo de lección que sube archivo.
     *
     * Las claves son ids de `course_types`: 2=AUDIO, 3=IMAGEN, 4=ZIP, 5=PDF.
     * Los tipos 1 (VIDEO), 6 (QUIZ) y 7 (TEXTO) no adjuntan archivo — el vídeo
     * se referencia por url y quiz/texto no tienen media.
     */
    private const MIMES_BY_TYPE = [
        2 => 'mp3,wav,ogg,m4a,aac,weba',
        3 => 'jpg,jpeg,png,gif,webp',
        4 => 'zip',
        5 => 'pdf',
    ];

    /**
     * Reglas del adjunto, acotadas al tipo de lección seleccionado.
     *
     * Sin `mimes:` cualquier archivo acabaría en public_path('media'), que es
     * webroot. Los tipos sin colección de media rechazan el adjunto por completo.
     */
    protected function fileRules(): array
    {
        $rules = ['nullable', 'file', 'max:512000'];

        $type = (int) $this->input('type');

        if (isset(self::MIMES_BY_TYPE[$type])) {
            $rules[] = 'mimes:'.self::MIMES_BY_TYPE[$type];
        }

        return $rules;
    }

    /** Mensajes en español del adjunto, con la lista de extensiones del tipo actual. */
    protected function fileMessages(): array
    {
        $type = (int) $this->input('type');

        return [
            'file.file' => 'El archivo adjunto no es válido.',
            'file.max' => 'El archivo no puede superar los 500 MB.',
            'file.mimes' => 'El archivo debe ser de tipo: '.(self::MIMES_BY_TYPE[$type] ?? '').'.',
        ];
    }
}
