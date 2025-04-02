<?php

namespace App\Providers;

use App\Abstract\BankParserAbstract;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class BankParserFactoryProvider extends ServiceProvider implements DeferrableProvider{

    public function register(): void{
        $this->app->singleton('bank.parsers', function ($app) {
            return $this->discoverBankParsers();
        });
    }

    public function boot(): void{
        //
    }

    /**
     * Découvre automatiquement les parseurs de banque dans le répertoire spécifié
     * et met en cache le résultat pour éviter de relire le répertoire à chaque requête
     *
     * @return array<string, string> Liste des parseurs disponibles (clé = nom de la banque, valeur = nom de classe complet)
     */
    protected function discoverBankParsers(): array{
        return Cache::remember('bank.parsers.list', env("NEW_BANK_PARSER_CACHE_DURATION", 43200), function () {
            $parsers = [];
            $path = app_path('Services/BankParsers');

            if (!File::isDirectory($path)) {
                return $parsers;
            }

            // Parcours tous les fichiers PHP du répertoire
            foreach (File::files($path) as $file) {
                if ($file->getExtension() === 'php') {
                    $className = '\\App\\Services\\BankParsers\\' . $file->getFilenameWithoutExtension();

                    // Vérifie que la classe existe et qu'elle hérite de BankParserAbstract
                    if (class_exists($className)) {
                        $reflection = new ReflectionClass($className);
                        if ($reflection->isSubclassOf(BankParserAbstract::class) && !$reflection->isAbstract())
                            $parsers[] = $className;
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
