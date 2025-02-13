<?php
// database/migrations/2024_12_16_195222_income.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('income', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2); // Permet des montants jusqu'à 99,999,999.99
            $table->date('income_date');
            $table->foreignId('incomeType_id')->constrained('incomeType');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('income');
    }
};
