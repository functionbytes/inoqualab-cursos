<?php

namespace Database\Seeders;

use App\Models\NewsletterList;
use Illuminate\Database\Seeder;

class NewsletterListSeeder extends Seeder
{
    public function run(): void
    {
        $lists = [
            [
                'slack' => 'course-completed',
                'name' => 'Completaron un curso',
                'description' => 'Alumnos que terminaron un curso. Se agregan solos y se retiran al comprar otro curso.',
                'trigger' => 'course_completed',
            ],
            [
                'slack' => 'certificate-expiring',
                'name' => 'Certificado por vencer',
                'description' => 'Alumnos cuyo certificado está por vencer. Se agregan solos y se retiran al renovar/comprar.',
                'trigger' => 'certificate_expiring',
            ],
            [
                'slack' => 'course-access-expiring',
                'name' => 'Acceso al curso por vencer',
                'description' => 'Alumnos cuyo acceso a un curso vence pronto sin haberlo completado. Se agregan solos y se retiran al renovar/comprar.',
                'trigger' => 'course_access_expiring',
            ],
        ];

        foreach ($lists as $list) {
            NewsletterList::updateOrCreate(
                ['trigger' => $list['trigger']],
                $list + ['is_active' => true]
            );
        }
    }
}
