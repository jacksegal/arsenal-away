<?php

namespace App\Models;

use App\Enums\Competition;
use App\Enums\Opposition;
use App\Enums\Season;
use Database\Factories\FixtureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'season',
    'competition',
    'opposition',
    'allocation',
    'fixture_date',
    'starting_sale_points',
    'sell_out_points',
    'arsenal_ticket_link',
    'game_week',
    'notes',
])]
class Fixture extends Model
{
    /** @use HasFactory<FixtureFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'season' => Season::class,
            'competition' => Competition::class,
            'opposition' => Opposition::class,
            'fixture_date' => 'date',
            'allocation' => 'integer',
            'starting_sale_points' => 'integer',
            'sell_out_points' => 'integer',
        ];
    }
}
