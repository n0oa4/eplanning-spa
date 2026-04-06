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
        Schema::table('sub_activities', function (Blueprint $table) {
            $table->string('prioritas_provinsi')->nullable();
            $table->string('prioritas_kabupaten')->nullable();
            $table->string('bidang_urusan')->nullable();
            $table->decimal('n1', 15, 2)->nullable();
            $table->decimal('n2', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_activities', function (Blueprint $table) {
            $table->dropColumn([
                'prioritas_provinsi',
                'prioritas_kabupaten',
                'bidang_urusan',
                'n1',
                'n2'
            ]);
        });
    }
};
