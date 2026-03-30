<?php

namespace App\Filament\Resources\Fixtures\Tables;

use App\Enums\Competition;
use App\Enums\Season;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FixturesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('season')
                    ->badge()
                    ->searchable(),
                TextColumn::make('competition')
                    ->badge()
                    ->searchable(),
                TextColumn::make('opposition')
                    ->searchable(),
                TextColumn::make('allocation')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('fixture_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('starting_sale_points')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sell_out_points')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('arsenal_ticket_link')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('game_week'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fixture_date', 'desc')
            ->filters([
                SelectFilter::make('season')
                    ->options(Season::class),
                SelectFilter::make('competition')
                    ->options(Competition::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
