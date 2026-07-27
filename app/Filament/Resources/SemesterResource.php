<?php

namespace App\Filament\Resources;

use App\Enums\SemesterJenisEnum;
use App\Filament\Resources\SemesterResource\Pages;
use App\Models\Semester;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SemesterResource extends Resource
{
    protected static ?string $model = Semester::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?string $navigationLabel = 'Semester';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Semester')
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->label('Kode Semester')
                            ->placeholder('e.g. 20251')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(10),

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Semester')
                            ->placeholder('e.g. 2025/2026 Ganjil')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('tahun')
                            ->label('Tahun Akademik')
                            ->numeric()
                            ->required()
                            ->minValue(2000)
                            ->maxValue(2100),

                        Forms\Components\Select::make('jenis')
                            ->label('Jenis Semester')
                            ->options(
                                collect(SemesterJenisEnum::cases())
                                    ->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])
                                    ->all()
                            )
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_akhir')
                            ->label('Tanggal Akhir')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif (Semester Berjalan)')
                            ->default(false)
                            ->required(),
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
                    ->label('Nama Semester')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge(),

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_akhir')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                Tables\Filters\SelectFilter::make('jenis')
                    ->options(
                        collect(SemesterJenisEnum::cases())
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
            'index' => Pages\ListSemesters::route('/'),
            'create' => Pages\CreateSemester::route('/create'),
            'edit' => Pages\EditSemester::route('/{record}/edit'),
        ];
    }
}
