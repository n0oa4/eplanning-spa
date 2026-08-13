<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
   public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalPaguAttribute()
    {
        return $this->activities->sum(function ($activity) {
            return $activity->subActivities->sum('pagu_anggaran');
        });
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * Baris data master (referensi Excel RENJA) yang kode-nya cocok dengan
     * kode_program milik Program ini. Bisa null kalau tidak ada yang cocok.
     */
    public function masterProgram()
    {
        return $this->belongsTo(MasterProgram::class, 'master_program_id');
    }

    /**
     * Semua catatan perbaikan di bawah program ini — baik yang menempel pada
     * Program itu sendiri, Kegiatan, maupun Sub Kegiatan.
     */
    public function revisionNotes()
    {
        return $this->hasMany(RevisionNote::class);
    }

    /**
     * Catatan perbaikan yang menempel langsung pada Program ini
     * (bukan pada Kegiatan/Sub Kegiatan di bawahnya).
     */
    public function notes()
    {
        return $this->morphMany(RevisionNote::class, 'notable');
    }

    /**
     * Apakah program ini masih punya catatan berstatus 'terbuka'.
     * Dipakai untuk logika aktif/nonaktif tombol "Tolak Program".
     */
    public function hasOpenRevisionNotes(): bool
    {
        return $this->revisionNotes()
            ->where('status', RevisionNote::STATUS_TERBUKA)
            ->exists();
    }

    /**
     * Apakah semua catatan sudah dikonfirmasi operator (tidak ada yang masih 'terbuka'),
     * sehingga program siap diajukan ulang ke Kabid.
     */
    public function canBeResubmitted(): bool
    {
        return ! $this->revisionNotes()
            ->where('status', RevisionNote::STATUS_TERBUKA)
            ->exists();
    }

    protected $fillable = [
    'kode_program',
    'master_program_id',
    'nama_program',
    'tahun',
    'status',
    'created_by',
    'disetujui_oleh',
    'disetujui_pada'
    ];
}