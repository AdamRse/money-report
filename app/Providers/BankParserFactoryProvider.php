<?php

namespace App\Providers;

use App\Abstract\BankParserAbstract;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class BankParserFactoryProvider extends ServiceProvider
{
    /**
     * Indique que le provider doit être chargé en différé
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('bank.parsers', function ($app) {
            return $this->discoverBankParsers();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Découvre automatiquement les parseurs de banque dans le répertoire spécifié
     * et met en cache le résultat pour éviter de relire le répertoire à chaque requête
     *
     * @return array<string, string> Liste des parseurs disponibles (clé = nom de la banque, valeur = nom de classe complet)
     */
    protected function discoverBankParsers(): array
    {
        // Cache pendant 1 heure, en production on pourrait augmenter cette durée
        return Cache::remember('bank.parsers.list', 3600, function () {
            $parsers = [];
            $path = app_path('Services/BankParser');

            if (!File::isDirectory($path)) {
                return $parsers;
            }

            // Parcours tous les fichiers PHP du répertoire
            foreach (File::files($path) as $file) {
                if ($file->getExtension() === 'php') {
                    $className = 'App\\Services\\BankParser\\' . $file->getFilenameWithoutExtension();

                    // Vérifie que la classe existe et qu'elle hérite de BankParserAbstract
                    if (class_exists($className)) {
                        $reflection = new ReflectionClass($className);
                        if ($reflection->isSubclassOf(BankParserAbstract::class) && !$reflection->isAbstract()) {
                            // Extrait le nom de la banque à partir du nom du fichier (ex: LaBanquePostaleParser -> LaBanquePostale)
                            $bankName = Str::replaceLast('Parser', '', $file->getFilenameWithoutExtension());
                            $parsers[$bankName] = $className;
                        }
                    }
                }
            }

            return $parsers;
        });
    }

    /**
     * Retourne les services fournis par ce provider
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return ['bank.parsers'];
    }
}
