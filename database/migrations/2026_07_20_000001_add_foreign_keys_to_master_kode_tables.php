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
        Schema::table('master_activities', function (Blueprint $table) {
            $table->foreign('kode_program')
                ->references('kode_program')->on('master_programs')
                ->cascadeOnDelete();
        });

        Schema::table('master_sub_activities', function (Blueprint $table) {
            $table->foreign('kode_kegiatan')
                ->references('kode_kegiatan')->on('master_activities')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_sub_activities', function (Blueprint $table) {
            $table->dropForeign(['kode_kegiatan']);
        });

        Schema::table('master_activities', function (Blueprint $table) {
            $table->dropForeign(['kode_program']);
        });
    }
};
