# Architecture
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── IncomeController.php
│   │   ├── IncomeImportController.php
│   │   ├── IncomeReportController.php
│   │   ├── income_typesController.php
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
|   |   |   ├── Storeincome_typesRequest.php
|   |   |   └── Updateincome_typesRequest.php
|   |   ├── IncomeImport/
|   |   |   ├── ImportFileRequest.php
|   |   |   └── ImportIncomesRequest.php
|   |   └── IncomeReport/
|   |       └── FilterIncomesRequest.php
├── Models/
|   ├── Income.php
│   └── income_types.php
├── Providers/
│   └── AppServiceProvider.php
├── Services/
│   └── IncomeStatisticsService.php
```
