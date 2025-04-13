<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('income_types', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name', 63);
            $table->string('description')->nullable();
            $table->boolean('taxable');
            $table->boolean('must_declare');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('income_types');
    }
};
