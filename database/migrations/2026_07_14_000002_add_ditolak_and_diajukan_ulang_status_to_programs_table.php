<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE programs MODIFY COLUMN status ENUM('draft', 'verifikasi', 'disetujui', 'ditolak', 'diajukan_ulang') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Catatan: rollback hanya aman kalau tidak ada baris programs yang sedang berstatus
        // 'ditolak' atau 'diajukan_ulang' saat migration ini di-rollback.
        DB::statement("ALTER TABLE programs MODIFY COLUMN status ENUM('draft', 'verifikasi', 'disetujui') DEFAULT 'draft'");
    }
};
