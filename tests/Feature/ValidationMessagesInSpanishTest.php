<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * El proyecto corre con APP_LOCALE=es pero no existía la carpeta `lang/`, así
 * que Laravel caía al fallback en inglés: los ~29 controllers con validación
 * inline y todo Form Request sin `messages()` mostraban "The title field is
 * required" a un usuario hispanohablante.
 *
 * Estos tests fijan que las traducciones siguen en su sitio.
 */
class ValidationMessagesInSpanishTest extends TestCase
{
    public function test_locale_is_spanish(): void
    {
        $this->assertSame('es', app()->getLocale());
    }

    public function test_common_rules_are_translated(): void
    {
        $v = Validator::make(
            ['title' => '', 'email' => 'no-es-correo', 'price' => 'abc'],
            [
                'title' => ['required'],
                'email' => ['required', 'email'],
                'price' => ['required', 'numeric'],
            ]
        );

        $errors = $v->errors();

        $this->assertSame('El campo título es obligatorio.', $errors->first('title'));
        $this->assertStringContainsString('correo electrónico', $errors->first('email'));
        $this->assertStringContainsString('debe ser un número', $errors->first('price'));
    }

    public function test_no_message_falls_back_to_english(): void
    {
        $v = Validator::make(
            ['a' => 'x', 'b' => str_repeat('x', 300), 'c' => 'no'],
            ['a' => ['integer'], 'b' => ['max:10'], 'c' => ['boolean']]
        );

        foreach ($v->errors()->all() as $message) {
            $this->assertStringNotContainsString('The ', $message, "Mensaje sin traducir: {$message}");
            $this->assertStringNotContainsString(' field ', $message, "Mensaje sin traducir: {$message}");
            $this->assertStringNotContainsString('must be', $message, "Mensaje sin traducir: {$message}");
        }
    }

    public function test_attribute_names_are_translated(): void
    {
        // Los nombres compartidos viven en lang/es/validation.php → attributes,
        // para no repetirlos en el attributes() de cada Form Request.
        $v = Validator::make([], ['source_path' => ['required'], 'available' => ['required']]);

        $this->assertStringContainsString('ruta de origen', $v->errors()->first('source_path'));
        $this->assertStringContainsString('estado', $v->errors()->first('available'));
    }

    public function test_auth_and_password_messages_are_translated(): void
    {
        $this->assertSame(
            'Las credenciales no coinciden con nuestros registros.',
            __('auth.failed')
        );
        $this->assertStringContainsString('contraseña', __('passwords.reset'));
    }
}
