<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
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

            $table->unsignedTinyInteger("id")->primary();
            $table->string('label');
            foreach($modelsList as $model){
                $table->string($model, 3)->nullable()->comment("niveaux de droit d'accès (r = read, w = write, d = delete) au modèle $model");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
