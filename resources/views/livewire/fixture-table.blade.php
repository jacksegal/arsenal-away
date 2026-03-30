<div>
    <div class="mb-6 flex flex-wrap gap-4">
        <div>
            <label for="season" class="block text-sm font-medium text-gray-700">Season</label>
            <select wire:model.live="season" id="season" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                <option value="">All Seasons</option>
                @foreach ($seasons as $s)
                    <option value="{{ $s->value }}">{{ $s->value }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="competition" class="block text-sm font-medium text-gray-700">Competition</label>
            <select wire:model.live="competition" id="competition" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                <option value="">All Competitions</option>
                @foreach ($competitions as $c)
                    <option value="{{ $c->value }}">{{ $c->value }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Opposition</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Competition</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">GW / Round</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Allocation</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Sale Points</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Sell Out Points</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($fixtures as $fixture)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">{{ $fixture->opposition->value }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $fixture->competition->value }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $fixture->fixture_date?->format('d M Y') ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $fixture->game_week ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-500">{{ $fixture->allocation ? number_format($fixture->allocation) : '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-500">{{ $fixture->starting_sale_points ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-500">{{ $fixture->sell_out_points ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">No fixtures found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
