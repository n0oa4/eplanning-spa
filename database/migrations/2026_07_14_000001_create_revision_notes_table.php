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
        Schema::create('revision_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('program_id')
                ->constrained('programs')
                ->cascadeOnDelete();

            // Relasi polymorphic: catatan bisa menempel ke Program, Activity, atau SubActivity
            // -> menghasilkan kolom notable_type & notable_id
            $table->morphs('notable');

            $table->text('catatan');

            // Status: terbuka | dikonfirmasi_operator | selesai
            $table->string('status')->default('terbuka');

            // Kabid yang membuat catatan
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // Operator yang klik "Konfirmasi" setelah edit item
            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();

            // Kabid yang menutup catatan (menandai selesai) saat review ulang
            $table->foreignId('resolved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revision_notes');
    }
};