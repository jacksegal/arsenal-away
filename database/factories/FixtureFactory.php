<?php

namespace Database\Factories;

use App\Enums\Competition;
use App\Enums\Opposition;
use App\Enums\Season;
use App\Models\Fixture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fixture>
 */
class FixtureFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'season' => Season::Season_25_26,
            'competition' => fake()->randomElement(Competition::cases()),
            'opposition' => fake()->randomElement(Opposition::cases()),
            'allocation' => fake()->optional()->numberBetween(1500, 3200),
            'fixture_date' => fake()->optional()->dateTimeBetween('now', '+6 months'),
            'starting_sale_points' => fake()->optional()->numberBetween(1, 30),
            'sell_out_points' => fake()->optional()->numberBetween(1, 30),
            'arsenal_ticket_link' => fake()->optional()->url(),
            'game_week' => fake()->optional()->randomElement(['GW1', 'GW2', 'GW3', 'GW10', 'GW15', 'GW20', 'R16', 'QF', 'SF', 'R3', 'R4', 'R5']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
