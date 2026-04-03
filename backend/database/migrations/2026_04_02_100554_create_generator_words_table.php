<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('generator_words', function (Blueprint $table) {
            $table->id();
            $table->string('word');
            $table->string('language', 50);
            $table->string('theme', 50);
            $table->timestamps();

            $table->index(['language', 'theme']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generator_words');
    }
};