<?php

namespace App\Filament\Resources;

use App\Enums\RoleEnum;
use App\Enums\StatusMahasiswaEnum;
use App\Filament\Resources\MahasiswaResource\Pages;
use App\Models\Mahasiswa;
use App\Models\Semester;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MahasiswaResource extends Resource
{
    protected static ?string $model = Mahasiswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Mahasiswa';

    protected static ?int $navigationSort = 3;

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
            return $query->where('prodi_id', $user->dosen->prodi_id);
        }

        if ($user->hasRole(RoleEnum::DOSEN) && $user->dosen) {
            return $query->where('dosen_wali_id', $user->dosen->id);
        }

        if ($user->hasRole(RoleEnum::MAHASISWA) && $user->mahasiswa) {
            return $query->where('id', $user->mahasiswa->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Mahasiswa')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Akun Pengguna (User)')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('nim')
                            ->label('NIM')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(30),

                        Forms\Components\Select::make('prodi_id')
                            ->label('Program Studi')
                            ->relationship('prodi', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('angkatan')
                            ->label('Tahun Angkatan')
                            ->numeric()
                            ->required()
                            ->minValue(2000)
                            ->maxValue(2100),

                        Forms\Components\Select::make('dosen_wali_id')
                            ->label('Dosen Wali')
                            ->relationship('dosenWali', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name ?? "Dosen #{$record->id}")
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\Select::make('status')
                            ->label('Status Akademik')
                            ->options(
                                collect(StatusMahasiswaEnum::cases())
                                    ->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])
                                    ->all()
                            )
                            ->default(StatusMahasiswaEnum::AKTIF->value)
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_masuk')
                            ->label('Tanggal Masuk')
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_lulus')
                            ->label('Tanggal Lulus')
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('prodi.nama')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('angkatan')
                    ->label('Angkatan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('dosenWali.user.name')
                    ->label('Dosen Wali')
                    ->default('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(
                        collect(StatusMahasiswaEnum::cases())
                            ->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])
                            ->all()
                    ),
                Tables\Filters\SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'nama'),
                Tables\Filters\SelectFilter::make('angkatan')
                    ->options(fn () => Mahasiswa::query()->pluck('angkatan', 'angkatan')->unique()->all()),
            ])
            ->actions([
                Tables\Actions\Action::make('cetakKhs')
                    ->label('Cetak KHS')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('semester_id')
                            ->label('Pilih Semester')
                            ->options(fn () => Semester::query()->pluck('nama', 'id')->all())
                            ->required(),
                    ])
                    ->action(function (Mahasiswa $record, array $data) {
                        return redirect()->route('reports.khs', ['mahasiswa' => $record->id, 'semester' => $data['semester_id']]);
                    }),

                Tables\Actions\Action::make('cetakTranskrip')
                    ->label('Transkrip Kumulatif')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->url(fn (Mahasiswa $record) => route('reports.transkrip', $record))
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
            'index' => Pages\ListMahasiswas::route('/'),
            'create' => Pages\CreateMahasiswa::route('/create'),
            'edit' => Pages\EditMahasiswa::route('/{record}/edit'),
        ];
    }
}
