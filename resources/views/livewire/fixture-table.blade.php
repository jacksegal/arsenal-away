<div>
    <div class="text-ceefax-green text-sm mb-3">P302 ARSENAL AWAY FIXTURES</div>

    <div class="mb-4 flex flex-wrap items-end gap-4">
        <flux:select variant="listbox" multiple wire:model.live="season" label="Season" placeholder="All Seasons" class="w-56">
            @foreach ($seasons as $s)
                <flux:select.option value="{{ $s->value }}">{{ $s->value }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" multiple wire:model.live="competition" label="Competition" placeholder="All Competitions" class="w-64">
            @foreach ($competitions as $c)
                <flux:select.option value="{{ $c->value }}">{{ $c->value }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" indicator="checkbox" multiple wire:model.live="columns" label="Columns" placeholder="Columns" class="w-64">
            <flux:select.option value="opposition">Opposition</flux:select.option>
            <flux:select.option value="competition">Competition</flux:select.option>
            <flux:select.option value="fixture_date">Date</flux:select.option>
            <flux:select.option value="game_week">GW / Round</flux:select.option>
            <flux:select.option value="allocation">Allocation</flux:select.option>
            <flux:select.option value="starting_sale_points">Starting Points</flux:select.option>
            <flux:select.option value="sell_out_points">Points Sell Out</flux:select.option>
            <flux:select.option value="tickets">Tickets</flux:select.option>
        </flux:select>

        <span wire:loading class="text-ceefax-yellow ceefax-blink text-xs self-center">Searching...</span>
    </div>

    <div class="border-t border-ceefax-cyan/40 mb-4"></div>

    <flux:table>
        <flux:table.columns>
            @if (in_array('opposition', $columns))
                <flux:table.column sortable :sorted="$sortBy === 'opposition'" :direction="$sortDirection" wire:click="sort('opposition')">Opposition</flux:table.column>
            @endif
            @if (in_array('competition', $columns))
                <flux:table.column sortable :sorted="$sortBy === 'competition'" :direction="$sortDirection" wire:click="sort('competition')">Competition</flux:table.column>
            @endif
            @if (in_array('fixture_date', $columns))
                <flux:table.column sortable :sorted="$sortBy === 'fixture_date'" :direction="$sortDirection" wire:click="sort('fixture_date')">Date</flux:table.column>
            @endif
            @if (in_array('game_week', $columns))
                <flux:table.column sortable :sorted="$sortBy === 'game_week'" :direction="$sortDirection" wire:click="sort('game_week')">GW / Round</flux:table.column>
            @endif
            @if (in_array('allocation', $columns))
                <flux:table.column sortable :sorted="$sortBy === 'allocation'" :direction="$sortDirection" wire:click="sort('allocation')">Allocation</flux:table.column>
            @endif
            @if (in_array('starting_sale_points', $columns))
                <flux:table.column sortable :sorted="$sortBy === 'starting_sale_points'" :direction="$sortDirection" wire:click="sort('starting_sale_points')">Starting Points</flux:table.column>
            @endif
            @if (in_array('sell_out_points', $columns))
                <flux:table.column sortable :sorted="$sortBy === 'sell_out_points'" :direction="$sortDirection" wire:click="sort('sell_out_points')">Points Sell Out</flux:table.column>
            @endif
            @if (in_array('tickets', $columns))
                <flux:table.column>Tickets</flux:table.column>
            @endif
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($fixtures as $fixture)
                <flux:table.row :key="$fixture->id">
                    @if (in_array('opposition', $columns))
                        <flux:table.cell class="font-bold text-ceefax-white!">{{ $fixture->opposition->value }}</flux:table.cell>
                    @endif
                    @if (in_array('competition', $columns))
                        <flux:table.cell>
                            <flux:badge size="sm" variant="solid" :class="match($fixture->competition) {
                                \App\Enums\Competition::PremierLeague => 'bg-ceefax-magenta! text-black!',
                                \App\Enums\Competition::ChampionsLeague => 'bg-ceefax-cyan! text-black!',
                                \App\Enums\Competition::FaCup => 'bg-ceefax-red! text-black!',
                                \App\Enums\Competition::CarabaoCup => 'bg-ceefax-green! text-black!',
                            }">{{ $fixture->competition->value }}</flux:badge>
                        </flux:table.cell>
                    @endif
                    @if (in_array('fixture_date', $columns))
                        <flux:table.cell>{{ $fixture->fixture_date?->format('d M Y') ?? '-' }}</flux:table.cell>
                    @endif
                    @if (in_array('game_week', $columns))
                        <flux:table.cell>{{ $fixture->game_week ?? '-' }}</flux:table.cell>
                    @endif
                    @if (in_array('allocation', $columns))
                        <flux:table.cell>{{ $fixture->allocation ? number_format($fixture->allocation) : '-' }}</flux:table.cell>
                    @endif
                    @if (in_array('starting_sale_points', $columns))
                        <flux:table.cell>{{ $fixture->starting_sale_points ?? '-' }}</flux:table.cell>
                    @endif
                    @if (in_array('sell_out_points', $columns))
                        <flux:table.cell>{{ $fixture->sell_out_points ?? '-' }}</flux:table.cell>
                    @endif
                    @if (in_array('tickets', $columns))
                        <flux:table.cell>
                            @if ($fixture->arsenal_ticket_link)
                                <a href="{{ $fixture->arsenal_ticket_link }}" target="_blank" class="text-ceefax-cyan hover:text-ceefax-yellow no-underline text-xs">
                                    TICKETS &gt;&gt;&gt;
                                </a>
                            @else
                                <span class="text-ceefax-border">-</span>
                            @endif
                        </flux:table.cell>
                    @endif
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell :colspan="count($columns)" class="text-center text-ceefax-yellow!">No fixtures found.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
