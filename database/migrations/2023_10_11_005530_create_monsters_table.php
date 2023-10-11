<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monsters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('max_hit_points');
            $table->integer('defense');
            $table->integer('attack_damage');
            $table->boolean('attack_is_ranged');
            $table->integer('max_damage');
            $table->integer('special_attack_chance');
            $table->integer('special_attack');
            $table->boolean('special_attack_is_ranged');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monsters');
    }
};
