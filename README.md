# Architecture
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── IncomeController.php
│   │   ├── IncomeImportController.php
│   │   ├── IncomeReportController.php
│   │   ├── IncomeTypeController.php
│   │   └── SingletonController.php # Will be removed
│   │
│   ├── Requests/
|   |   ├── Auth/
|   |   |   ├── LoginRequest.php
|   |   |   └── RegisterRequest.php
|   |   ├── Income/
|   |   |   ├── StoreIncomeRequest.php
|   |   |   └── UpdateIncomeRequest.php
|   |   ├── income_types/
|   |   |   ├── StoreincomeTypeRequest.php
|   |   |   └── UpdateIncomeTypeRequest.php
|   |   ├── IncomeImport/
|   |   |   ├── ImportFileRequest.php
|   |   |   └── ImportIncomesRequest.php
|   |   └── IncomeReport/
|   |       └── FilterIncomesRequest.php
├── Models/
|   ├── Income.php
│   └── IncomeTypes.php
├── Providers/
│   └── AppServiceProvider.php
└── Services/
    └── IncomeStatisticsService.php
```
# Procédure pour ajouter un parser de banque
- Ajouter la classe dans `app/Services/BankParser` qui doit hériter de `BankParserAbstract` (`app/Abstract/BankParserAbstract.php`)
- Ajouter cette nouvelle classe aux options de `BankParserFactory` (`app/Factories/BankParserFactory.php`)
> [!IMPORTANT]
> Le fichier doit respecter la convention de nommage PSR-4 (Même nom que la classe).
> Par exemple la classe `MaNouvelleBanqueParser` doit se trouver dans le fichier `app/Services/BankParser/MaNouvelleBanqueParser.php`.
> La prise en compte se faisant automatiquement, veiller à vider le cache pour une prise en compte immédiate.
## Vider le cache des bank parsers :
`php artisan bank-parsers:clear-cache`
La commande est dans le répertoire `app/Console/Commands/ClearBankParsersCache.php`


# Erreurs
- `Le parseur de la banque n'a pas été trouvé.` : La factory BankParserFactory n'a pas pu déterminer le bon parseur en fonction du fichier uploadé, mais sans retourner d'erreur explicite (app/Factories/BankParserFactory.php)
