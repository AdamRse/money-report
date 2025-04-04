> [!IMPORTANT]
> L'application est en cours de développement, certaines fonctionnalités essentielles ne sont pas terminées !

# Exigences
Laravel 11 avec php 8.3
# Architecture
```
app/
├── Abstract
│   └── BankParserAbstract.php
├── Console
│   └── Commands
│       └── ClearBankParsersCache.php
├── Factories
│   └── BankParserFactory.php
├── Http
│   ├── Controllers
│   │   ├── AuthController.php
│   │   ├── Controller.php
│   │   ├── IncomeController.php
│   │   ├── IncomeImportController.php
│   │   ├── IncomeReportController.php
│   │   ├── IncomeTypeController.php
│   │   └── SingletonController.php
│   └── Requests
│       ├── Auth
│       │   ├── LoginRequest.php
│       │   └── RegisterRequest.php
│       ├── Income
│       │   ├── StoreIncomeRequest.php
│       │   └── UpdateIncomeRequest.php
│       ├── IncomeImport
│       │   ├── ImportFileRequest.php
│       │   └── ImportIncomesRequest.php
│       ├── IncomeReport
│       │   └── FilterIncomesRequest.php
│       └── IncomeType
│           ├── StoreIncomeTypeRequest.php
│           └── UpdateIncomeTypeRequest.php
├── Interfaces
│   ├── Factories
│   │   └── BankParserFactoryInterface.php
│   ├── Repositories
│   │   └── IncomeRepositoryInterface.php
│   └── Services
│       ├── BankParserInterface.php
│       ├── DateParserServiceInterface.php
│       ├── DocumentParserServiceInterface.php
│       ├── FileEncodingServiceInterface.php
│       ├── IncomeDuplicateCheckerServiceInterface.php
│       └── IncomeStatisticsServiceInterface.php
├── Models
│   ├── Income.php
│   ├── IncomeType.php
│   └── User.php
├── Providers
│   ├── AppServiceProvider.php
│   └── BankParserFactoryProvider.php
├── Repositories
│   └── IncomeRepository.php
├── Services
│   ├── BankParsers
│   │   └── LaBanquePostaleParser.php
│   ├── DateParserService.php
│   ├── DocumentParserService.php
│   ├── FileEncodingService.php
│   ├── IncomeDuplicateCheckerService.php
│   └── IncomeStatisticsService.php
└── Traits
    └── ErrorManagementTrait.php
```
# Procédure pour ajouter un parser de banque
- Ajouter la classe dans `app/Services/BankParser` qui doit hériter de `BankParserAbstract` (`app/Abstract/BankParserAbstract.php`)
- Ajouter cette nouvelle classe aux options de `BankParserFactory` (`app/Factories/BankParserFactory.php`)
> [!IMPORTANT]
> Le fichier doit respecter la convention de nommage PSR-4 (Même nom que la classe).
> Par exemple la classe `MaNouvelleBanqueParser` doit se trouver dans le fichier `app/Services/BankParser/MaNouvelleBanqueParser.php`.
> La prise en compte se faisant automatiquement, veiller à vider le cache pour une prise en compte immédiate.
### Vider le cache des bank parsers :
`php artisan bank-parsers:clear-cache`
La commande est dans le répertoire `app/Console/Commands/ClearBankParsersCache.php`
