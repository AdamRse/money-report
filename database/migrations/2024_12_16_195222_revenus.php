<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('revenus', function (Blueprint $table) {
            $table->id();
            $table->decimal('montant', 10, 2); // Permet des montants jusqu'à 99,999,999.99
            $table->date('date_revenu');
            $table->foreignId('type_revenu_id')->constrained('type_revenus');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('revenus');
    }
};
