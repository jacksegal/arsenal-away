<?php

namespace App\Filament\Resources\FixtureResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TicketSalesPhasesRelationManager extends RelationManager
{
    protected static string $relationship = 'salesPhases';

    protected static ?string $recordTitleAttribute = 'sales_phase';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sales_phase')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('who_can_buy')
                    ->maxLength(255),
                Forms\Components\TextInput::make('points_required')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('sale_date'),
                Forms\Components\TextInput::make('sale_time')
                    ->maxLength(255),
                Forms\Components\Toggle::make('notified')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sales_phase')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('who_can_buy')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('points_required')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_time')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('notified')
                    ->boolean()
                    ->sortable(),
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