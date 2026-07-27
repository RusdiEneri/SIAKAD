<?php

namespace App\Filament\Resources;

use App\Enums\SemesterPenawaranEnum;
use App\Filament\Resources\MataKuliahResource\Pages;
use App\Models\MataKuliah;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MataKuliahResource extends Resource
{
    protected static ?string $model = MataKuliah::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Mata Kuliah';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Mata Kuliah')
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->label('Kode Mata Kuliah')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Mata Kuliah')
                            ->required()
                            ->maxLength(150),

                        Forms\Components\TextInput::make('sks')
                            ->label('Bobot SKS')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(8),

                        Forms\Components\Select::make('semester_penawaran')
                            ->label('Semester Penawaran')
                            ->options(
                                collect(SemesterPenawaranEnum::cases())
                                    ->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])
                                    ->all()
                            )
                            ->default(SemesterPenawaranEnum::SEMUA->value)
                            ->required(),

                        Forms\Components\Select::make('prodi_id')
                            ->label('Program Studi')
                            ->relationship('prodi', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->required(),

                        Forms\Components\Select::make('prasyarat')
                            ->label('Mata Kuliah Prasyarat (Opsional)')
                            ->relationship('prasyarat', 'nama')
                            ->multiple()
                            ->preload()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi / Silabus Ringkas')
                            ->nullable()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Mata Kuliah')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sks')
                    ->label('SKS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('semester_penawaran')
                    ->label('Penawaran')
                    ->badge(),

                Tables\Columns\TextColumn::make('prodi.nama')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('prasyarat.kode')
                    ->label('Prasyarat')
                    ->badge()
                    ->separator(', '),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                Tables\Filters\SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'nama'),
                Tables\Filters\SelectFilter::make('semester_penawaran')
                    ->options(
                        collect(SemesterPenawaranEnum::cases())
                            ->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])
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
            'index' => Pages\ListMataKuliahs::route('/'),
            'create' => Pages\CreateMataKuliah::route('/create'),
            'edit' => Pages\EditMataKuliah::route('/{record}/edit'),
        ];
    }
}
