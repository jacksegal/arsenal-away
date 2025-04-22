<?php

namespace App\Livewire;

use App\Models\Fixture;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public $filter = 'upcoming';
    public $showHome = false;
    public $seasonFilter = '';
    public $search = '';

    protected $queryString = [
        'filter' => ['except' => 'upcoming'],
        'showHome' => ['except' => false],
        'seasonFilter' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        // Set default season filter to current season
        if (empty($this->seasonFilter)) {
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            
            // Football season spans from August to May, so if we're before August, we're in the previous season
            if ($currentMonth < 8) {
                $this->seasonFilter = ($currentYear - 1) . '-' . substr($currentYear, -2);
            } else {
                $this->seasonFilter = $currentYear . '-' . substr(($currentYear + 1), -2);
            }
        }
    }

    public function resetFilters()
    {
        $this->reset(['filter', 'showHome', 'search']);
        $this->mount(); // Reset season to current
    }

    public function runScraper()
    {
        \Artisan::call('app:scrape-arsenal-tickets');
        session()->flash('message', 'Scraper job started successfully!');
    }

    public function render()
    {
        $query = Fixture::query()->with('salesPhases');

        // Apply filters
        if ($this->filter === 'upcoming') {
            $query->where('date', '>=', Carbon::now());
        } elseif ($this->filter === 'past') {
            $query->where('date', '<', Carbon::now());
        }

        // Filter by home/away
        if (!$this->showHome) {
            $query->where('is_home', false);
        }

        // Filter by season
        if ($this->seasonFilter) {
            $query->where('season', $this->seasonFilter);
        }

        // Search
        if ($this->search) {
            $query->where('team', 'like', '%' . $this->search . '%')
                  ->orWhere('competition', 'like', '%' . $this->search . '%');
        }

        // Get seasons for dropdown
        $seasons = Fixture::select('season')
            ->distinct()
            ->orderBy('season', 'desc')
            ->pluck('season');

        // Order by date (upcoming first)
        $fixtures = $query->orderBy('date', $this->filter === 'past' ? 'desc' : 'asc')
            ->paginate(10);

        return view('livewire.dashboard', [
            'fixtures' => $fixtures,
            'seasons' => $seasons,
        ]);
    }
}
