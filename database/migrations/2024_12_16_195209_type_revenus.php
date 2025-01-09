<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('type_revenus', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 63);
            $table->string('description')->nullable();
            $table->boolean('imposable');
            $table->boolean('declarable');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('type_revenus');
    }
};
