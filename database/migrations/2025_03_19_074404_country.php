<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // Schema::create('country', function (Blueprint $table) {
        //     $table->unsignedTinyInteger('id')->autoIncrement()->primary();
        //     $table->string("name", 63);
        //     $table->string("timezone", 63);
        //     $table->string("code_alpha2", 3)->comment("code pays (FR) norme ISO 3166-1 alpha-2");
        //     $table->string("code_alpha3", 3)->comment("code pays (FR) norme ISO 3166-1 alpha-2");
        //     $table->string("tag", 7)->comment("langage-région (fr-FR) norme IETF");
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
    }
};
