<?php

namespace App\Http\Controllers;

use App\Models\MasterActivity;
use App\Models\MasterProgram;
use App\Models\MasterSubActivity;

class MasterKodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:operator');
    }

    /**
     * Kirim seluruh data master kode (Program, Kegiatan, Sub Kegiatan) sekaligus.
     * Datanya kecil (puluhan-ratusan baris), jadi lookup by kode dilakukan
     * di sisi frontend tanpa perlu request per-ketikan.
     */
    public function index()
    {
        return response()->json([
            'programs' => MasterProgram::query()
                ->select(['kode_program', 'nama_program'])
                ->get(),

            'activities' => MasterActivity::query()
                ->select(['kode_kegiatan', 'kode_program', 'nama_kegiatan'])
                ->get(),

            'sub_activities' => MasterSubActivity::query()
                ->select([
                    'kode_sub_kegiatan', 'kode_kegiatan', 'nama_sub_kegiatan',
                    'indikator', 'target', 'prioritas_provinsi', 'prioritas_kabupaten',
                    'bidang_urusan', 'pagu_anggaran', 'n1', 'n2',
                ])
                ->get(),
        ]);
    }
}
