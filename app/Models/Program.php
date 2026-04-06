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



    protected $fillable = [
    'kode_program',
    'nama_program',
    'tahun',
    'status',
    'created_by',
    'disetujui_oleh',
    'disetujui_pada'
    ];
}
