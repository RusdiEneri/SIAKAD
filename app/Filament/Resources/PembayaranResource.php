<?php

namespace App\Filament\Resources;

use App\Enums\PembayaranJenisEnum;
use App\Enums\PembayaranStatusEnum;
use App\Enums\RoleEnum;
use App\Filament\Resources\PembayaranResource\Pages;
use App\Models\Pembayaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PembayaranResource extends Resource
{
    protected static ?string $model = Pembayaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Keuangan & UKT';

    protected static ?string $navigationLabel = 'Data Pembayaran UKT';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        if ($user->hasRole([RoleEnum::ADMIN, RoleEnum::KEUANGAN])) {
            return $query;
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
                Forms\Components\Section::make('Informasi Pembayaran UKT')
                    ->schema([
                        Forms\Components\Select::make('mahasiswa_id')
                            ->label('Mahasiswa')
                            ->relationship('mahasiswa', 'nim')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nim} - {$record->user->name}")
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('semester_id')
                            ->label('Semester Tagihan')
                            ->relationship('semester', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('jenis')
                            ->label('Jenis Pembayaran')
                            ->options(
                                collect(PembayaranJenisEnum::cases())
                                    ->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])
                                    ->all()
                            )
                            ->default(PembayaranJenisEnum::UKT->value)
                            ->required(),

                        Forms\Components\TextInput::make('nominal')
                            ->label('Nominal (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status Pembayaran')
                            ->options(
                                collect(PembayaranStatusEnum::cases())
                                    ->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])
                                    ->all()
                            )
                            ->default(PembayaranStatusEnum::BELUM->value)
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_bayar')
                            ->label('Tanggal Pembayaran')
                            ->nullable(),

                        Forms\Components\TextInput::make('referensi')
                            ->label('Nomor Referensi / Kuitansi')
                            ->nullable()
                            ->maxLength(100),
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

                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge(),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                Tables\Columns\TextColumn::make('tanggal_bayar')
                    ->label('Tgl Bayar')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('referensi')
                    ->label('Referensi')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(
                        collect(PembayaranStatusEnum::cases())
                            ->mapWithKeys(fn ($enum) => [$enum->value => $enum->label()])
                            ->all()
                    ),
                Tables\Filters\SelectFilter::make('semester_id')
                    ->label('Semester')
                    ->relationship('semester', 'nama'),
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
            'index' => Pages\ListPembayarans::route('/'),
            'create' => Pages\CreatePembayaran::route('/create'),
            'edit' => Pages\EditPembayaran::route('/{record}/edit'),
        ];
    }
}
