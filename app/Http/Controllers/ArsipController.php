<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ArsipController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->input('sort', 'disetujui_pada');
        $sortDirection = $request->input('direction', 'desc');

        $query = Program::where('status', 'disetujui')
            ->with('activities.subActivities', 'approver');

        if ($request->input('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if (in_array($sortField, ['tahun', 'disetujui_pada'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $programs = $query->get();

        $programs->each(function ($program) {

            $program->activities->each(function ($activity) {

                $activity->total_pagu = $activity->subActivities->sum('pagu_anggaran');

            });

            $program->total_pagu = $program->activities->sum('total_pagu');

        });

        if ($sortField === 'total_pagu') {
            $programs = $sortDirection === 'asc'
                ? $programs->sortBy('total_pagu')
                : $programs->sortByDesc('total_pagu');
        } else {
            // 🔥 reset urutan berdasarkan DB (default)
            $programs = $programs->sortByDesc('disetujui_pada');
        }

        $programs = $programs->values();

        return Inertia::render('arsip/index', [
            'programs' => $programs,
            'filters' => $request->only('tahun', 'sort', 'direction')
        ]);
    }
}
