<?php

namespace App\Filament\Resources\MonthlyHarvestResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HarvestVarietiesRelationManager extends RelationManager
{
    protected static string $relationship = 'varieties';

    protected static ?string $title = 'Harvest Varieties';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('variety')
                    ->label('Variety / Hybrid Crosses')
                    ->required()
                    ->maxLength(200),
                Forms\Components\Select::make('seednuts_type')
                    ->label('Seednuts Type')
                    ->options([
                        'OPV' => 'OPV',
                        'Hybrid' => 'Hybrid',
                    ]),
                Forms\Components\TextInput::make('seednuts_count')
                    ->label('Seednuts Count')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('remarks')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('variety')
                    ->label('Variety / Hybrid Crosses'),
                Tables\Columns\TextColumn::make('seednuts_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'OPV' => 'info',
                        'Hybrid' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('seednuts_count')
                    ->label('Seednuts Count')
                    ->numeric(),
                Tables\Columns\TextColumn::make('remarks'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('New Variety'),
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
