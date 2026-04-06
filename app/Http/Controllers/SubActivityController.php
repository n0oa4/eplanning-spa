<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\SubActivity;

class SubActivityController extends Controller
{
    public function store(Request $request, Activity $activity)
    {
        if ($activity->program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_sub_kegiatan' => 'required|string|max:50',
            'nama_sub_kegiatan' => 'required|string|max:255',
            'pagu_anggaran' => 'required|numeric|min:0',
            'indikator' => 'required|string|max:255',
            'target' => 'required|string|max:100',
            'prioritas_provinsi' => 'nullable|string|max:255',
            'prioritas_kabupaten' => 'nullable|string|max:255',
            'bidang_urusan' => 'nullable|string|max:255',
            'n1' => 'nullable|numeric',
            'n2' => 'nullable|numeric',
        ]);

        $activity->subActivities()->create($validated);

        return redirect()->route('program.show', $activity->program_id)
            ->with('success', 'Sub kegiatan berhasil ditambahkan');
    }

    public function update(Request $request, SubActivity $subActivity)
    {
        if ($subActivity->activity->program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_sub_kegiatan' => 'required|string|max:50',
            'nama_sub_kegiatan' => 'required|string|max:255',
            'pagu_anggaran' => 'required|numeric|min:0',
            'indikator' => 'required|string|max:255',
            'target' => 'required|string|max:100',
            'prioritas_provinsi' => 'nullable|string|max:255',
            'prioritas_kabupaten' => 'nullable|string|max:255',
            'bidang_urusan' => 'nullable|string|max:255',
            'n1' => 'nullable|numeric',
            'n2' => 'nullable|numeric',
        ]);

        $subActivity->update($validated);

        return redirect()->route('program.show', $subActivity->activity->program_id)
            ->with('success', 'Sub kegiatan berhasil diupdate');
    }

    public function destroy(SubActivity $subActivity)
    {
        if ($subActivity->activity->program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $programId = $subActivity->activity->program_id;
        $subActivity->delete();

        return redirect()->route('program.show', $programId)
            ->with('success', 'Sub kegiatan berhasil dihapus');
    }
}
