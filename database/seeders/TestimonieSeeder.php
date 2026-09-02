<?php

namespace Database\Seeders;

use App\Models\Testimonie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Testimonios de ejemplo para el carrusel del home, en el mismo espíritu que
 * inoqualab.test/es (nombre de sector + resultado destacado) pero adaptados
 * al negocio real de esta plataforma: capacitación/e-learning en BPM y
 * manipulación de alimentos, no análisis directo de laboratorio.
 */
class TestimonieSeeder extends Seeder
{
    private const TESTIMONIOS = [
        [
            'firstname' => 'María Fernanda',
            'lastname' => 'Gómez',
            'role' => 'Estudiante certificada',
            'icon' => 'fas fa-user-graduate',
            'benefit' => 'Certificada en menos de un mes',
            'description' => 'El curso de manipulación de alimentos me permitió certificarme rápido y sin complicaciones. El contenido es claro, práctico y aplicable desde el primer día en mi trabajo.',
        ],
        [
            'firstname' => 'Restaurante',
            'lastname' => 'El Fogón',
            'role' => 'Cliente corporativo · Sector gastronómico',
            'icon' => 'fas fa-utensils',
            'benefit' => 'Todo el personal certificado',
            'description' => 'Capacitamos a todo nuestro equipo de cocina con estos cursos. La plataforma es fácil de usar y el certificado nos ayudó a cumplir con la normativa sanitaria sin contratiempos.',
        ],
        [
            'firstname' => 'Lácteos',
            'lastname' => 'La Sabana',
            'role' => 'Cliente corporativo · Sector lácteo',
            'icon' => 'fas fa-industry',
            'benefit' => 'Mayor cumplimiento en auditorías',
            'description' => 'Los módulos de buenas prácticas de manufactura fortalecieron los conocimientos de nuestro personal de planta. Notamos una mejora real en el cumplimiento de nuestros protocolos de calidad.',
        ],
        [
            'firstname' => 'Laboratorio',
            'lastname' => 'ClinLab',
            'role' => 'Cliente corporativo · Laboratorio microbiológico',
            'icon' => 'fas fa-flask',
            'benefit' => 'Personal técnico más preparado',
            'description' => 'La formación en buenas prácticas de manufactura fue clave para preparar a nuestro equipo técnico. Muy recomendado para el sector de análisis y laboratorio.',
        ],
        [
            'firstname' => 'Carlos Andrés',
            'lastname' => 'Rueda',
            'role' => 'Estudiante certificado',
            'icon' => 'fas fa-user-graduate',
            'benefit' => 'Aprendizaje 100% práctico',
            'description' => 'Excelente experiencia, los contenidos son fáciles de entender y el certificado me abrió las puertas para un nuevo empleo en el sector de alimentos.',
        ],
        [
            'firstname' => 'Panificadora',
            'lastname' => 'San José',
            'role' => 'Cliente corporativo · Sector panificado',
            'icon' => 'fas fa-store',
            'benefit' => 'Certificaciones siempre al día',
            'description' => 'Gracias a estos cursos mantenemos actualizadas las certificaciones de manipulación de alimentos de todo el personal, sin tener que desplazarnos a capacitaciones presenciales.',
        ],
    ];

    public function run(): void
    {
        foreach (self::TESTIMONIOS as $position => $testimonio) {
            Testimonie::updateOrCreate(
                ['slack' => Str::slug($testimonio['firstname'].'-'.$testimonio['lastname'])],
                [
                    'firstname' => $testimonio['firstname'],
                    'lastname' => $testimonio['lastname'],
                    'role' => $testimonio['role'],
                    'icon' => $testimonio['icon'],
                    'rating' => 5,
                    'benefit' => $testimonio['benefit'],
                    'description' => $testimonio['description'],
                    'position' => $position,
                    'available' => 1,
                ]
            );

            $this->command?->info("  {$testimonio['firstname']} {$testimonio['lastname']}");
        }
    }
}
