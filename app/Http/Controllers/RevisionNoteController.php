<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Program;
use App\Models\RevisionNote;
use App\Models\SubActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevisionNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:kabid')->only(['store']);
        $this->middleware('role:operator')->only(['konfirmasi']);
    }

    /**
     * Kabid menambahkan catatan perbaikan pada Program, Kegiatan, atau Sub Kegiatan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_id'   => 'required|integer|exists:programs,id',
            'notable_type' => 'required|string|in:program,activity,sub_activity',
            'notable_id'   => 'required|integer',
            'catatan'      => 'required|string|max:2000',
        ]);

        $notableClass = match ($validated['notable_type']) {
            'program'      => Program::class,
            'activity'     => Activity::class,
            'sub_activity' => SubActivity::class,
        };

        // Pastikan item yang dituju benar-benar ada
        $notable = $notableClass::findOrFail($validated['notable_id']);

        RevisionNote::create([
            'program_id'   => $validated['program_id'],
            'notable_type' => $notableClass,
            'notable_id'   => $notable->id,
            'catatan'      => $validated['catatan'],
            'status'       => RevisionNote::STATUS_TERBUKA,
            'created_by'   => Auth::id(),
        ]);

        return back()->with('success', 'Catatan perbaikan berhasil ditambahkan');
    }

    /**
     * Operator mengonfirmasi bahwa item terkait catatan sudah diperbaiki.
     */
    public function konfirmasi(RevisionNote $revisionNote)
    {
        if ($revisionNote->status !== RevisionNote::STATUS_TERBUKA) {
            return back()->with('error', 'Catatan ini sudah dikonfirmasi sebelumnya');
        }

        $revisionNote->update([
            'status'       => RevisionNote::STATUS_DIKONFIRMASI_OPERATOR,
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
        ]);

        return back()->with('success', 'Catatan perbaikan dikonfirmasi sudah diperbaiki');
    }
}
