<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\income_types;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        $types = [
            [
                'name' => 'Professionnel',
                'description' => 'Revenus issus d\'activités professionnelles indépendantes',
                'taxable' => 1,
                'must_declare' => 1,
            ],
            [
                'name' => 'Prestation CAF',
                'description' => 'Aides et allocations diverses',
                'taxable' => 0,
                'must_declare' => 0,
            ],
            [
                'name' => 'Pole Emploi Formation',
                'description' => 'Rémunération déclarable de pole emploi pour une formation',
                'taxable' => 0,
                'must_declare' => 1,
            ],
            [
                'name' => 'Salaire',
                'description' => 'Revenus issus d\'un contrat de travail salarié',
                'taxable' => 1,
                'must_declare' => 1,
            ],
            [
                'name' => 'Chômage',
                'description' => 'Assurance Chômage',
                'taxable' => 1,
                'must_declare' => 1,
            ],
            [
                'name' => 'Remboursement',
                'description' => 'Remboursement d\'un achat précédent',
                'taxable' => 0,
                'must_declare' => 0,
            ]
        ];

        foreach ($types as $type) {
            income_types::create($type);
        }
    }
}
