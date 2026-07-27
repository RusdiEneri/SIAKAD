<?php

namespace App\Filament\Resources\KelasResource\RelationManagers;

use App\Enums\HariEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class JadwalsRelationManager extends RelationManager
{
    protected static string $relationship = 'jadwals';

    protected static ?string $title = 'Jadwal Kuliah';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('hari')
                    ->label('Hari Perkuliahan')
                    ->options(
                        collect(HariEnum::cases())
                            ->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])
                            ->all()
                    )
                    ->required(),

                Forms\Components\TimePicker::make('jam_mulai')
                    ->label('Jam Mulai')
                    ->seconds(false)
                    ->required(),

                Forms\Components\TimePicker::make('jam_selesai')
                    ->label('Jam Selesai')
                    ->seconds(false)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hari')
                    ->label('Hari')
                    ->badge(),

                Tables\Columns\TextColumn::make('jam_mulai')
                    ->label('Jam Mulai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('jam_selesai')
                    ->label('Jam Selesai')
                    ->time('H:i'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
