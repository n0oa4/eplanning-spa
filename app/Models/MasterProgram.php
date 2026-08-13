<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterProgram extends Model
{
    protected $fillable = [
        'kode_program',
        'nama_program',
    ];

    public function activities()
    {
        return $this->hasMany(MasterActivity::class, 'kode_program', 'kode_program');
    }
}
