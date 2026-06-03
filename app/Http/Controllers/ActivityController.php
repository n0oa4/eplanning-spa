<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Activity;

class ActivityController extends Controller
{
    public function store(Request $request, Program $program)
    {
        if ($program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_kegiatan' => 'required|string|max:50',
            'nama_kegiatan' => 'required|string|max:255',
        ]);

        $program->activities()->create($validated);

        return redirect()->route('program.show', $program->id)
            ->with('success', 'Kegiatan berhasil ditambahkan');
    }

    public function update(Request $request, Activity $activity)
    {
        if ($activity->program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_kegiatan' => 'required|string|max:50',
            'nama_kegiatan' => 'required|string|max:255',
        ]);

        $activity->update($validated);

        return redirect()->route('program.show', $activity->program_id)
            ->with('success', 'Kegiatan berhasil diupdate');
    }

    public function destroy(Activity $activity)
    {
        if ($activity->program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $programId = $activity->program_id;
        $activity->delete(); // cascade sub-activity otomatis

        return redirect()->route('program.show', $programId)
            ->with('success', 'Kegiatan berhasil dihapus');
    }
}
