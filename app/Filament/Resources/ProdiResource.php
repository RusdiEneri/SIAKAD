<?php

namespace App\Filament\Resources;

use App\Enums\JenjangEnum;
use App\Filament\Resources\ProdiResource\Pages;
use App\Models\Prodi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProdiResource extends Resource
{
    protected static ?string $model = Prodi::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Program Studi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Program Studi')
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->label('Kode Prodi')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Program Studi')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\Select::make('jenjang')
                            ->label('Jenjang')
                            ->options(
                                collect(JenjangEnum::cases())
                                    ->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])
                                    ->all()
                            )
                            ->required(),

                        Forms\Components\TextInput::make('fakultas')
                            ->label('Fakultas')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
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
                    ->label('Nama Prodi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenjang')
                    ->label('Jenjang')
                    ->badge(),

                Tables\Columns\TextColumn::make('fakultas')
                    ->label('Fakultas')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                Tables\Filters\SelectFilter::make('jenjang')
                    ->options(
                        collect(JenjangEnum::cases())
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
            'index' => Pages\ListProdis::route('/'),
            'create' => Pages\CreateProdi::route('/create'),
            'edit' => Pages\EditProdi::route('/{record}/edit'),
        ];
    }
}
