<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubActivity extends Model
{
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Catatan perbaikan yang menempel langsung pada Sub Kegiatan ini.
     */
    public function notes()
    {
        return $this->morphMany(RevisionNote::class, 'notable');
    }

    /**
     * Baris data master (referensi Excel RENJA) yang kode-nya cocok dengan
     * kode_sub_kegiatan milik Sub Kegiatan ini. Bisa null kalau tidak ada yang cocok.
     */
    public function masterSubActivity()
    {
        return $this->belongsTo(MasterSubActivity::class, 'master_sub_activity_id');
    }

    protected $fillable = [
    'kode_sub_kegiatan',
    'master_sub_activity_id',
    'activity_id',
    'nama_sub_kegiatan',
    'pagu_anggaran',
    'indikator',
    'target',
    'prioritas_provinsi',
    'prioritas_kabupaten',
    'bidang_urusan',
    'n1',
    'n2'
    ];
}
