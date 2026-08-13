<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterActivity extends Model
{
    protected $fillable = [
        'kode_kegiatan',
        'kode_program',
        'nama_kegiatan',
    ];

    public function program()
    {
        return $this->belongsTo(MasterProgram::class, 'kode_program', 'kode_program');
    }

    public function subActivities()
    {
        return $this->hasMany(MasterSubActivity::class, 'kode_kegiatan', 'kode_kegiatan');
    }
}
