<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gamertag_values', function (Blueprint $table) {
            $table->id();
            $table->string('gamertag')->unique();
            $table->enum('tier', ['legendary', 'rare', 'uncommon', 'common', 'low_value']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gamertag_values');
    }
};