<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Hasil Studi (KHS) - {{ $mahasiswa->nim }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', Arial, sans-serif; box-sizing: border-box; }
        body { font-size: 13px; color: #1e293b; margin: 40px; background-color: #fff; }
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px double #0f172a; padding-bottom: 16px; margin-bottom: 24px; }
        .header-title h2 { margin: 0; font-size: 20px; font-weight: 800; text-transform: uppercase; color: #0f172a; letter-spacing: 0.5px; }
        .header-title p { margin: 4px 0 0 0; color: #64748b; font-weight: 600; font-size: 12px; }
        .header-logo { width: 50px; height: 50px; border-radius: 12px; background: #0284c7; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; }
        .meta-table { width: 100%; margin-bottom: 24px; border-collapse: collapse; background: #f8fafc; border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0; }
        .meta-table td { padding: 8px 14px; vertical-align: top; font-size: 13px; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .data-table th { background-color: #0f172a; color: #fff; padding: 10px 14px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; }
        .data-table td { border-bottom: 1px solid #e2e8f0; padding: 12px 14px; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .footer-table { width: 100%; margin-top: 40px; }
        .footer-table td { text-align: center; width: 50%; vertical-align: top; }
        .signature-space { height: 70px; }
        .btn-print { padding: 10px 20px; background-color: #0284c7; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 13px; transition: background 0.2s; }
        .btn-print:hover { background-color: #0369a1; }
        @media print { .no-print { display: none; } body { margin: 20px; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 24px; text-align: right;">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Dokumen KHS</button>
    </div>

    <div class="header">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div class="header-logo">📊</div>
            <div class="header-title">
                <h2>PERGURUAN TINGGI SIAKAD UTAMA</h2>
                <p>KARTU HASIL STUDI (KHS) ACADEMIC TRANSCRIPT</p>
            </div>
        </div>
        <div style="text-align: right;">
            <span style="background: #e0f2fe; color: #0369a1; padding: 6px 14px; border-radius: 20px; font-weight: 700; font-size: 12px;">OFFICIAL KHS</span>
        </div>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>NIM</strong></td>
            <td width="35%">: <strong>{{ $mahasiswa->nim }}</strong></td>
            <td width="15%"><strong>Semester</strong></td>
            <td width="35%">: {{ $semester->nama }}</td>
        </tr>
        <tr>
            <td><strong>Nama Mahasiswa</strong></td>
            <td>: {{ $mahasiswa->user->name }}</td>
            <td><strong>IPS Semester Ini</strong></td>
            <td>: <strong style="font-size: 16px; color: #0284c7;">{{ number_format($ips, 2) }}</strong></td>
        </tr>
        <tr>
            <td><strong>Program Studi</strong></td>
            <td>: {{ $mahasiswa->prodi->nama }} ({{ $mahasiswa->prodi->jenjang->value }})</td>
            <td><strong>IPK Kumulatif</strong></td>
            <td>: <strong style="font-size: 16px; color: #16a34a;">{{ number_format($ipk, 2) }}</strong></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode MK</th>
                <th>Mata Kuliah</th>
                <th width="10%" style="text-align: center;">SKS</th>
                <th width="12%" style="text-align: center;">Nilai Huruf</th>
                <th width="12%" style="text-align: center;">Bobot</th>
                <th width="15%" style="text-align: center;">Mutu (Bobot x SKS)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalSks = 0; $totalMutu = 0; @endphp
            @forelse($nilais as $index => $nilai)
                @php
                    $mutu = $nilai->bobot * $nilai->sks;
                    $totalSks += $nilai->sks;
                    $totalMutu += $mutu;
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: 600;">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: 700; color: #0284c7;">{{ $nilai->krsDetail->kelas->mataKuliah->kode ?? '-' }}</td>
                    <td><strong>{{ $nilai->krsDetail->kelas->mataKuliah->nama ?? '-' }}</strong></td>
                    <td style="text-align: center; font-weight: 700;">{{ $nilai->sks }}</td>
                    <td style="text-align: center;"><span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-weight: 800;">{{ $nilai->nilai_huruf->value ?? $nilai->nilai_huruf }}</span></td>
                    <td style="text-align: center; font-weight: 600;">{{ number_format($nilai->bobot, 2) }}</td>
                    <td style="text-align: center; font-weight: 700; color: #0f172a;">{{ number_format($mutu, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 24px;">Belum ada nilai yang diinput pada semester ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9;">
                <th colspan="3" style="text-align: right; color: #0f172a; background: none;">Total SKS & Mutu:</th>
                <th style="text-align: center; color: #0284c7; background: none; font-size: 15px;">{{ $totalSks }}</th>
                <th colspan="2" style="background: none;"></th>
                <th style="text-align: center; color: #16a34a; background: none; font-size: 15px;">{{ number_format($totalMutu, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <table class="footer-table">
        <tr>
            <td>
                <p>Ketua Program Studi,</p>
                <div class="signature-space"></div>
                <p><strong><u>{{ $mahasiswa->prodi->nama }}</u></strong></p>
            </td>
            <td>
                <p>Dosen Wali,</p>
                <div class="signature-space"></div>
                <p><strong><u>{{ $mahasiswa->dosenWali->user->name ?? 'Staf Akademik' }}</u></strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
