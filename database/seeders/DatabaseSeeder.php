<?php

namespace Database\Seeders;

use App\Models\TypeRevenu;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        $types = [
            [
                'nom' => 'Professionnel',
                'description' => 'Revenus issus d\'activités professionnelles indépendantes',
                'imposable' => 1,
                'declarable' => 1,
            ],
            [
                'nom' => 'Prestation CAF',
                'description' => 'Aides et allocations diverses',
                'imposable' => 0,
                'declarable' => 0,
            ],
            [
                'nom' => 'Pole Emploi Formation',
                'description' => 'Rémunération déclarable de pole emploi pour une formation',
                'imposable' => 0,
                'declarable' => 1,
            ],
            [
                'nom' => 'Salaire',
                'description' => 'Revenus issus d\'un contrat de travail salarié',
                'imposable' => 1,
                'declarable' => 1,
            ],
            [
                'nom' => 'Chômage',
                'description' => 'Assurance Chômage',
                'imposable' => 1,
                'declarable' => 1,
            ],
            [
                'nom' => 'Remboursement',
                'description' => 'Remboursement d\'un achat précédent',
                'imposable' => 0,
                'declarable' => 0,
            ]
        ];

        foreach ($types as $type) {
            TypeRevenu::create($type);
        }
    }
}
