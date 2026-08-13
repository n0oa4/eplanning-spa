<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Activity;
use App\Models\MasterActivity;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:operator')->only(['store', 'update', 'destroy']);
    }
    public function store(Request $request, Program $program)
    {
        if (! in_array($program->status, ['draft', 'ditolak'])) {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_kegiatan' => [
                'required',
                'string',
                'max:50',
                'regex:/^\d+(\.\d{2})*\.2\.\d{2}$/',
            ],
            'nama_kegiatan' => 'required|string|max:255',
        ], [
            'kode_kegiatan.regex' => 'Format kode kegiatan harus seperti 3.27.01.2.01',
        ]);

        $masterActivity = MasterActivity::where('kode_kegiatan', $validated['kode_kegiatan'])->first();
        $validated['master_activity_id'] = $masterActivity?->id;

        $program->activities()->create($validated);

        return redirect()->route('program.index')
            ->with('success', 'Kegiatan berhasil ditambahkan');
    }

    public function update(Request $request, Activity $activity)
    {
        if (! in_array($activity->program->status, ['draft', 'ditolak'])) {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_kegiatan' => [
                'required',
                'string',
                'max:50',
                'regex:/^\d+(\.\d{2})*\.2\.\d{2}$/',
            ],
            'nama_kegiatan' => 'required|string|max:255',
        ], [
            'kode_kegiatan.regex' => 'Format kode kegiatan harus seperti 3.27.01.2.01',
        ]);

        $masterActivity = MasterActivity::where('kode_kegiatan', $validated['kode_kegiatan'])->first();
        $validated['master_activity_id'] = $masterActivity?->id;

        $activity->update($validated);

        return redirect()->route('program.index')
            ->with('success', 'Kegiatan berhasil diupdate');
    }

    public function destroy(Activity $activity)
    {
        if (! in_array($activity->program->status, ['draft', 'ditolak'])) {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $activity->delete(); // cascade sub-activity otomatis

        return redirect()->route('program.index')
            ->with('success', 'Kegiatan berhasil dihapus');
    }
}