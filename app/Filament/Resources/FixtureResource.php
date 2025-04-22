<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FixtureResource\Pages;
use App\Filament\Resources\FixtureResource\RelationManagers;
use App\Models\Fixture;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FixtureResource extends Resource
{
    protected static ?string $model = Fixture::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Fixtures';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('team')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('competition')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('date')
                    ->required(),
                Forms\Components\Toggle::make('is_home')
                    ->required(),
                Forms\Components\TextInput::make('away_points_sold_out')
                    ->numeric()
                    ->label('Points Sold Out At')
                    ->helperText('The number of points at which tickets sold out'),
                Forms\Components\TextInput::make('away_allocation_tickets')
                    ->numeric()
                    ->label('Away Allocation Tickets'),
                Forms\Components\TextInput::make('season')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('arsenal_url')
                    ->required()
                    ->url()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('ticket_url')
                    ->url()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('competition')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_home')
                    ->boolean()
                    ->label('Home'),
                Tables\Columns\TextColumn::make('away_points_sold_out')
                    ->numeric()
                    ->label('Sold Out At')
                    ->sortable(),
                Tables\Columns\TextColumn::make('away_allocation_tickets')
                    ->numeric()
                    ->label('Allocation'),
                Tables\Columns\TextColumn::make('season')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('season')
                    ->options(function () {
                        return Fixture::distinct()->pluck('season', 'season');
                    }),
                Tables\Filters\TernaryFilter::make('is_home')
                    ->label('Fixture Type')
                    ->options([
                        '1' => 'Home',
                        '0' => 'Away',
                    ]),
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
        return [
            RelationManagers\TicketSalesPhasesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFixtures::route('/'),
            'create' => Pages\CreateFixture::route('/create'),
            'edit' => Pages\EditFixture::route('/{record}/edit'),
        ];
    }
}
