<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubActivity extends Model
{
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    protected $fillable = [
    'kode_sub_kegiatan',
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
