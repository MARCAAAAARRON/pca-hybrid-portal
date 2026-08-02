<?php

namespace App\Filament\Resources\NurseryOperationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NurseryBatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batches';

    protected static ?string $title = 'Nursery Batches';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Seednut Details')
                    ->schema([
                        Forms\Components\TextInput::make('seednuts_harvested')
                            ->label('No. Harvested')
                            ->numeric()->default(0),
                        Forms\Components\TextInput::make('date_harvested')
                            ->label('Date Harvested')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('date_received')
                            ->label('Date Received')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('source_of_seednuts')
                            ->label('Source of Seednuts')
                            ->maxLength(200),
                        Forms\Components\TextInput::make('variety')
                            ->label('Type / Variety')
                            ->maxLength(100),
                    ])->columns(3),

                Forms\Components\Section::make('Nursery Progress')
                    ->schema([
                        Forms\Components\TextInput::make('seednuts_sown')
                            ->label('No. Sown')
                            ->numeric()->default(0),
                        Forms\Components\TextInput::make('date_sown')
                            ->label('Date Sown')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('seedlings_germinated')
                            ->label('Germinated')
                            ->numeric()->default(0),
                        Forms\Components\TextInput::make('ungerminated_seednuts')
                            ->label('Ungerminated')
                            ->numeric()->default(0),
                        Forms\Components\TextInput::make('culled_seedlings')
                            ->label('Culled')
                            ->numeric()->default(0),
                        Forms\Components\TextInput::make('good_seedlings')
                            ->label('Good @ 1ft')
                            ->numeric()->default(0),
                        Forms\Components\TextInput::make('ready_to_plant')
                            ->label('Ready (Polybagged)')
                            ->numeric()->default(0),
                        Forms\Components\TextInput::make('seedlings_dispatched')
                            ->label('Dispatched')
                            ->numeric()->default(0),
                    ])->columns(4),

                Forms\Components\TextInput::make('remarks')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('variety')->label('Variety'),
                Tables\Columns\TextColumn::make('seednuts_harvested')->label('Harvested')->numeric(),
                Tables\Columns\TextColumn::make('seednuts_sown')->label('Sown')->numeric(),
                Tables\Columns\TextColumn::make('seedlings_germinated')->label('Germinated')->numeric(),
                Tables\Columns\TextColumn::make('good_seedlings')->label('Good @1ft')->numeric(),
                Tables\Columns\TextColumn::make('ready_to_plant')->label('Ready')->numeric(),
                Tables\Columns\TextColumn::make('seedlings_dispatched')->label('Dispatched')->numeric(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('New Batch'),
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
