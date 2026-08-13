<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Program;
use App\Models\RevisionNote;
use Illuminate\Support\Facades\Auth;
use App\Exports\RanwalExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;

class ProgramController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:operator')->only(['destroy', 'edit', 'update', 'create', 'store', 'nextCode', 'konfirmasi', 'ajukan']);
        $this->middleware('role:operator|kabid')->only(['index', 'show']);

        // Approval — hanya kabid
        $this->middleware('role:kabid')->only(['verifikasi', 'kembalikan', 'tolak']);

        // Laporan & Export — operator dan kabid
        $this->middleware('role:operator|kabid')->only(['ranwal', 'exportExcel']);
    }

    public function index()
    {
        $user = User::find(Auth::id());

        $programs = Program::whereIn('status', ['draft', 'verifikasi', 'ditolak', 'diajukan_ulang'])
            ->with([
                'activities.subActivities',
                'notes' => fn ($query) => $query->with(['creator', 'confirmer', 'resolver'])->latest(),
                'activities.notes' => fn ($query) => $query->with(['creator', 'confirmer', 'resolver'])->latest(),
                'activities.subActivities.notes' => fn ($query) => $query->with(['creator', 'confirmer', 'resolver'])->latest(),
            ])
            ->get();

        $programs->each(function ($program) {
            $program->activities->each(function ($activity) {
                $activity->total_pagu = $activity->subActivities->sum('pagu_anggaran');
            });
            $program->total_pagu = $program->activities->sum('total_pagu');
        });

        return Inertia::render('program/index', [
            'programs' => $programs,
            'auth' => ['user' => $user?->load('roles')]
        ]);
    }

    public function create()
    {
        return Inertia::render('program/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_program' => [
                'required',
                'string',
                'max:50',
                'unique:programs',
                'regex:/^\d+\.\d{2}\.\d{2}$/',
            ],
            'nama_program' => 'required|string|max:255',
            'tahun'        => 'required|integer|min:2000|max:2100',
        ], [
            'kode_program.regex' => 'Format kode program harus seperti 3.27.01 (angka.dua digit.dua digit)',
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
        $program->load([
            'activities.subActivities',
            'notes' => fn ($query) => $query->with(['creator', 'confirmer', 'resolver'])->latest(),
            'activities.notes' => fn ($query) => $query->with(['creator', 'confirmer', 'resolver'])->latest(),
            'activities.subActivities.notes' => fn ($query) => $query->with(['creator', 'confirmer', 'resolver'])->latest(),
        ]);

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
        if (! in_array($program->status, ['draft', 'ditolak'])) {
            return redirect()->back()->with('error', 'Program tidak dapat diubah');
        }

        $validated = $request->validate([
            'kode_program' => [
                'required',
                'string',
                'max:50',
                'unique:programs,kode_program,' . $program->id,
                'regex:/^\d+\.\d{2}\.\d{2}$/',
            ],
            'nama_program' => 'required|string|max:255',
            'tahun'        => 'required|integer|min:2000|max:2100',
        ], [
            'kode_program.regex' => 'Format kode program harus seperti 3.27.01 (angka.dua digit.dua digit)',
        ]);

        $program->update($validated);

        return redirect()->route('program.index')
            ->with('success', 'Program berhasil diupdate');
    }

    public function destroy(Program $program)
    {
        if (! in_array($program->status, ['draft', 'ditolak'])) {
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
        if (! in_array($program->status, ['draft', 'diajukan_ulang'])) {
            return back()->with('error', 'Hanya program draft atau yang diajukan ulang yang bisa disetujui rencana');
        }

        $program->update(['status' => 'verifikasi']);

        // Program baru saja disetujui rencananya — tutup semua catatan yang
        // sudah dikonfirmasi operator, dianggap selesai diperiksa Kabid.
        $program->revisionNotes()
            ->where('status', RevisionNote::STATUS_DIKONFIRMASI_OPERATOR)
            ->update([
                'status'      => RevisionNote::STATUS_SELESAI,
                'resolved_by' => Auth::id(),
                'resolved_at' => now(),
            ]);

        return back()->with('success', 'Rencana program berhasil disetujui');
    }

    public function tolak($id)
    {
        $program = Program::findOrFail($id);

        if (! in_array($program->status, ['draft', 'diajukan_ulang'])) {
            return back()->with('error', 'Program tidak dalam status yang bisa ditolak');
        }

        if (! $program->hasOpenRevisionNotes()) {
            return back()->with('error', 'Tambahkan minimal satu catatan perbaikan sebelum menolak program');
        }

        $program->update(['status' => 'ditolak']);

        return back()->with('success', 'Program ditolak dan dikembalikan ke operator untuk diperbaiki');
    }

    public function ajukan($id)
    {
        $program = Program::findOrFail($id);

        if ($program->status !== 'ditolak') {
            return back()->with('error', 'Hanya program berstatus ditolak yang bisa diajukan ulang');
        }

        if (! $program->canBeResubmitted()) {
            return back()->with('error', 'Masih ada catatan perbaikan yang belum dikonfirmasi');
        }

        $program->update(['status' => 'diajukan_ulang']);

        return back()->with('success', 'Program berhasil diajukan ulang untuk direview Kabid');
    }

    public function konfirmasi($id)
    {
        $program = Program::findOrFail($id);
        if ($program->status !== 'verifikasi') {
            return back()->with('error', 'Program belum disetujui rencana oleh Kabid');
        }

        $program->update([
            'status'         => 'disetujui',
            'disetujui_pada' => now(),
            'disetujui_oleh' => Auth::id(),
        ]);

        return back()->with('success', 'Input SIPD berhasil dikonfirmasi, program telah diarsipkan');
    }

    // === Auto-generate Kode Program ===
    public function nextCode(Request $request)
    {
        $tahun = $request->input('tahun');

        if (!$tahun) {
            return response()->json(['kode' => null]);
        }

        $jumlahProgram = Program::where('tahun', $tahun)->count();
        $nomorUrut = str_pad($jumlahProgram + 1, 2, '0', STR_PAD_LEFT);

        return response()->json([
            'kode' => "3.27.{$nomorUrut}",
        ]);
    }

    // === Laporan & Export ===
    public function ranwal(Request $request)
    {
        $query = Program::where('status', 'disetujui')
            ->with('activities.subActivities');

        if ($request->input('tahun')) {
            $query->where('tahun', $request->input('tahun'));
        }

        $programs = $query->get();

        return view('ranwal.print', compact('programs'));
    }

    public function exportExcel()
    {
        return Excel::download(new RanwalExport, 'ranwal.xlsx');
    }
}
