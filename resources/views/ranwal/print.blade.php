<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RANWAL RENJA</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid black;
            padding: 4px;
            vertical-align: middle;
        }

        th {
            background-color: #f0f0f0;
        }

        .bold { font-weight: bold; }

        /* === Perbaikan tampilan saat print/save as PDF === */

        /* Cegah satu baris tabel terpotong di tengah saat ganti halaman */
        tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Ulangi header tabel (Kode, Uraian, dst) di setiap halaman baru */
        thead {
            display: table-header-group;
        }

        /* Cegah baris terakhir tabel di suatu halaman "menggantung" sendirian
           tanpa baris berikutnya yang biasanya menyertainya (misal Program tanpa Kegiatan-nya) */
        tbody tr {
            page-break-after: auto;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="text-center bold">
        <div>RENCANA KERJA ORGANISASI PERANGKAT DAERAH (RENJA OPD)</div>
        <div>DINAS TANAMAN PANGAN, HORTIKULTURA DAN PERKEBUNAN</div>
        <div>KABUPATEN BANGGAI</div>
        <div>TAHUN {{ request('tahun') ?? date('Y') }}</div>
    </div>



    {{-- TABLE --}}
    <table>
        <thead>
            <tr>
                <th rowspan="2">Kode</th>
                <th rowspan="2">Uraian</th>
                <th rowspan="2">Indikator</th>
                <th rowspan="2">SKPD</th>
                <th rowspan="2">Prioritas Provinsi</th>
                <th rowspan="2">Prioritas Kabupaten</th>
                <th rowspan="2">Bidang Urusan</th>
                <th colspan="4">Capaian Kinerja dan Kerangka Pendanaan</th>
            </tr>
            <tr>
                <th>Target</th>
                <th>Pagu (Rp)</th>
                <th>N-1</th>
                <th>N-2</th>
            </tr>
        </thead>

        <tbody>

            {{-- URUSAN --}}
            <tr>
                <td class="bold">3</td>
                <td class="bold">URUSAN PEMERINTAHAN PILIHAN</td>
                <td colspan="9"></td>
            </tr>

            <tr>
                <td class="bold">3.27</td>
                <td class="bold">URUSAN PEMERINTAHAN BIDANG PERTANIAN</td>
                <td colspan="9"></td>
            </tr>

            @forelse($programs as $program)

            {{-- PROGRAM --}}
            <tr>
                <td class="bold">{{ $program->kode_program }}</td>
                <td class="bold">{{ $program->nama_program }}</td>
                <td colspan="9"></td>
            </tr>

            @foreach($program->activities as $kegiatan)

                {{-- KEGIATAN --}}
                <tr>
                    <td>{{ $kegiatan->kode_kegiatan }}</td>
                    <td>{{ $kegiatan->nama_kegiatan }}</td>
                    <td colspan="9"></td>
                </tr>

                @foreach($kegiatan->subActivities as $sub)

                    {{-- SUB KEGIATAN --}}
                    <tr>
                        <td>{{ $sub->kode_sub_kegiatan }}</td>
                        <td>{{ $sub->nama_sub_kegiatan }}</td>
                        <td>{{ $sub->indikator }}</td>
                        <td>Dinas Tanaman Pangan, Hortikultura dan Perkebunan</td>
                        <td>{{ $sub->prioritas_provinsi }}</td>
                        <td>{{ $sub->prioritas_kabupaten }}</td>
                        <td>{{ $sub->bidang_urusan }}</td>
                        <td>{{ $sub->target }}</td>
                        <td class="text-right">
                            {{ number_format($sub->pagu_anggaran, 2, ',', '.') }}
                        </td>
                        <td class="text-right">
                            {{ number_format($sub->n1, 2, ',', '.') }}
                        </td>
                        <td class="text-right">
                            {{ number_format($sub->n2, 2, ',', '.') }}
                        </td>
                    </tr>

                @endforeach

            @endforeach

            @empty
                <tr>
                    <td colspan="11" class="text-center">Tidak ada data program yang disetujui</td>
                </tr>
            @endforelse

        </tbody>
    </table>

</body>
</html>
