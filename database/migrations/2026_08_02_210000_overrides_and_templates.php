<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_schedule_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('reason')->nullable();
            $table->json('periods'); // [{starts_at, ends_at, service_ids|null}]
            $table->timestamps();
            $table->unique(['doctor_id', 'date']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('doctor_id')->nullable()->after('email')->constrained()->nullOnDelete();
        });

        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event'); // potvrda, podsetnik, nalaz, dokument, odbijen
            $table->string('name');
            $table->json('service_ids')->nullable(); // null = sve usluge
            $table->unsignedInteger('offset_hours')->nullable(); // za podsetnik: koliko sati pre termina
            $table->text('body');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index('event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('doctor_id');
        });
        Schema::dropIfExists('doctor_schedule_overrides');
    }
};
