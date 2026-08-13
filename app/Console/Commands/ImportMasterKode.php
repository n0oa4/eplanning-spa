<?php

namespace App\Console\Commands;

use App\Models\MasterActivity;
use App\Models\MasterProgram;
use App\Models\MasterSubActivity;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportMasterKode extends Command
{
    /**
     * Contoh pemakaian:
     * php artisan import:master-kode storage/app/master-kode/RANWAL_2027.xlsx
     * php artisan import:master-kode storage/app/master-kode/RANWAL_2027.xlsx --sheet="RENJA"
     */
    protected $signature = 'import:master-kode {file : Path ke file Excel RENJA/Ranwal} {--sheet=RENJA : Nama sheet yang dibaca}';

    protected $description = 'Import data master Program/Kegiatan/Sub Kegiatan (kode, nama, indikator, target, pagu, dll) dari file Excel RENJA';

    public function handle(): int
    {
        $path = $this->argument('file');
        $sheetName = $this->option('sheet');

        if (! file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $spreadsheet = IOFactory::load($path);

        if (! $spreadsheet->sheetNameExists($sheetName)) {
            $this->error("Sheet '{$sheetName}' tidak ditemukan di file ini.");

            return self::FAILURE;
        }

        $sheet = $spreadsheet->getSheetByName($sheetName);
        $highestRow = $sheet->getHighestRow();

        $countProgram = 0;
        $countKegiatan = 0;
        $countSub = 0;
        $failures = [];

        // Data mulai dari baris 9 — baris 1-8 adalah judul dokumen, header kolom,
        // dan baris legenda nomor kolom (1,2,3,...). Baris-baris itu otomatis
        // terlewati karena kolom B-F-nya tidak memenuhi pola kode manapun di bawah.
        for ($row = 9; $row <= $highestRow; $row++) {
            $b = trim((string) $sheet->getCell("B{$row}")->getCalculatedValue());
            $c = trim((string) $sheet->getCell("C{$row}")->getCalculatedValue());
            $d = trim((string) $sheet->getCell("D{$row}")->getCalculatedValue());
            $e = trim((string) $sheet->getCell("E{$row}")->getCalculatedValue());
            $f = trim((string) $sheet->getCell("F{$row}")->getCalculatedValue());
            $g = trim((string) $sheet->getCell("G{$row}")->getCalculatedValue());

            try {
                if ($d !== '' && $e === '' && $b !== '' && $c !== '') {
                    // Baris Program: kolom B,C,D terisi, E masih kosong
                    $kodeProgram = "{$b}.{$c}.{$d}";

                    MasterProgram::updateOrCreate(
                        ['kode_program' => $kodeProgram],
                        ['nama_program' => $g]
                    );
                    $countProgram++;
                } elseif ($e !== '' && $f === '') {
                    // Baris Kegiatan: kolom E terisi, F masih kosong
                    // Induknya (master_programs) wajib sudah ada duluan — kalau tidak, FK akan menolak.
                    $kodeKegiatan = "{$b}.{$c}.{$d}.{$e}";
                    $kodeProgram = "{$b}.{$c}.{$d}";

                    MasterActivity::updateOrCreate(
                        ['kode_kegiatan' => $kodeKegiatan],
                        [
                            'kode_program'  => $kodeProgram,
                            'nama_kegiatan' => $g,
                        ]
                    );
                    $countKegiatan++;
                } elseif ($f !== '') {
                    // Baris Sub Kegiatan: kode + nama di baris ini,
                    // detail (indikator/target/pagu/dll) ada di baris berikutnya.
                    // Induknya (master_activities) wajib sudah ada duluan — kalau tidak, FK akan menolak.
                    $kodeSub = "{$b}.{$c}.{$d}.{$e}.{$f}";
                    $kodeKegiatan = "{$b}.{$c}.{$d}.{$e}";

                    $detailRow = $row + 1;
                    $indikator = trim((string) $sheet->getCell("H{$detailRow}")->getCalculatedValue());
                    $prioritasProvinsi = trim((string) $sheet->getCell("J{$detailRow}")->getCalculatedValue());
                    $prioritasKabupaten = trim((string) $sheet->getCell("K{$detailRow}")->getCalculatedValue());
                    $bidangUrusan = trim((string) $sheet->getCell("L{$detailRow}")->getCalculatedValue());
                    $target = trim((string) $sheet->getCell("M{$detailRow}")->getCalculatedValue());
                    $pagu = $sheet->getCell("N{$detailRow}")->getCalculatedValue();
                    $n1 = $sheet->getCell("O{$detailRow}")->getCalculatedValue();
                    $n2 = $sheet->getCell("P{$detailRow}")->getCalculatedValue();

                    MasterSubActivity::updateOrCreate(
                        ['kode_sub_kegiatan' => $kodeSub],
                        [
                            'kode_kegiatan'       => $kodeKegiatan,
                            'nama_sub_kegiatan'   => $g,
                            'indikator'           => $indikator !== '' ? $indikator : null,
                            'target'              => $target !== '' ? $target : null,
                            'prioritas_provinsi'  => $prioritasProvinsi !== '' ? $prioritasProvinsi : null,
                            'prioritas_kabupaten' => $prioritasKabupaten !== '' ? $prioritasKabupaten : null,
                            'bidang_urusan'       => $bidangUrusan !== '' ? $bidangUrusan : null,
                            'pagu_anggaran'       => is_numeric($pagu) ? $pagu : null,
                            'n1'                  => is_numeric($n1) ? $n1 : null,
                            'n2'                  => is_numeric($n2) ? $n2 : null,
                        ]
                    );
                    $countSub++;
                }
            } catch (QueryException $e) {
                // Kemungkinan besar pelanggaran foreign key (induk belum/tidak ada).
                // Baris ini dilewati, import tetap lanjut ke baris berikutnya.
                $failures[] = "Baris {$row} (kode: " . ($kodeSub ?? $kodeKegiatan ?? $kodeProgram ?? '-') . '): ' . $e->getMessage();
            }
        }

        $this->info("Import selesai. Program: {$countProgram}, Kegiatan: {$countKegiatan}, Sub Kegiatan: {$countSub}");
        $this->comment('Catatan: command ini upsert (update kalau kode sudah ada, tambah kalau baru). Kode yang dihapus dari revisi Excel baru TIDAK otomatis terhapus dari tabel master.');

        if (! empty($failures)) {
            $this->warn(count($failures) . ' baris gagal diimport (kemungkinan induknya belum masuk / urutan Excel tidak berurutan):');
            foreach ($failures as $failure) {
                $this->line("  - {$failure}");
            }
        }

        return self::SUCCESS;
    }
}
