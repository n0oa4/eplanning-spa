<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $totalProgram = Program::whereIn('status', ['draft', 'verifikasi'])->count();
        $menungguPersetujuan = Program::where('status', 'verifikasi')->count();
        $totalPagu = Program::whereIn('status', ['draft', 'verifikasi'])
            ->with('activities.subActivities')
            ->get()
            ->sum(function ($program) {
                return $program->activities->sum(function ($activity) {
                    return $activity->subActivities->sum('pagu_anggaran');
                });
            });

        $programTerbaru = Program::whereIn('status', ['draft', 'verifikasi'])
            ->with('activities.subActivities')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($program) {
                $program->total_pagu = $program->activities->sum(function ($activity) {
                    return $activity->subActivities->sum('pagu_anggaran');
                });
                return $program;
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_program' => $totalProgram,
                'menunggu_persetujuan' => $menungguPersetujuan,
                'total_pagu' => $totalPagu,
            ],
            'program_terbaru' => $programTerbaru,
        ]);
    }
}
