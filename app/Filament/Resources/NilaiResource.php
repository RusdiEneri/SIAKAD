<?php

namespace App\Filament\Resources;

use App\Enums\NilaiHurufEnum;
use App\Enums\RoleEnum;
use App\Filament\Resources\NilaiResource\Pages;
use App\Models\KrsDetail;
use App\Models\Nilai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NilaiResource extends Resource
{
    protected static ?string $model = Nilai::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationGroup = 'Transkrip & Nilai';

    protected static ?string $navigationLabel = 'Input & Rekap Nilai';

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
            return $query->whereHas('krsDetail.kelas.mataKuliah', fn ($q) => $q->where('prodi_id', $user->dosen->prodi_id));
        }

        if ($user->hasRole(RoleEnum::DOSEN) && $user->dosen) {
            return $query->whereHas('krsDetail.kelas', fn ($q) => $q->where('dosen_id', $user->dosen->id));
        }

        if ($user->hasRole(RoleEnum::MAHASISWA) && $user->mahasiswa) {
            return $query->whereHas('krsDetail.krs', fn ($q) => $q->where('mahasiswa_id', $user->mahasiswa->id));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Input Nilai Mahasiswa')
                    ->schema([
                        Forms\Components\Select::make('krs_detail_id')
                            ->label('Peserta Kelas (KRS Detail)')
                            ->relationship('krsDetail', 'id')
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                $nim = $record->krs->mahasiswa->nim ?? '-';
                                $nama = $record->krs->mahasiswa->user->name ?? '-';
                                $kelas = $record->kelas->kode ?? '-';
                                $mk = $record->kelas->mataKuliah->nama ?? '-';
                                return "{$nim} - {$nama} [Kelas: {$kelas} - {$mk}]";
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $detail = KrsDetail::find($state);
                                    if ($detail) {
                                        $set('sks', $detail->kelas->mataKuliah->sks ?? 0);
                                        $set('tahun_akademik', $detail->kelas->semester->nama ?? '2025/2026');
                                    }
                                }
                            })
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('nilai_huruf')
                            ->label('Nilai Huruf')
                            ->options(
                                collect(NilaiHurufEnum::cases())
                                    ->mapWithKeys(fn ($enum) => [$enum->value => "{$enum->value} (Bobot: " . number_format($enum->bobot(), 2) . ')'])
                                    ->all()
                            )
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $enum = NilaiHurufEnum::tryFrom($state);
                                    if ($enum) {
                                        $set('bobot', $enum->bobot());
                                        $set('lulus', $enum->isLulus());
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('bobot')
                            ->label('Bobot Kuantitatif')
                            ->numeric()
                            ->readOnly()
                            ->dehydrated()
                            ->required(),

                        Forms\Components\TextInput::make('sks')
                            ->label('SKS Mata Kuliah')
                            ->numeric()
                            ->readOnly()
                            ->dehydrated()
                            ->required(),

                        Forms\Components\Toggle::make('lulus')
                            ->label('Status Kelulusan')
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Forms\Components\TextInput::make('tahun_akademik')
                            ->label('Tahun Akademik')
                            ->required()
                            ->readOnly()
                            ->dehydrated(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('krsDetail.krs.mahasiswa.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('krsDetail.krs.mahasiswa.user.name')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('krsDetail.kelas.mataKuliah.nama')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('krsDetail.kelas.kode')
                    ->label('Kelas')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nilai_huruf')
                    ->label('Nilai')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('bobot')
                    ->label('Bobot')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sks')
                    ->label('SKS')
                    ->sortable(),

                Tables\Columns\IconColumn::make('lulus')
                    ->label('Lulus')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('lulus')
                    ->label('Status Kelulusan'),
                Tables\Filters\SelectFilter::make('nilai_huruf')
                    ->options(
                        collect(NilaiHurufEnum::cases())
                            ->mapWithKeys(fn ($enum) => [$enum->value => $enum->value])
                            ->all()
                    ),
            ])
            ->actions([
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
            'index' => Pages\ListNilais::route('/'),
            'create' => Pages\CreateNilai::route('/create'),
            'edit' => Pages\EditNilai::route('/{record}/edit'),
        ];
    }
}
