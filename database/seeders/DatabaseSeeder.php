<?php

namespace Database\Seeders;

use App\Models\TypeRevenu;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $types = [
            [
                'nom' => 'Professionnel',
                'description' => 'Revenus issus d\'activités professionnelles indépendantes'
            ],
            [
                'nom' => 'Prestation sociale',
                'description' => 'Aides et allocations diverses'
            ],
            [
                'nom' => 'Salaire',
                'description' => 'Revenus issus d\'un contrat de travail salarié'
            ]
        ];

        foreach ($types as $type) {
            TypeRevenu::create($type);
        }
    }
}
