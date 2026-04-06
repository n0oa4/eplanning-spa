    <meta charset="utf-8">
    <title>RANWAL RENJA</title>

    <style>

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


    </style>

    {{-- HEADER --}}
    <div class="text-center bold">
        <div>RENCANA KERJA ORGANISASI PERANGKAT DAERAH (RENJA OPD)</div>
        <div>DINAS TANAMAN PANGAN, HORTIKULTURA DAN PERKEBUNAN</div>
        <div>KABUPATEN BANGGAI</div>
        <div>TAHUN 2026</div>
    </div>



    {{-- TABLE --}}
    <table border="1" style="border-collapse: collapse;">
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
                <td></td>
                <td class="bold">URUSAN PEMERINTAHAN PILIHAN</td>
                <td colspan="9"></td>
            </tr>

            <tr>
                <td></td>
                <td class="bold">URUSAN PEMERINTAHAN BIDANG PERTANIAN</td>
                <td colspan="9"></td>
            </tr>

            @foreach($programs as $program)

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
                        <td>{{ $sub->skpd }}</td>
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

        @endforeach

        </tbody>
    </table>
