<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSubActivity extends Model
{
    protected $fillable = [
        'kode_sub_kegiatan',
        'kode_kegiatan',
        'nama_sub_kegiatan',
        'indikator',
        'target',
        'prioritas_provinsi',
        'prioritas_kabupaten',
        'bidang_urusan',
        'pagu_anggaran',
        'n1',
        'n2',
    ];

    protected $casts = [
        'pagu_anggaran' => 'decimal:2',
        'n1'            => 'decimal:2',
        'n2'            => 'decimal:2',
    ];

    public function activity()
    {
        return $this->belongsTo(MasterActivity::class, 'kode_kegiatan', 'kode_kegiatan');
    }
}
