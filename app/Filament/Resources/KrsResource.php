<?php

namespace App\Filament\Resources;

use App\Enums\KrsStatusEnum;
use App\Enums\RoleEnum;
use App\Filament\Resources\KrsResource\Pages;
use App\Models\Kelas;
use App\Models\Krs;
use App\Services\EnrollmentService;
use App\Services\KrsApprovalService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class KrsResource extends Resource
{
    protected static ?string $model = Krs::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Kartu Rencana Studi (KRS)';

    protected static ?string $navigationLabel = 'KRS & Approval';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        if ($user->hasRole(RoleEnum::ADMIN)) {
            return $query;
        }

        if ($user->hasRole(RoleEnum::KAPRODI) && $user->dosen) {
            return $query->whereHas('mahasiswa', fn ($q) => $q->where('prodi_id', $user->dosen->prodi_id));
        }

        if ($user->hasRole(RoleEnum::DOSEN) && $user->dosen) {
            return $query->whereHas('mahasiswa', fn ($q) => $q->where('dosen_wali_id', $user->dosen->id));
        }

        if ($user->hasRole(RoleEnum::MAHASISWA) && $user->mahasiswa) {
            return $query->where('mahasiswa_id', $user->mahasiswa->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengajuan KRS')
                    ->schema([
                        Forms\Components\Select::make('mahasiswa_id')
                            ->label('Mahasiswa')
                            ->relationship('mahasiswa', 'nim')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nim} - {$record->user->name}")
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('semester_id')
                            ->label('Semester')
                            ->relationship('semester', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('total_sks')
                            ->label('Total SKS Diambil')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(0),

                        Forms\Components\Select::make('status')
                            ->label('Status KRS')
                            ->options(
                                collect(KrsStatusEnum::cases())
                                    ->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])
                                    ->all()
                            )
                            ->default(KrsStatusEnum::DRAFT->value)
                            ->required(),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Approval / Penolakan')
                            ->columnSpanFull()
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('mahasiswa.user.name')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('semester.nama')
                    ->label('Semester')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_sks')
                    ->label('Total SKS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->default('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(
                        collect(KrsStatusEnum::cases())
                            ->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])
                            ->all()
                    ),
                Tables\Filters\SelectFilter::make('semester_id')
                    ->label('Semester')
                    ->relationship('semester', 'nama'),
            ])
            ->actions([
                Tables\Actions\Action::make('tambahKelas')
                    ->label('Tambah Kelas')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('kelas_id')
                            ->label('Pilih Kelas Perkuliahan')
                            ->options(function (Krs $record) {
                                return Kelas::query()
                                    ->where('semester_id', $record->semester_id)
                                    ->with(['mataKuliah', 'jadwals'])
                                    ->get()
                                    ->mapWithKeys(function ($kelas) {
                                        $jadwalStr = $kelas->jadwals->map(fn ($j) => sprintf('%s %s-%s', ucfirst($j->hari->value ?? $j->hari), substr($j->jam_mulai, 0, 5), substr($j->jam_selesai, 0, 5)))->implode(', ');
                                        return [$kelas->id => sprintf('%s - %s (%d SKS) [%s]', $kelas->kode, $kelas->mataKuliah->nama, $kelas->mataKuliah->sks, $jadwalStr)];
                                    });
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Krs $record, array $data, EnrollmentService $enrollmentService) {
                        try {
                            $kelas = Kelas::findOrFail($data['kelas_id']);
                            $enrollmentService->addClassToKrs($record->mahasiswa, $record->semester, $kelas);

                            Notification::make()
                                ->title('Berhasil Memilih Kelas')
                                ->body("Kelas {$kelas->kode} berhasil ditambahkan ke KRS.")
                                ->success()
                                ->send();
                        } catch (ValidationException $e) {
                            $message = collect($e->errors())->flatten()->first() ?? $e->getMessage();

                            Notification::make()
                                ->title('Gagal Memilih Kelas')
                                ->body($message)
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Krs $record) => in_array($record->status->value ?? $record->status, [KrsStatusEnum::DRAFT->value, KrsStatusEnum::DITOLAK->value])),

                Tables\Actions\Action::make('approveKrs')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Krs $record, KrsApprovalService $approvalService) {
                        try {
                            $approvalService->approve($record, auth()->user());

                            Notification::make()
                                ->title('KRS Disetujui')
                                ->body('KRS mahasiswa berhasil disetujui.')
                                ->success()
                                ->send();
                        } catch (ValidationException $e) {
                            $message = collect($e->errors())->flatten()->first() ?? $e->getMessage();

                            Notification::make()
                                ->title('Persetujuan KRS Gagal')
                                ->body($message)
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Krs $record) => auth()->user()->can('approve', $record) && in_array($record->status->value ?? $record->status, [KrsStatusEnum::MENUNGGU->value, KrsStatusEnum::DRAFT->value])),

                Tables\Actions\Action::make('rejectKrs')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (Krs $record, array $data, KrsApprovalService $approvalService) {
                        $approvalService->reject($record, auth()->user(), $data['catatan']);

                        Notification::make()
                            ->title('KRS Ditolak')
                            ->body('Status KRS diubah menjadi Ditolak.')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (Krs $record) => auth()->user()->can('approve', $record) && in_array($record->status->value ?? $record->status, [KrsStatusEnum::MENUNGGU->value, KrsStatusEnum::DISETUJUI->value])),

                Tables\Actions\Action::make('cetakKrs')
                    ->label('Cetak KRS')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Krs $record) => route('reports.krs', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKrs::route('/'),
            'create' => Pages\CreateKrs::route('/create'),
            'edit' => Pages\EditKrs::route('/{record}/edit'),
        ];
    }
}
