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


    protected $fillable = [
    'kode_kegiatan',
    'program_id',
    'nama_kegiatan'
    ];
}
