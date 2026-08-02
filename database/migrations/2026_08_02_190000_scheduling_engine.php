<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 1 = ponedeljak … 7 = nedelja (ISO)
            $table->string('starts_at', 5); // "08:00"
            $table->string('ends_at', 5);
            $table->json('service_ids')->nullable(); // null = sve usluge doktora
            $table->timestamps();
            $table->index(['doctor_id', 'weekday']);
        });

        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->nullable()->constrained()->cascadeOnDelete(); // null = cela klinika (praznik)
            $table->date('date_from');
            $table->date('date_to');
            $table->string('reason');
            $table->boolean('repeat_yearly')->default(false);
            $table->timestamps();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->unsignedInteger('buffer_before')->default(0)->after('duration_minutes');
            $table->unsignedInteger('buffer_after')->default(0)->after('buffer_before');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->string('action_token', 64)->nullable()->unique()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', fn (Blueprint $table) => $table->dropColumn('action_token'));
        Schema::table('services', fn (Blueprint $table) => $table->dropColumn(['buffer_before', 'buffer_after']));
        Schema::dropIfExists('absences');
        Schema::dropIfExists('doctor_working_hours');
    }
};
