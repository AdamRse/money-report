<?php
// database/migrations/2024_12_16_195222_income.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2); // Permet des amounts jusqu'à 99,999,999.99
            $table->date('income_date');
            $table->foreignId('income_type_id')->constrained('income_types');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('incomes');
    }
};
