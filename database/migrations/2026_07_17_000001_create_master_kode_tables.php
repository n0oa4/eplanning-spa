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
        Schema::create('master_programs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_program')->unique();
            $table->string('nama_program');
            $table->timestamps();
        });

        Schema::create('master_activities', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kegiatan')->unique();
            $table->string('kode_program')->index();
            $table->string('nama_kegiatan');
            $table->timestamps();
        });

        Schema::create('master_sub_activities', function (Blueprint $table) {
            $table->id();
            $table->string('kode_sub_kegiatan')->unique();
            $table->string('kode_kegiatan')->index();
            $table->string('nama_sub_kegiatan');
            $table->text('indikator')->nullable();
            $table->string('target')->nullable();
            $table->text('prioritas_provinsi')->nullable();
            $table->text('prioritas_kabupaten')->nullable();
            $table->string('bidang_urusan')->nullable();
            $table->decimal('pagu_anggaran', 18, 2)->nullable();
            $table->decimal('n1', 18, 2)->nullable();
            $table->decimal('n2', 18, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_sub_activities');
        Schema::dropIfExists('master_activities');
        Schema::dropIfExists('master_programs');
    }
};
