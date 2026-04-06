<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Program;
use App\Models\Activity;
use App\Models\SubActivity;
use Illuminate\Support\Facades\Auth;
use App\Exports\RanwalExport;
use Maatwebsite\Excel\Facades\Excel;


class ProgramController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:admin')->only([
            'destroy',
            'edit',
            'update'
        ]);

        $this->middleware('role:admin|operator')->only([
            'index',
            'show',
            'create',
            'store'
        ]);
    }

    /**
     * Display a listing of the resource.
     */
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('program/create');
    }

    public function update(Request $request, $id)
    {
        $program = Program::findOrFail($id);

        if ($program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_program' => 'required|string|max:50',
            'nama_program' => 'required|string|max:255',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $program->update($validated);

        return redirect()->route('program.index');
    }

    public function destroy($id)
    {
        $program = Program::findOrFail($id);

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_program' => 'required|string|max:50',
            'nama_program' => 'required|string|max:255',
            'tahun' => 'required|integer',
        ]);

        Program::create([
            'kode_program' => $validated['kode_program'],
            'nama_program' => $validated['nama_program'],
            'tahun' => $validated['tahun'],
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('program.index');
    }

    public function storeActivity(Request $request, Program $program)
    {

        if ($program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_kegiatan' => 'required|string|max:50',
            'nama_kegiatan' => 'required|string|max:255',
        ]);

        $program->activities()->create($validated);

        return redirect()->route('program.show', $program->id);
    }

    public function updateActivity(Request $request, Activity $activity)
    {
        if ($activity->program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_kegiatan' => 'required|string|max:50',
            'nama_kegiatan' => 'required|string|max:255',
        ]);

        $activity->update($validated);

        return redirect()->route('program.show', $activity->program_id);
    }

    public function destroyActivity(Activity $activity)
    {
        if ($activity->program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $programId = $activity->program_id;

        $activity->delete(); // otomatis hapus sub karena cascade

        return redirect()->route('program.show', $programId);
    }

    public function storeSubActivity(Request $request, Activity $activity)
    {
        if ($activity->program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_sub_kegiatan' => 'required|string|max:50',
            'nama_sub_kegiatan' => 'required|string|max:255',
            'pagu_anggaran' => 'required|numeric',
            'indikator' => 'required|string|max:255',
            'target' => 'required|string|max:100',
            'prioritas_provinsi' => 'nullable|string|max:255',
            'prioritas_kabupaten' => 'nullable|string|max:255',
            'bidang_urusan' => 'nullable|string|max:255',
            'n1' => 'nullable|numeric',
            'n2' => 'nullable|numeric',
        ]);

        $activity->subActivities()->create($validated);

        return redirect()->route('program.show', $activity->program_id);
    }

    public function updateSubActivity(Request $request, SubActivity $subActivity)
    {
        if ($subActivity->activity->program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_sub_kegiatan' => 'required|string|max:50',
            'nama_sub_kegiatan' => 'required|string|max:255',
            'pagu_anggaran' => 'required|numeric',
            'indikator' => 'required|string|max:255',
            'target' => 'required|string|max:100',
            'prioritas_provinsi' => 'nullable|string|max:255',
            'prioritas_kabupaten' => 'nullable|string|max:255',
            'bidang_urusan' => 'nullable|string|max:255',
            'n1' => 'nullable|numeric',
            'n2' => 'nullable|numeric',
        ]);

        $subActivity->update($validated);

        return redirect()->route('program.show', $subActivity->activity->program_id);
    }

    public function destroySubActivity(SubActivity $subActivity)
    {
        if ($subActivity->activity->program->status !== 'draft') {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $programId = $subActivity->activity->program_id;

        $subActivity->delete();

        return redirect()->route('program.show', $programId);
    }

    /**
     * Display the specified resource.
     */
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

    public function kembalikan($id)
    {
        $program = Program::findOrFail($id);

        if ($program->status !== 'verifikasi') {
            return back()->with('error', 'Program tidak bisa dikembalikan');
        }

        $program->update([
            'status' => 'draft'
        ]);

        return redirect()->back()->with('success', 'Program berhasil dikembalikan ke draft');
    }

    public function verifikasi($id)
    {
        $program = Program::findOrFail($id);

        // hanya program draft yang bisa diverifikasi
        if ($program->status !== 'draft') {
            return back()->with('error', 'Program tidak bisa diverifikasi');
        }

        $program->update([
            'status' => 'verifikasi'
        ]);

        return redirect()->back()->with('success', 'Program berhasil diverifikasi');
    }

    public function setujui($id)
    {
        $program = Program::findOrFail($id);

        if ($program->status !== 'verifikasi') {
            return back()->with('error', 'Program belum diverifikasi');
        }

        $program->update([
            'status' => 'disetujui',
            'disetujui_pada' => now(),
            'disetujui_oleh' => Auth::id(),
        ]);

        return back()->with('success', 'Program berhasil disetujui');
    }

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
