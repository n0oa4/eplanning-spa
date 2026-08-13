<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function subActivities()
    {
        return $this->hasMany(SubActivity::class);
    }

    public function getTotalPaguAttribute()
    {
        return $this->subActivities->sum('pagu_anggaran');
    }

    /**
     * Catatan perbaikan yang menempel langsung pada Kegiatan ini.
     */
    public function notes()
    {
        return $this->morphMany(RevisionNote::class, 'notable');
    }

    /**
     * Baris data master (referensi Excel RENJA) yang kode-nya cocok dengan
     * kode_kegiatan milik Kegiatan ini. Bisa null kalau tidak ada yang cocok.
     */
    public function masterActivity()
    {
        return $this->belongsTo(MasterActivity::class, 'master_activity_id');
    }

    protected $fillable = [
    'kode_kegiatan',
    'master_activity_id',
    'program_id',
    'nama_kegiatan'
    ];
}
