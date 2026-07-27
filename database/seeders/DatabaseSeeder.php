<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Primero los catálogos: el resto del sistema los da por hechos.
            CatalogsSeeder::class,
            RolesAndPermissionsSeeder::class,
            MailTemplateSeeder::class,
        ]);
    }
}
