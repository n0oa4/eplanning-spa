<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\SubActivity;

class SubActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:operator')->only(['store', 'update', 'destroy']);
    }
    public function store(Request $request, Activity $activity)
    {
        if (! in_array($activity->program->status, ['draft', 'ditolak'])) {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_sub_kegiatan' => [
                'required',
                'string',
                'max:50',
                'regex:/^\d+(\.\d{2})*\.2\.\d{2}\.\d{4}$/',
            ],
            'nama_sub_kegiatan' => 'required|string|max:255',
            'pagu_anggaran' => 'required|numeric|min:0',
            'indikator' => 'required|string|max:255',
            'target' => 'required|string|max:100',
            'prioritas_provinsi' => 'nullable|string|max:255',
            'prioritas_kabupaten' => 'nullable|string|max:255',
            'bidang_urusan' => 'nullable|string|max:255',
            'n1' => 'nullable|numeric',
            'n2' => 'nullable|numeric',
        ], [
            'kode_sub_kegiatan.regex' => 'Format kode sub kegiatan harus seperti 3.27.01.2.01.0001',
        ]);

        $activity->subActivities()->create($validated);

        return redirect()->route('program.index')
            ->with('success', 'Sub kegiatan berhasil ditambahkan');
    }

    public function update(Request $request, SubActivity $subActivity)
    {
        if (! in_array($subActivity->activity->program->status, ['draft', 'ditolak'])) {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_sub_kegiatan' => [
                'required',
                'string',
                'max:50',
                'regex:/^\d+(\.\d{2})*\.2\.\d{2}\.\d{4}$/',
            ],
            'nama_sub_kegiatan' => 'required|string|max:255',
            'pagu_anggaran' => 'required|numeric|min:0',
            'indikator' => 'required|string|max:255',
            'target' => 'required|string|max:100',
            'prioritas_provinsi' => 'nullable|string|max:255',
            'prioritas_kabupaten' => 'nullable|string|max:255',
            'bidang_urusan' => 'nullable|string|max:255',
            'n1' => 'nullable|numeric',
            'n2' => 'nullable|numeric',
        ], [
            'kode_sub_kegiatan.regex' => 'Format kode sub kegiatan harus seperti 3.27.01.2.01.0001',
        ]);

        $subActivity->update($validated);

        return redirect()->route('program.index')
            ->with('success', 'Sub kegiatan berhasil diupdate');
    }

    public function destroy(SubActivity $subActivity)
    {
        if (! in_array($subActivity->activity->program->status, ['draft', 'ditolak'])) {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $subActivity->delete();

        return redirect()->route('program.index')
            ->with('success', 'Sub kegiatan berhasil dihapus');
    }
}