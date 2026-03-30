<?php

namespace App\Livewire;

use App\Enums\Competition;
use App\Enums\Season;
use App\Models\Fixture;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FixtureTable extends Component
{
    public string $season = '25/26';

    public string $competition = '';

    public function render(): View
    {
        $fixtures = Fixture::query()
            ->when($this->season, fn ($q) => $q->where('season', $this->season))
            ->when($this->competition, fn ($q) => $q->where('competition', $this->competition))
            ->orderBy('fixture_date')
            ->get();

        return view('livewire.fixture-table', [
            'fixtures' => $fixtures,
            'seasons' => Season::cases(),
            'competitions' => Competition::cases(),
        ]);
    }
}
