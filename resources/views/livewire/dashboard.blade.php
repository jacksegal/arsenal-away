<div class="max-w-7xl mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6">Arsenal Ticket Tracker</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="filter" class="block text-sm font-medium text-gray-700">Match Filter</label>
            <select wire:model.live="filter" id="filter" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="upcoming">Upcoming Matches</option>
                <option value="past">Past Matches</option>
                <option value="all">All Matches</option>
            </select>
        </div>
        
        <div>
            <label for="seasonFilter" class="block text-sm font-medium text-gray-700">Season</label>
            <select wire:model.live="seasonFilter" id="seasonFilter" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                @foreach($seasons as $season)
                    <option value="{{ $season }}">{{ $season }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700">Search Team or Competition</label>
            <input wire:model.live.debounce.300ms="search" type="text" id="search" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Search...">
        </div>

        <div class="flex items-end">
            <div class="flex items-center mb-1">
                <input wire:model.live="showHome" type="checkbox" id="showHome" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50">
                <label for="showHome" class="ml-2 block text-sm text-gray-700">Show Home Games</label>
            </div>
            
            <button wire:click="resetFilters" class="ml-auto mb-1 px-3 py-1 border border-gray-300 rounded-md text-sm leading-5 font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 transition duration-150 ease-in-out">
                Reset
            </button>
        </div>
    </div>

    <div class="mb-4 flex justify-between items-center">
        <h2 class="text-xl font-semibold">{{ $fixtures->total() }} Matches Found</h2>
        <button wire:click="runScraper" class="px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-red-600 hover:bg-red-500 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 active:bg-red-700 transition duration-150 ease-in-out">
            Run Scraper Now
        </button>
    </div>

    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Competition</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Home/Away</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket Sale Phases</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($fixtures as $fixture)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ $fixture->date->format('D, j M Y - g:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            {{ $fixture->team }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ $fixture->competition ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $fixture->is_home ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $fixture->is_home ? 'Home' : 'Away' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if ($fixture->salesPhases->isEmpty())
                                <span class="text-gray-500 italic">No sales phases yet</span>
                            @else
                                <div class="space-y-3">
                                    @foreach ($fixture->salesPhases as $phase)
                                        <div class="flex flex-col border-b pb-2">
                                            <span class="font-medium">{{ $phase->sales_phase }}</span>
                                            
                                            @if ($phase->who_can_buy)
                                                <span class="text-xs mt-1">
                                                    <span class="font-semibold">Who:</span> {{ $phase->who_can_buy }}
                                                </span>
                                            @endif
                                            
                                            @if ($phase->points_required)
                                                <span class="text-xs">
                                                    <span class="font-semibold">Points:</span> {{ $phase->points_required }}
                                                </span>
                                            @endif
                                            
                                            <span class="text-xs mt-1">
                                                <span class="font-semibold">Sale date:</span>
                                                @if ($phase->sale_date)
                                                    {{ $phase->sale_date->format('j M Y') }}
                                                    @if ($phase->sale_time)
                                                        at {{ $phase->sale_time }}
                                                    @endif
                                                @else
                                                    Unknown
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <a href="{{ $fixture->ticket_url }}" target="_blank" class="mt-2 inline-flex items-center text-xs font-medium text-indigo-600 hover:text-indigo-900">
                                View Ticket Info
                                <svg class="ml-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            No fixtures found. Run the scraper to fetch the latest fixtures.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $fixtures->links() }}
    </div>
</div>
