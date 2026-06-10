<?php

namespace Tests\Unit\IncomingMail;

use App\Services\IncomingMail\Parsers\RedNacionalParser;
use PHPUnit\Framework\TestCase;

class RedNacionalParserTest extends TestCase
{
    private RedNacionalParser $parser;

    private string $sampleBody = <<<'BODY'
Fecha: 2026/06/09 - Hora: 08:35:55
El usuario maria.duran en la sede Apolo ha oficializado la siguiente orden de atencion:
Documento: 1005038876
Nombre: CALDERON ORTEGA SANDRA MILENA
Ocupacion: DOMIORIENTE S.A.S.
Empresa: DOMIORIENTE S.A.S./BPM (Agrupador: C00)
Examenes a cancelar por la empresa: , BPM VIRTUAL (10 horas - 5 modulos)
Examenes a cancelar por el paciente:
IPS que remite: San Diego (Bucaramanga)
Nombre IPS: INOQUALAB S.A.S.- Capacitaciones BPM
Direccion: CR 22 35 40 INT 224
Ciudad: BUCARAMANGA (SANTANDER)
Telefono: 3152880890
Persona de contacto: NIKOLL CORREDOR
BODY;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new RedNacionalParser;
    }

    public function test_extrae_documento(): void
    {
        $result = $this->parser->parse('', $this->sampleBody);

        $this->assertSame('1005038876', $result['document']);
    }

    public function test_extrae_nombre(): void
    {
        $result = $this->parser->parse('', $this->sampleBody);

        $this->assertSame('CALDERON ORTEGA SANDRA MILENA', $result['name']);
    }

    public function test_extrae_codigo_agrupador(): void
    {
        $result = $this->parser->parse('', $this->sampleBody);

        $this->assertSame('C00', $result['enterprise_code']);
    }

    public function test_extrae_razon_social_empresa(): void
    {
        $result = $this->parser->parse('', $this->sampleBody);

        $this->assertSame('DOMIORIENTE S.A.S.', $result['enterprise_name']);
    }

    public function test_extrae_cursos_limpios(): void
    {
        $result = $this->parser->parse('', $this->sampleBody);

        $this->assertSame(['BPM VIRTUAL'], $result['courses']);
    }

    public function test_maneja_coma_inicial_en_cursos(): void
    {
        $result = $this->parser->parse('', $this->sampleBody);

        // The leading empty token before the comma must not appear in courses
        foreach ($result['courses'] as $course) {
            $this->assertNotSame('', $course);
        }
        $this->assertCount(1, $result['courses']);
    }

    public function test_multiples_cursos(): void
    {
        $body = <<<'BODY'
Documento: 12345
Nombre: PEREZ GOMEZ JUAN CARLOS
Empresa: EMPRESA TEST S.A./PLAN (Agrupador: T01)
Examenes a cancelar por la empresa: , CURSO A (2 horas - 1 modulos), CURSO B (5 horas - 3 modulos)
BODY;

        $result = $this->parser->parse('', $body);

        $this->assertSame(['CURSO A', 'CURSO B'], $result['courses']);
    }

    public function test_documento_solo_digitos(): void
    {
        // Parser strips all non-digit characters from the Documento field
        $body = "Documento: 1005038876\nNombre: PRUEBA TEST\nEmpresa: EMPRESA/PLAN\n";

        $result = $this->parser->parse('', $body);

        $this->assertMatchesRegularExpression('/^\d+$/', $result['document']);
    }

    public function test_split_nombre_firstname_lastname(): void
    {
        // Name "CALDERON ORTEGA SANDRA MILENA":
        // Heuristic: first 2 tokens = lastnames, remaining tokens = firstnames
        $result = $this->parser->parse('', $this->sampleBody);

        $this->assertSame('SANDRA MILENA', $result['firstname']);
        $this->assertSame('CALDERON ORTEGA', $result['lastname']);
    }

    public function test_body_sin_linea_de_cursos_devuelve_array_vacio(): void
    {
        $body = <<<'BODY'
Documento: 99999999
Nombre: LOPEZ MARTINEZ PEDRO JOSE
Empresa: EMPRESA ABC S.A./PLAN (Agrupador: Z99)
BODY;

        $result = $this->parser->parse('', $body);

        $this->assertSame([], $result['courses']);
    }

    public function test_cursos_vacios_cuando_valor_es_cadena_vacia(): void
    {
        $body = "Documento: 11111\nNombre: TEST\nEmpresa: EMP/PLAN\nExamenes a cancelar por la empresa: \n";

        $result = $this->parser->parse('', $body);

        $this->assertSame([], $result['courses']);
    }

    public function test_name_null_cuando_no_hay_linea_nombre(): void
    {
        $body = "Documento: 99999\nEmpresa: EMP/PLAN\n";

        $result = $this->parser->parse('', $body);

        $this->assertNull($result['name']);
        $this->assertNull($result['firstname']);
        $this->assertNull($result['lastname']);
    }

    public function test_split_nombre_dos_tokens(): void
    {
        // 2-part name: second token = firstname, first token = lastname
        $body = "Nombre: PEREZ JUAN\nDocumento: 1\nEmpresa: E/P\n";

        $result = $this->parser->parse('', $body);

        $this->assertSame('JUAN', $result['firstname']);
        $this->assertSame('PEREZ', $result['lastname']);
    }

    public function test_split_nombre_un_token(): void
    {
        $body = "Nombre: SOLITARIO\nDocumento: 1\nEmpresa: E/P\n";

        $result = $this->parser->parse('', $body);

        $this->assertSame('SOLITARIO', $result['firstname']);
        $this->assertNull($result['lastname']);
    }

    public function test_empresa_sin_agrupador_devuelve_code_null(): void
    {
        $body = "Documento: 1\nNombre: TEST\nEmpresa: EMPRESA SIN CODIGO\n";

        $result = $this->parser->parse('', $body);

        $this->assertNull($result['enterprise_code']);
        $this->assertSame('EMPRESA SIN CODIGO', $result['enterprise_name']);
    }
}
