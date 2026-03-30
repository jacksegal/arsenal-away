<div>
    <div class="mb-6 flex flex-wrap items-end gap-4">
        <flux:select wire:model.live="season" label="Season" placeholder="All Seasons" class="w-40">
            @foreach ($seasons as $s)
                <flux:select.option value="{{ $s->value }}">{{ $s->value }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="competition" label="Competition" placeholder="All Competitions" class="w-56">
            @foreach ($competitions as $c)
                <flux:select.option value="{{ $c->value }}">{{ $c->value }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Opposition</flux:table.column>
            <flux:table.column>Competition</flux:table.column>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column>GW / Round</flux:table.column>
            <flux:table.column class="text-right">Allocation</flux:table.column>
            <flux:table.column class="text-right">Sale Points</flux:table.column>
            <flux:table.column class="text-right">Sell Out</flux:table.column>
            <flux:table.column class="text-right">Tickets</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($fixtures as $fixture)
                <flux:table.row :key="$fixture->id">
                    <flux:table.cell class="font-medium">{{ $fixture->opposition->value }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="match($fixture->competition) {
                            \App\Enums\Competition::PremierLeague => 'purple',
                            \App\Enums\Competition::ChampionsLeague => 'blue',
                            \App\Enums\Competition::FaCup => 'red',
                            \App\Enums\Competition::CarabaoCup => 'green',
                        }">{{ $fixture->competition->value }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $fixture->fixture_date?->format('d M Y') ?? '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $fixture->game_week ?? '-' }}</flux:table.cell>
                    <flux:table.cell class="text-right">{{ $fixture->allocation ? number_format($fixture->allocation) : '-' }}</flux:table.cell>
                    <flux:table.cell class="text-right">{{ $fixture->starting_sale_points ?? '-' }}</flux:table.cell>
                    <flux:table.cell class="text-right">{{ $fixture->sell_out_points ?? '-' }}</flux:table.cell>
                    <flux:table.cell class="text-right">
                        @if ($fixture->arsenal_ticket_link)
                            <flux:button variant="ghost" size="xs" icon="ticket" href="{{ $fixture->arsenal_ticket_link }}" target="_blank" />
                        @else
                            -
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center">No fixtures found.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
