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
            $table->string('kode_program')->unique();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->string('kode_kegiatan');
        });

        Schema::table('sub_activities', function (Blueprint $table) {
            $table->string('kode_sub_kegiatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique(['kode_program']);
            $table->dropColumn('kode_program');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('kode_kegiatan');
        });

        Schema::table('sub_activities', function (Blueprint $table) {
            $table->dropColumn('kode_sub_kegiatan');
        });
    }
};
