<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Activity;
use App\Models\SubActivity;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:operator|kabid|admin');
    }

    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // === Cari Program ===
        $programs = Program::where('nama_program', 'like', "%{$query}%")
            ->orWhere('kode_program', 'like', "%{$query}%")
            ->limit(8)
            ->get(['id', 'nama_program', 'kode_program', 'status']);

        foreach ($programs as $program) {
            $results[] = [
                'type' => 'program',
                'id' => $program->id,
                'label' => $program->nama_program,
                'sublabel' => $program->kode_program,
                'status' => $program->status,
                'program_id' => $program->id,
                'activity_id' => null,
            ];
        }

        // === Cari Kegiatan ===
        $activities = Activity::where('nama_kegiatan', 'like', "%{$query}%")
            ->orWhere('kode_kegiatan', 'like', "%{$query}%")
            ->with('program:id,nama_program,status')
            ->limit(8)
            ->get(['id', 'nama_kegiatan', 'kode_kegiatan', 'program_id']);

        foreach ($activities as $activity) {
            $results[] = [
                'type' => 'kegiatan',
                'id' => $activity->id,
                'label' => $activity->nama_kegiatan,
                'sublabel' => $activity->kode_kegiatan,
                'status' => $activity->program?->status,
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'program_name' => $activity->program?->nama_program,
            ];
        }

        // === Cari Sub Kegiatan ===
        $subActivities = SubActivity::where('nama_sub_kegiatan', 'like', "%{$query}%")
            ->orWhere('kode_sub_kegiatan', 'like', "%{$query}%")
            ->with('activity:id,program_id,nama_kegiatan', 'activity.program:id,nama_program,status')
            ->limit(8)
            ->get(['id', 'nama_sub_kegiatan', 'kode_sub_kegiatan', 'activity_id']);

        foreach ($subActivities as $sub) {
            $results[] = [
                'type' => 'sub_kegiatan',
                'id' => $sub->id,
                'label' => $sub->nama_sub_kegiatan,
                'sublabel' => $sub->kode_sub_kegiatan,
                'status' => $sub->activity?->program?->status,
                'program_id' => $sub->activity?->program_id,
                'activity_id' => $sub->activity_id,
                'sub_activity_id' => $sub->id,
                'program_name' => $sub->activity?->program?->nama_program,
                'activity_name' => $sub->activity?->nama_kegiatan,
            ];
        }

        return response()->json(['results' => $results]);
    }
}
