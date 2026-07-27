<?php

namespace App\Filament\Resources;

use App\Enums\RoleEnum;
use App\Filament\Resources\DosenResource\Pages;
use App\Models\Dosen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DosenResource extends Resource
{
    protected static ?string $model = Dosen::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Dosen';

    protected static ?int $navigationSort = 2;

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
            return $query->where('id', $user->dosen->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dosen')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Akun Pengguna (User)')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('nidn')
                            ->label('NIDN')
                            ->unique(ignoreRecord: true)
                            ->maxLength(30),

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
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Dosen')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nidn')
                    ->label('NIDN')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('prodi.nama')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable(),

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
            'index' => Pages\ListDosens::route('/'),
            'create' => Pages\CreateDosen::route('/create'),
            'edit' => Pages\EditDosen::route('/{record}/edit'),
        ];
    }
}
