<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\IncomeType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */

    private function getAllModels():array{
        //On doit chercher tous les modèles existants dans app/Models pour définir des droits spécifiques à chaque modèles
        $modelsList = [];

        $modelsPath = app_path('Models');
        $files = File::allFiles($modelsPath);

        foreach ($files as $file){
            $relativePath = str_replace(
                ['/', '.php'],
                ['\\', ''],
                $file->getRelativePathname()
            );

            $className = 'App\\Models\\' . $relativePath;

            // Vérifier si la classe existe et qu'elle n'est pas déjà ajouté à $modelsList
            if(class_exists($className)){
                $addModel = true;
                foreach ($modelsList as $model) {
                    if($model==$className){
                        $addModel = false;
                        break;
                    }
                }

                if($addModel)
                    $modelsList[] = $className;
            }
        }
        return $modelsList;
    }
    public function run(): void {
        $adminRignts = [];
        foreach ($this->getAllModels() as $modelName){
            $adminRignts[$modelName]="rwd";
        }
        $roles = [
            [
                'id' => 1,
                'label' => 'admin',
                ...$adminRignts //Tout à rwd
            ],
            [
                'id' => 2,
                'label' => 'customer'
            ]
        ];
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

        foreach ($types as $type){
            IncomeType::create($type);
        }
        foreach ($roles as $role){
            Role::create($role);
        }
    }
}
