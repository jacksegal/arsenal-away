<?php

namespace App\Filament\Resources\Fixtures\Schemas;

use App\Enums\Competition;
use App\Enums\Opposition;
use App\Enums\Season;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FixtureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('season')
                    ->options(Season::class)
                    ->default('25/26')
                    ->required(),
                Select::make('competition')
                    ->options(Competition::class)
                    ->required(),
                Select::make('opposition')
                    ->options(Opposition::class)
                    ->required(),
                TextInput::make('allocation')
                    ->numeric(),
                DatePicker::make('fixture_date'),
                TextInput::make('starting_sale_points')
                    ->numeric(),
                TextInput::make('sell_out_points')
                    ->numeric(),
                TextInput::make('arsenal_ticket_link')
                    ->url(),
                TextInput::make('game_week')
                    ->placeholder('e.g. GW1, R16, QF'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
