# Architecture
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── IncomeController.php
│   │   ├── IncomeImportController.php
│   │   ├── IncomeReportController.php
│   │   ├── incomeTypesController.php
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
├── Services/
│   └── IncomeStatisticsService.php
```
