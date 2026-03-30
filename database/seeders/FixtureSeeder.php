<?php

namespace Database\Seeders;

use App\Models\Fixture;
use Illuminate\Database\Seeder;

class FixtureSeeder extends Seeder
{
    public function run(): void
    {
        $fixtures = json_decode(
            file_get_contents(database_path('fixtures/fixtures.json')),
            true,
        );

        foreach ($fixtures as $fixture) {
            Fixture::updateOrCreate(
                [
                    'season' => $fixture['season'],
                    'opposition' => $fixture['opposition'],
                    'competition' => $fixture['competition'],
                ],
                collect($fixture)->except(['season', 'opposition', 'competition'])->filter()->all(),
            );
        }
    }
}
