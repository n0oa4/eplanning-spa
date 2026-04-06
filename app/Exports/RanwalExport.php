<?php

namespace App\Exports;

use App\Models\Program;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RanwalExport implements FromView, \Maatwebsite\Excel\Concerns\WithStyles
{
    public function view(): View
    {
        $programs = Program::with('activities.subActivities')->get();

        return view('ranwal.excel', [
            'programs' => $programs
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // header bold
        ];
    }
}
