<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('generator_gender_words', function (Blueprint $table) {
            $table->id();
            $table->string('word');
            $table->string('language', 50);
            $table->string('gender', 20);
            $table->string('position', 20); // prefix or suffix
            $table->timestamps();

            $table->index(['language', 'gender', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generator_gender_words');
    }
};