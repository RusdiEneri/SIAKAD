<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Rencana Studi (KRS) - {{ $krs->mahasiswa->nim }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', Arial, sans-serif; box-sizing: border-box; }
        body { font-size: 13px; color: #1e293b; margin: 40px; background-color: #fff; }
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px double #0f172a; padding-bottom: 16px; margin-bottom: 24px; }
        .header-title h2 { margin: 0; font-size: 20px; font-weight: 800; text-transform: uppercase; color: #0f172a; letter-spacing: 0.5px; }
        .header-title p { margin: 4px 0 0 0; color: #64748b; font-weight: 600; font-size: 12px; }
        .header-logo { width: 50px; height: 50px; border-radius: 12px; background: #4f46e5; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; }
        .meta-table { width: 100%; margin-bottom: 24px; border-collapse: collapse; background: #f8fafc; border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0; }
        .meta-table td { padding: 8px 14px; vertical-align: top; font-size: 13px; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .data-table th { background-color: #0f172a; color: #fff; padding: 10px 14px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; }
        .data-table td { border-bottom: 1px solid #e2e8f0; padding: 12px 14px; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .footer-table { width: 100%; margin-top: 40px; }
        .footer-table td { text-align: center; width: 50%; vertical-align: top; }
        .signature-space { height: 70px; }
        .btn-print { padding: 10px 20px; background-color: #4f46e5; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 13px; transition: background 0.2s; }
        .btn-print:hover { background-color: #4338ca; }
        @media print { .no-print { display: none; } body { margin: 20px; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 24px; text-align: right;">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Dokumen KRS</button>
    </div>

    <div class="header">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div class="header-logo">🎓</div>
            <div class="header-title">
                <h2>PERGURUAN TINGGI SIAKAD UTAMA</h2>
                <p>KARTU RENCANA STUDI (KRS) SEMESTER ACADEMIC</p>
            </div>
        </div>
        <div style="text-align: right;">
            <span style="background: #e0e7ff; color: #3730a3; padding: 6px 14px; border-radius: 20px; font-weight: 700; font-size: 12px;">DOKUMEN RESMI</span>
        </div>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>NIM</strong></td>
            <td width="35%">: <strong>{{ $krs->mahasiswa->nim }}</strong></td>
            <td width="15%"><strong>Semester</strong></td>
            <td width="35%">: {{ $krs->semester->nama }}</td>
        </tr>
        <tr>
            <td><strong>Nama Mahasiswa</strong></td>
            <td>: {{ $krs->mahasiswa->user->name }}</td>
            <td><strong>Tahun Akademik</strong></td>
            <td>: {{ $krs->semester->tahun }}</td>
        </tr>
        <tr>
            <td><strong>Program Studi</strong></td>
            <td>: {{ $krs->mahasiswa->prodi->nama }} ({{ $krs->mahasiswa->prodi->jenjang->value }})</td>
            <td><strong>Status KRS</strong></td>
            <td>: <strong style="color: #16a34a;">{{ strtoupper($krs->status->value) }}</strong></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Kelas</th>
                <th>Mata Kuliah</th>
                <th width="10%" style="text-align: center;">SKS</th>
                <th width="22%">Dosen Pengampu</th>
                <th width="22%">Jadwal Kuliah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($krs->details as $index => $detail)
                <tr>
                    <td style="text-align: center; font-weight: 600;">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: 700; color: #4f46e5;">{{ $detail->kelas->kode }}</td>
                    <td><strong>{{ $detail->kelas->mataKuliah->nama }}</strong></td>
                    <td style="text-align: center; font-weight: 700;">{{ $detail->kelas->mataKuliah->sks }}</td>
                    <td>{{ $detail->kelas->dosen->user->name ?? '-' }}</td>
                    <td>
                        @foreach($detail->kelas->jadwals as $j)
                            {{ ucfirst($j->hari->value) }}, {{ substr($j->jam_mulai,0,5) }}-{{ substr($j->jam_selesai,0,5) }}<br>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 24px;">Belum ada kelas yang diambil.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9;">
                <th colspan="3" style="text-align: right; color: #0f172a; background: none;">Total SKS Diambil:</th>
                <th style="text-align: center; color: #4f46e5; background: none; font-size: 15px;">{{ $krs->total_sks }}</th>
                <th colspan="2" style="background: none;"></th>
            </tr>
        </tfoot>
    </table>

    <table class="footer-table">
        <tr>
            <td>
                <p>Mahasiswa Yang Bersangkutan,</p>
                <div class="signature-space"></div>
                <p><strong><u>{{ $krs->mahasiswa->user->name }}</u></strong><br>NIM. {{ $krs->mahasiswa->nim }}</p>
            </td>
            <td>
                <p>Disetujui Oleh Dosen Wali / Kaprodi,</p>
                <div class="signature-space"></div>
                <p><strong><u>{{ $krs->approver->name ?? $krs->mahasiswa->dosenWali->user->name ?? 'Staf Akademik' }}</u></strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
