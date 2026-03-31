<?php

namespace App\Livewire;

use App\Enums\Competition;
use App\Enums\Season;
use App\Models\Fixture;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FixtureTable extends Component
{
    public array $season = ['25/26'];

    public array $competition = [];

    public array $columns = ['opposition', 'competition', 'fixture_date', 'game_week', 'allocation', 'sell_out_points'];

    public string $sortBy = 'fixture_date';

    public string $sortDirection = 'asc';

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function render(): View
    {
        $fixtures = Fixture::query()
            ->when($this->season, fn ($q) => $q->whereIn('season', $this->season))
            ->when($this->competition, fn ($q) => $q->whereIn('competition', $this->competition))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->get();

        return view('livewire.fixture-table', [
            'fixtures' => $fixtures,
            'seasons' => Season::cases(),
            'competitions' => Competition::cases(),
        ]);
    }
}
