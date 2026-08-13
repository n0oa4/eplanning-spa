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
        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('master_program_id')
                ->nullable()
                ->after('kode_program')
                ->constrained('master_programs')
                ->nullOnDelete();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('master_activity_id')
                ->nullable()
                ->after('kode_kegiatan')
                ->constrained('master_activities')
                ->nullOnDelete();
        });

        Schema::table('sub_activities', function (Blueprint $table) {
            $table->foreignId('master_sub_activity_id')
                ->nullable()
                ->after('kode_sub_kegiatan')
                ->constrained('master_sub_activities')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('master_program_id');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('master_activity_id');
        });

        Schema::table('sub_activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('master_sub_activity_id');
        });
    }
};
