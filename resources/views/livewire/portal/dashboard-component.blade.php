<div>
    <!-- Profile & Header Banner Section -->
    <div style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.95)); border: 1px solid var(--card-border); border-radius: 24px; padding: 28px 32px; margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.6); position: relative; overflow: hidden;">
        <div style="position: absolute; right: -20px; top: -20px; font-size: 140px; opacity: 0.03; pointer-events: none;">🎓</div>
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <h1 style="font-size: 26px; font-weight: 800; color: #fff; letter-spacing: -0.5px;">Selamat Datang, {{ $user->name }}!</h1>
                <span class="badge badge-success" style="font-size: 11px;">Akun Aktif</span>
            </div>
            <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
                @if($mahasiswa)
                    NIM: <strong style="color: #fff;">{{ $mahasiswa->nim }}</strong> &bull; 
                    Prodi: <strong style="color: #38bdf8;">{{ $mahasiswa->prodi->nama }} ({{ $mahasiswa->prodi->jenjang->value }})</strong> &bull; 
                    Dosen Wali: <strong style="color: #fff;">{{ $mahasiswa->dosenWali->user->name ?? '-' }}</strong>
                @elseif($dosen)
                    NIDN: <strong style="color: #fff;">{{ $dosen->nidn ?? '-' }}</strong> &bull; 
                    Prodi: <strong style="color: #38bdf8;">{{ $dosen->prodi->nama }}</strong>
                @else
                    Akses Portal Akademik SIAKAD
                @endif
            </p>
        </div>
        <div>
            <div class="badge badge-info" style="font-size: 13px; padding: 8px 16px; border-radius: 12px; backdrop-filter: blur(8px);">
                🗓️ Semester Aktif: {{ $activeSemester->nama ?? 'Tidak Ada' }}
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if($errorMessage)
        <div class="alert alert-danger">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 20px;">⚠️</span>
                <div><strong>Validasi Aturan Bisnis / Error:</strong> {{ $errorMessage }}</div>
            </div>
            <button wire:click="$set('errorMessage', null)" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 16px;">✕</button>
        </div>
    @endif

    @if($successMessage)
        <div class="alert alert-success">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 20px;">🎉</span>
                <div><strong>Berhasil:</strong> {{ $successMessage }}</div>
            </div>
            <button wire:click="$set('successMessage', null)" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 16px;">✕</button>
        </div>
    @endif

    <!-- Stat Cards Grid (Mahasiswa) -->
    @if($mahasiswa)
        <div class="grid-3">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">IPK Kumulatif</span>
                    <div class="card-icon-box" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">🎓</div>
                </div>
                <div class="card-value" style="color: #38bdf8;">{{ number_format($ipk, 2) }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 8px; font-weight: 500;">Calculated Realtime (R9/R10)</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="card-title">Batas SKS Maksimum (R1)</span>
                    <div class="card-icon-box" style="background: rgba(168, 85, 247, 0.15); color: #c084fc;">📊</div>
                </div>
                <div class="card-value" style="color: #c084fc;">{{ $maxSks }} <span style="font-size: 18px; font-weight: 600;">SKS</span></div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 8px; font-weight: 500;">Berdasarkan Tier IPS Lalu</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="card-title">Status UKT (R6 Gate)</span>
                    <div class="card-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">💳</div>
                </div>
                <div class="card-value" style="font-size: 24px; color: {{ $statusUkt === 'Lunas' ? '#34d399' : '#f87171' }};">
                    {{ $statusUkt }}
                </div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 8px; font-weight: 500;">Status Pembayaran UKT</div>
            </div>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="tabs">
        @if($mahasiswa)
            <button class="tab-btn {{ $activeTab === 'krs' ? 'active' : '' }}" wire:click="switchTab('krs')">📝 Pengisian & Status KRS</button>
            <button class="tab-btn {{ $activeTab === 'khs' ? 'active' : '' }}" wire:click="switchTab('khs')">📊 Hasil Studi (KHS & Transkrip)</button>
        @endif

        @if($dosen)
            <button class="tab-btn {{ $activeTab === 'dosen' ? 'active' : '' }}" wire:click="switchTab('dosen')">👨‍🏫 Input Nilai Kelas Ampuan</button>
        @endif
    </div>

    <!-- TAB 1: KRS (UNTUK MAHASISWA) -->
    @if($activeTab === 'krs' && $mahasiswa)
        <div style="margin-bottom: 32px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                <h3 style="font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">Daftar Kelas Ditawarkan (Semester {{ $activeSemester->nama ?? '-' }})</h3>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 14px; color: var(--text-muted);">Status KRS:</span>
                    <span class="badge {{ $krsAktif && $krsAktif->status->value === 'disetujui' ? 'badge-success' : ($krsAktif && $krsAktif->status->value === 'menunggu' ? 'badge-warning' : 'badge-info') }}" style="font-size: 13px; padding: 6px 14px;">
                        {{ $krsAktif ? strtoupper($krsAktif->status->value) : 'DRAFT (BELUM ADA)' }}
                    </span>
                </div>
            </div>

            <!-- Tabel Ditawarkan -->
            <div class="table-responsive" style="margin-bottom: 36px;">
                <table>
                    <thead>
                        <tr>
                            <th>Kode Kelas</th>
                            <th>Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Dosen Pengampu</th>
                            <th>Jadwal Kuliah</th>
                            <th>Kapasitas</th>
                            <th>Aksi Pengambilan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kelasDitawarkan as $kelas)
                            @php
                                $isTaken = $krsAktif && $krsAktif->details->where('kelas_id', $kelas->id)->where('status.value', 'diambil')->first();
                            @endphp
                            <tr>
                                <td><strong style="color: #38bdf8; font-family: monospace; font-size: 15px;">{{ $kelas->kode }}</strong></td>
                                <td><strong style="color: #fff;">{{ $kelas->mataKuliah->nama }}</strong></td>
                                <td><span class="badge badge-info">{{ $kelas->mataKuliah->sks }} SKS</span></td>
                                <td>{{ $kelas->dosen->user->name ?? '-' }}</td>
                                <td>
                                    @foreach($kelas->jadwals as $j)
                                        <div style="font-size: 13px; color: #cbd5e1;">
                                            🕒 {{ ucfirst($j->hari->value) }}, {{ substr($j->jam_mulai,0,5) }}-{{ substr($j->jam_selesai,0,5) }} <span style="color: var(--text-muted);">({{ $kelas->ruang }})</span>
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    <span style="font-weight: 700; color: {{ $kelas->krsDetails->where('status.value', 'diambil')->count() >= $kelas->kapasitas ? '#f87171' : '#34d399' }};">
                                        {{ $kelas->krsDetails->where('status.value', 'diambil')->count() }} / {{ $kelas->kapasitas }}
                                    </span>
                                </td>
                                <td>
                                    @if($isTaken)
                                        <span class="badge badge-success">✓ Sudah Diambil</span>
                                    @else
                                        <button wire:click="ambilKelas({{ $kelas->id }})" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">
                                            + Ambil Kelas
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">Tidak ada kelas ditawarkan untuk semester ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Tabel KRS Yang Diambil -->
            <div class="card" style="padding: 28px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h3 style="font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">Rincian KRS Yang Diambil</h3>
                        <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Daftar seksi kelas yang sudah masuk ke draf Rencana Studi Anda</p>
                    </div>
                    <div style="font-size: 18px; font-weight: 800; color: #38bdf8; background: rgba(56, 189, 248, 0.1); padding: 8px 18px; border-radius: 12px; border: 1px solid rgba(56, 189, 248, 0.3);">
                        Total SKS: {{ $krsAktif ? $krsAktif->total_sks : 0 }} / {{ $maxSks }} SKS
                    </div>
                </div>

                <div class="table-responsive" style="margin-bottom: 24px;">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Kelas</th>
                                <th>Mata Kuliah</th>
                                <th>SKS</th>
                                <th>Jadwal Perkuliahan</th>
                                <th>Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($krsAktif && $krsAktif->details->where('status.value', 'diambil')->count() > 0)
                                @foreach($krsAktif->details->where('status.value', 'diambil') as $index => $detail)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong style="color: #38bdf8; font-family: monospace;">{{ $detail->kelas->kode }}</strong></td>
                                        <td><strong style="color: #fff;">{{ $detail->kelas->mataKuliah->nama }}</strong></td>
                                        <td><span class="badge badge-info">{{ $detail->kelas->mataKuliah->sks }} SKS</span></td>
                                        <td>
                                            @foreach($detail->kelas->jadwals as $j)
                                                🕒 {{ ucfirst($j->hari->value) }} {{ substr($j->jam_mulai,0,5) }}-{{ substr($j->jam_selesai,0,5) }}<br>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if(in_array($krsAktif->status->value, ['draft', 'ditolak']))
                                                <button wire:click="batalkanKelas({{ $detail->id }})" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">
                                                    🗑️ Batal
                                                </button>
                                            @else
                                                <span class="badge badge-info">🔒 Termasuk KRS Final</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 32px;">Belum ada kelas yang dimasukkan ke KRS.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if($krsAktif && in_array($krsAktif->status->value, ['draft', 'ditolak']) && $krsAktif->total_sks > 0)
                    <div style="text-align: right;">
                        <button wire:click="ajukanKrs({{ $krsAktif->id }})" class="btn btn-success" style="padding: 14px 28px; font-size: 15px;">
                            🚀 Ajukan KRS ke Dosen Wali & Kaprodi
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- TAB 2: KHS & TRANSKRIP (UNTUK MAHASISWA) -->
    @if($activeTab === 'khs' && $mahasiswa)
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h3 style="font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">Kartu Hasil Studi (KHS) Per Semester</h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Pilih semester akademik untuk melihat rekapitulasi nilai dan IPS</p>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label style="font-size: 14px; font-weight: 600; color: var(--text-muted);">Semester:</label>
                    <select wire:model.live="selectedSemesterId" style="background-color: #0f172a; color: #fff; border: 1px solid var(--card-border); padding: 10px 16px; border-radius: 12px; font-weight: 600;">
                        @foreach($allSemesters as $sem)
                            <option value="{{ $sem->id }}">{{ $sem->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="card" style="padding: 28px; margin-bottom: 28px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Indeks Prestasi Semester (IPS)</div>
                        <div style="font-size: 36px; font-weight: 800; color: #38bdf8; letter-spacing: -1px; margin-top: 4px;">{{ number_format($khsIps, 2) }}</div>
                    </div>
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <a href="{{ route('reports.khs', ['mahasiswa' => $mahasiswa->id, 'semester' => $selectedSemesterId ?? $activeSemester?->id]) }}" target="_blank" class="btn btn-primary">
                            🖨️ Cetak KHS PDF
                        </a>
                        <a href="{{ route('reports.transkrip', $mahasiswa->id) }}" target="_blank" class="btn btn-success">
                            🎓 Cetak Transkrip Kumulatif
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode MK</th>
                                <th>Mata Kuliah</th>
                                <th>SKS</th>
                                <th>Nilai Huruf</th>
                                <th>Bobot</th>
                                <th>Status Kelulusan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($khsNilais as $index => $nilai)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong style="color: #38bdf8; font-family: monospace;">{{ $nilai->krsDetail->kelas->mataKuliah->kode ?? '-' }}</strong></td>
                                    <td><strong style="color: #fff;">{{ $nilai->krsDetail->kelas->mataKuliah->nama ?? '-' }}</strong></td>
                                    <td><span class="badge badge-info">{{ $nilai->sks }} SKS</span></td>
                                    <td><span class="badge badge-info" style="font-size: 14px; font-weight: 800; padding: 6px 14px;">{{ $nilai->nilai_huruf->value ?? $nilai->nilai_huruf }}</span></td>
                                    <td><strong style="color: #fff;">{{ number_format($nilai->bobot, 2) }}</strong></td>
                                    <td>
                                        @if($nilai->lulus)
                                            <span class="badge badge-success">✓ LULUS</span>
                                        @else
                                            <span class="badge badge-danger">✗ TIDAK LULUS</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">Belum ada nilai terdaftar untuk semester yang dipilih.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- TAB 3: DOSEN GRADING (UNTUK DOSEN) -->
    @if($activeTab === 'dosen' && $dosen)
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h3 style="font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">Input Nilai Mahasiswa (Kelas Ampuan Dosen)</h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Kelola nilai akhir mahasiswa pada seksi kelas yang Anda ampu</p>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label style="font-size: 14px; font-weight: 600; color: var(--text-muted);">Pilih Kelas Ampuan:</label>
                    <select wire:model.live="selectedKelasId" style="background-color: #0f172a; color: #fff; border: 1px solid var(--card-border); padding: 10px 16px; border-radius: 12px; font-weight: 600;">
                        @foreach($dosenKelasList as $kls)
                            <option value="{{ $kls->id }}">{{ $kls->kode }} - {{ $kls->mataKuliah->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="card" style="padding: 28px;">
                <div class="table-responsive" style="margin-bottom: 24px;">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Mata Kuliah</th>
                                <th>Nilai Saat Ini</th>
                                <th>Input / Update Nilai Baru</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pesertaKelasList as $index => $peserta)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong style="color: #38bdf8; font-family: monospace;">{{ $peserta->krs->mahasiswa->nim }}</strong></td>
                                    <td><strong style="color: #fff;">{{ $peserta->krs->mahasiswa->user->name }}</strong></td>
                                    <td>{{ $peserta->kelas->mataKuliah->nama }} <span class="badge badge-info">{{ $peserta->kelas->mataKuliah->sks }} SKS</span></td>
                                    <td>
                                        @if($peserta->nilai)
                                            <span class="badge badge-success" style="font-size: 13px;">{{ $peserta->nilai->nilai_huruf->value ?? $peserta->nilai->nilai_huruf }} (Bobot: {{ number_format($peserta->nilai->bobot, 2) }})</span>
                                        @else
                                            <span class="badge badge-warning">Belum Ada Nilai</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select wire:model="inputNilai.{{ $peserta->id }}" style="background-color: #0f172a; color: #fff; border: 1px solid var(--card-border); padding: 8px 14px; border-radius: 8px; font-weight: 600;">
                                            <option value="">-- Pilih Nilai --</option>
                                            <option value="A">A (4.00 - Sangat Baik)</option>
                                            <option value="A-">A- (3.70)</option>
                                            <option value="B+">B+ (3.30)</option>
                                            <option value="B">B (3.00 - Baik)</option>
                                            <option value="B-">B- (2.70)</option>
                                            <option value="C+">C+ (2.30)</option>
                                            <option value="C">C (2.00 - Cukup)</option>
                                            <option value="D">D (1.00 - Tidak Lulus)</option>
                                            <option value="E">E (0.00 - Tidak Lulus)</option>
                                        </select>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 32px;">Belum ada peserta mahasiswa terdaftar di kelas ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($pesertaKelasList->count() > 0)
                    <div style="text-align: right;">
                        <button wire:click="simpanNilaiDosen" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px;">
                            💾 Simpan Perubahan Nilai
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
