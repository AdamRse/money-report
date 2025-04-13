<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->decimal('amount', 10, 2); // Permet des amounts jusqu'à 99,999,999.99
            $table->date('income_date');
            $table->unsignedTinyInteger('income_type_id');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('income_date');
        });

    }

    public function down() {
        Schema::dropIfExists('incomes');
    }
};
