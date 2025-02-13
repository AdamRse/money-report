<?php
// database/migrations/2024_12_16_195209_incomeType.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('incomeType', function (Blueprint $table) {
            $table->id();
            $table->string('name', 63);
            $table->string('description')->nullable();
            $table->boolean('taxable');
            $table->boolean('must_declare');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('incomeType');
    }
};
