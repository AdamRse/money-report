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
|   |   ├── IncomeType/
|   |   |   ├── StoreIncomeTypeRequest.php
|   |   |   └── UpdateIncomeTypeRequest.php
|   |   ├── IncomeImport/
|   |   |   ├── ImportFileRequest.php
|   |   |   └── ImportIncomesRequest.php
|   |   └── IncomeReport/
|   |       └── FilterIncomesRequest.php
├── Models/
|   ├── Income.php
│   └── IncomeType.php
├── Providers/
│   └── AppServiceProvider.php
├── Services/
│   └── IncomeStatisticsService.php
```
