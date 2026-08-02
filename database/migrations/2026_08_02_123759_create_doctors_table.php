<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('dr');
            $table->string('name');
            $table->string('specialty');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('ics_token', 64)->unique();
            $table->string('color', 7)->default('#0E6E6B');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
