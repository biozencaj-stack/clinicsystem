<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->boolean('viber_opt_in')->default(false)->after('whatsapp_opt_in');
            $table->boolean('email_opt_in')->default(false)->after('viber_opt_in');
        });

        Schema::table('nalazs', function (Blueprint $table) {
            $table->text('content')->nullable()->after('title');
        });

        Schema::rename('whatsapp_messages', 'messages');

        Schema::table('messages', function (Blueprint $table) {
            $table->string('channel')->default('whatsapp')->after('direction');
            $table->renameColumn('to_phone', 'destination');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->renameColumn('destination', 'to_phone');
            $table->dropColumn('channel');
        });

        Schema::rename('messages', 'whatsapp_messages');

        Schema::table('nalazs', function (Blueprint $table) {
            $table->dropColumn('content');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['viber_opt_in', 'email_opt_in']);
        });
    }
};
