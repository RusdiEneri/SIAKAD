<?php

namespace App\Filament\Resources;

use App\Enums\RoleEnum;
use App\Filament\Resources\KelasResource\Pages;
use App\Filament\Resources\KelasResource\RelationManagers\JadwalsRelationManager;
use App\Models\Kelas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?string $navigationLabel = 'Kelas Perkuliahan';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        if ($user->hasRole([RoleEnum::ADMIN, RoleEnum::MAHASISWA])) {
            return $query;
        }

        if ($user->hasRole(RoleEnum::KAPRODI) && $user->dosen) {
            return $query->whereHas('mataKuliah', fn ($q) => $q->where('prodi_id', $user->dosen->prodi_id));
        }

        if ($user->hasRole(RoleEnum::DOSEN) && $user->dosen) {
            return $query->where('dosen_id', $user->dosen->id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Seksi Kelas')
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->label('Kode Kelas')
                            ->placeholder('e.g. TIF101-20251-A')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(30),

                        Forms\Components\Select::make('mata_kuliah_id')
                            ->label('Mata Kuliah')
                            ->relationship('mataKuliah', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('semester_id')
                            ->label('Semester')
                            ->relationship('semester', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('dosen_id')
                            ->label('Dosen Pengampu')
                            ->relationship('dosen', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name ?? "Dosen #{$record->id}")
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('kapasitas')
                            ->label('Kapasitas Kelas')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100),

                        Forms\Components\TextInput::make('ruang')
                            ->label('Ruang Perkuliahan')
                            ->required()
                            ->maxLength(50),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode Kelas')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('mataKuliah.nama')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('semester.nama')
                    ->label('Semester')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('dosen.user.name')
                    ->label('Dosen Pengampu')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->sortable(),

                Tables\Columns\TextColumn::make('krs_details_count')
                    ->label('Terisi')
                    ->counts('krsDetails')
                    ->badge(),

                Tables\Columns\TextColumn::make('ruang')
                    ->label('Ruang')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('semester_id')
                    ->label('Semester')
                    ->relationship('semester', 'nama'),
                Tables\Filters\SelectFilter::make('mata_kuliah_id')
                    ->label('Mata Kuliah')
                    ->relationship('mataKuliah', 'nama'),
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
        return [
            JadwalsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKelas::route('/'),
            'create' => Pages\CreateKelas::route('/create'),
            'edit' => Pages\EditKelas::route('/{record}/edit'),
        ];
    }
}
