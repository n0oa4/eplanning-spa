<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use App\Exports\RanwalExport;
use Maatwebsite\Excel\Facades\Excel;

class ProgramController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin')->only(['destroy', 'edit', 'update']);
        $this->middleware('role:admin|operator')->only(['index', 'show', 'create', 'store']);
    }

    public function index()
    {
        $programs = Program::whereIn('status', ['draft', 'verifikasi'])
            ->with('activities.subActivities')
            ->get();

        $programs->each(function ($program) {
            $program->activities->each(function ($activity) {
                $activity->total_pagu = $activity->subActivities->sum('pagu_anggaran');
            });
            $program->total_pagu = $program->activities->sum('total_pagu');
        });

        return Inertia::render('program/index', [
            'programs' => $programs
        ]);
    }

    public function create()
    {
        return Inertia::render('program/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_program' => 'required|string|max:50|unique:programs',
            'nama_program' => 'required|string|max:255',
            'tahun'        => 'required|integer|min:2000|max:2100',
        ]);

        Program::create([
            'kode_program' => $validated['kode_program'],
            'nama_program' => $validated['nama_program'],
            'tahun'        => $validated['tahun'],
            'status'       => 'draft',
            'created_by'   => Auth::id(),
        ]);

        return redirect()->route('program.index')
            ->with('success', 'Program berhasil dibuat');
    }

    public function show(Program $program)
    {
        $program->load('activities.subActivities');

        $program->activities->each(function ($activity) {
            $activity->total_pagu = $activity->subActivities->sum('pagu_anggaran');
        });

        $program->total_pagu = $program->activities->sum('total_pagu');

        return Inertia::render('program/show', [
            'program' => $program
        ]);
    }

    public function update(Request $request, Program $program)
    {
        if ($program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_program' => 'required|string|max:50|unique:programs,kode_program,' . $program->id,
            'nama_program' => 'required|string|max:255',
            'tahun'        => 'required|integer|min:2000|max:2100',
        ]);

        $program->update($validated);

        return redirect()->route('program.index')
            ->with('success', 'Program berhasil diupdate');
    }

    public function destroy(Program $program)
    {
        if ($program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat dihapus');
        }

        if ($program->activities()->count() > 0) {
            return redirect()->back()->with('error', 'Program memiliki kegiatan dan tidak dapat dihapus');
        }

        $program->delete();

        return redirect()->route('program.index')
            ->with('success', 'Program berhasil dihapus');
    }

    // === Approval Flow ===
    public function kembalikan($id)
    {
        $program = Program::findOrFail($id);
        if ($program->status !== 'verifikasi') {
            return back()->with('error', 'Program tidak bisa dikembalikan');
        }

        $program->update(['status' => 'draft']);
        return back()->with('success', 'Program berhasil dikembalikan ke draft');
    }

    public function verifikasi($id)
    {
        $program = Program::findOrFail($id);
        if ($program->status !== 'draft') {
            return back()->with('error', 'Hanya program draft yang bisa diverifikasi');
        }

        $program->update(['status' => 'verifikasi']);
        return back()->with('success', 'Program berhasil diverifikasi');
    }

    public function setujui($id)
    {
        $program = Program::findOrFail($id);
        if ($program->status !== 'verifikasi') {
            return back()->with('error', 'Program belum diverifikasi');
        }

        $program->update([
            'status'         => 'disetujui',
            'disetujui_pada' => now(),
            'disetujui_oleh' => Auth::id(),
        ]);

        return back()->with('success', 'Program berhasil disetujui');
    }

    // === Laporan & Export ===
    public function ranwal()
    {
        $programs = Program::with('activities.subActivities')->get();
        return view('ranwal.print', compact('programs'));
    }

    public function exportExcel()
    {
        return Excel::download(new RanwalExport, 'ranwal.xlsx');
    }
}
