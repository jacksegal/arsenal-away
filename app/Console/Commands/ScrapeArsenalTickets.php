<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\TicketSalesPhase;
use App\Models\User;
use App\Notifications\NewTicketSalesPhase;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeArsenalTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-arsenal-tickets {--force : Force notifications even for existing sales phases}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape Arsenal FC website for away ticket information';

    /**
     * The HTTP client instance.
     */
    protected Client $client;

    /**
     * The base URL for Arsenal tickets.
     */
    protected string $baseUrl = 'https://www.arsenal.com';

    /**
     * The tickets listing URL.
     */
    protected string $ticketsUrl = 'https://www.arsenal.com/tickets?field_arsenal_team_target_id=1';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            ],
        ]);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Arsenal away ticket scraper...');

        try {
            // First, scrape the fixtures from the main tickets page
            $fixtures = $this->scrapeFixtures();
            $this->info("Found {$fixtures->count()} away fixtures");

            // Then, scrape ticket sales phases for away fixtures
            $newSalesPhases = 0;
            foreach ($fixtures as $fixture) {
                if ($fixture->ticket_url) {
                    $this->info("Checking sales phases for {$fixture->team} (Away)");
                    $this->info("Checking ticket URL: {$fixture->ticket_url}");
                    $salesPhases = $this->scrapeSalesPhases($fixture);
                    $newSalesPhases += $salesPhases;
                }
            }

            $this->info("Scraping completed. {$newSalesPhases} new sales phases discovered.");

        } catch (\Exception $e) {
            $this->error("An error occurred: {$e->getMessage()}");
            Log::error("Arsenal ticket scraper error: {$e->getMessage()}", [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return Command::SUCCESS;
    }

    /**
     * Scrape fixtures from the main tickets page.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function scrapeFixtures()
    {
        $this->info("Scraping fixtures from {$this->ticketsUrl}");
        
        $response = $this->client->get($this->ticketsUrl);
        $html = (string) $response->getBody();
        $crawler = new Crawler($html);
        
        $fixtures = collect();
        
        // Find away match listings only
        $crawler->filter('article.ticket-card.ticket-card--away')->each(function (Crawler $node) use (&$fixtures) {
            try {
                // Extract date and time information
                $dateText = $node->filter('.event-info-alt__date')->text();
                $timeText = '';
                if ($node->filter('.event-info-alt__kickoff')->count() > 0) {
                    $timeText = $this->extractTimeFromKickoff($node->filter('.event-info-alt__kickoff')->text());
                }
                
                // Extract competition and venue if available
                $competition = null;
                if ($node->filter('.event-info-alt__extra')->count() > 0) {
                    $competition = trim($node->filter('.event-info-alt__extra')->text());
                }
                
                $venue = null;
                if ($node->filter('.event-info-alt__venue')->count() > 0) {
                    $venue = trim($node->filter('.event-info-alt__venue')->text());
                }
                
                // Extract team name
                $team = 'Unknown';
                if ($node->filter('.ticket-card__opponent a')->count() > 0) {
                    $team = trim($node->filter('.ticket-card__opponent a')->text());
                }
                
                // Extract ticket URL
                $ticketUrl = $this->extractTicketUrl($node);
                
                if (!$ticketUrl) {
                    $this->warn("Could not find ticket URL for {$team}");
                    return;
                }
                
                // Parse date and time
                $date = $this->parseDateTime($dateText, $timeText, $ticketUrl);
                
                // Determine season
                $season = $this->determineSeason($date);
                
                // Extract sales phases info 
                $salesPhaseInfo = [];
                $node->filter('.ticket-list__item')->each(function (Crawler $phaseNode) use (&$salesPhaseInfo) {
                    $phaseName = '';
                    $phaseDate = '';
                    
                    if ($phaseNode->filter('.ticket-list__item-text')->count() > 0) {
                        $phaseName = trim($phaseNode->filter('.ticket-list__item-text')->text());
                    }
                    
                    if ($phaseNode->filter('.ticket-list__item-date')->count() > 0) {
                        $phaseDate = trim($phaseNode->filter('.ticket-list__item-date')->text());
                    }
                    
                    if ($phaseName && $phaseDate) {
                        $salesPhaseInfo[] = [
                            'name' => $phaseName,
                            'date' => $phaseDate,
                        ];
                    }
                });
                
                // Check if this fixture already exists in our database
                $fixture = Fixture::firstOrNew(['ticket_url' => $ticketUrl]);
                
                // Update or create the fixture
                $fixture->fill([
                    'team' => $team,
                    'competition' => $competition,
                    'date' => $date,
                    'is_home' => false, // Always away since we're filtering for away matches
                    'season' => $season,
                ]);
                
                // Only save if new or changed
                if ($fixture->isDirty()) {
                    $fixture->save();
                    $this->info("Added/Updated away fixture: {$team} on {$date->format('Y-m-d')}");
                    
                    // Pre-populate sales phases if available from the ticket list
                    foreach ($salesPhaseInfo as $phaseInfo) {
                        try {
                            $salesPhase = TicketSalesPhase::firstOrNew([
                                'fixture_id' => $fixture->id,
                                'sales_phase' => $phaseInfo['name'],
                            ]);
                            
                            if (!$salesPhase->exists) {
                                try {
                                    $startDate = Carbon::parse($phaseInfo['date']);
                                    $salesPhase->start_date = $startDate;
                                } catch (\Exception $e) {
                                    $salesPhase->description = $phaseInfo['date'];
                                }
                                
                                $salesPhase->save();
                                $this->info("Added sales phase: {$phaseInfo['name']} for {$team}");
                            }
                        } catch (\Exception $e) {
                            $this->warn("Error adding sales phase: " . $e->getMessage());
                        }
                    }
                }
                
                $fixtures->push($fixture);
                
            } catch (\Exception $e) {
                $this->warn("Error parsing fixture: " . $e->getMessage());
            }
        });
        
        return $fixtures;
    }

    /**
     * Scrape ticket sales phases for a given fixture.
     *
     * @param Fixture $fixture
     * @return int Number of new sales phases
     */
    protected function scrapeSalesPhases(Fixture $fixture)
    {
        $this->info("Scraping sales phases from {$fixture->ticket_url}");
        
        $response = $this->client->get($fixture->ticket_url);
        $html = (string) $response->getBody();
        $crawler = new Crawler($html);
        
        $newSalesPhases = 0;
        $forceSend = $this->option('force');
        
        // Look for "SALES PHASES" heading followed by responsive-table
        $salesPhasesHeadingFound = false;
        
        // Find all h2 elements and check if one contains "SALES PHASES"
        $crawler->filter('h2')->each(function (Crawler $heading) use (&$salesPhasesHeadingFound, $crawler, $fixture, &$newSalesPhases, $forceSend) {
            if (stripos($heading->text(), 'SALES PHASES') !== false) {
                $salesPhasesHeadingFound = true;
                
                // Look for the next responsive-table after this heading
                $responsiveTable = $heading->nextAll()->filter('.responsive-table')->first();
                
                if ($responsiveTable->count() > 0) {
                    $this->processResponseTable($responsiveTable, $fixture, $newSalesPhases, $forceSend);
                }
            }
        });
        
        // If we didn't find the heading with "SALES PHASES", try to find any responsive-table
        if (!$salesPhasesHeadingFound) {
            $crawler->filter('.responsive-table')->each(function (Crawler $table) use ($fixture, &$newSalesPhases, $forceSend) {
                $this->processResponseTable($table, $fixture, $newSalesPhases, $forceSend);
            });
        }
        
        // If we still didn't find sales phases in responsive tables, check for any tables with sales phase headers
        if ($newSalesPhases === 0) {
            $crawler->filter('table')->each(function (Crawler $table) use ($fixture, &$newSalesPhases, $forceSend) {
                if ($table->filter('th:contains("Sales Phase"), th:contains("Who can Buy"), th:contains("Sale Date")')->count() > 0) {
                    $this->processResponseTable($table, $fixture, $newSalesPhases, $forceSend);
                }
            });
        }
        
        // Fallback: also check for ticket-list items which contain sales phases
        if ($newSalesPhases === 0) {
            $crawler->filter('.ticket-list__item, .ticket-card .ticket-list .ticket-list__item')->each(function (Crawler $item) use ($fixture, &$newSalesPhases, $forceSend) {
                $phaseName = '';
                $saleInfo = '';
                
                if ($item->filter('.ticket-list__item-text')->count() > 0) {
                    $phaseName = trim($item->filter('.ticket-list__item-text')->text());
                }
                
                if ($item->filter('.ticket-list__item-date')->count() > 0) {
                    $saleInfo = trim($item->filter('.ticket-list__item-date')->text());
                }
                
                if ($phaseName && $saleInfo) {
                    // Try to parse date and time
                    $saleDate = null;
                    $saleTime = null;
                    
                    // Try to extract date/time from format like "Mon Apr 28 - 10:00"
                    if (preg_match('/(\w+\s+\w+\s+\d+)\s*-\s*(\d+:\d+)/', $saleInfo, $matches)) {
                        $dateStr = $matches[1];
                        $timeStr = $matches[2];
                        
                        try {
                            $saleDate = Carbon::parse($dateStr)->toDateString();
                            $saleTime = $timeStr;
                        } catch (\Exception $e) {
                            $this->warn("Could not parse date/time from: {$saleInfo}");
                        }
                    }
                    
                    try {
                        $this->processSalesPhase(
                            fixture: $fixture,
                            name: $phaseName,
                            whoCanBuy: $phaseName,
                            pointsRequired: null,
                            saleDate: $saleDate,
                            saleTime: $saleTime,
                            newSalesPhases: $newSalesPhases,
                            forceSend: $forceSend
                        );
                    } catch (\Exception $e) {
                        $this->error("Error adding sales phase: " . $e->getMessage());
                    }
                }
            });
        }
        
        return $newSalesPhases;
    }
    
    /**
     * Process a responsive table containing sales phase information
     *
     * @param Crawler $table
     * @param Fixture $fixture
     * @param int &$newSalesPhases
     * @param bool $forceSend
     * @return void
     */
    protected function processResponseTable(Crawler $table, Fixture $fixture, &$newSalesPhases, $forceSend)
    {
        // Find the table and get header indexes
        $headerIndexes = [];
        
        // Get the first row of tbody as headers
        $headerRow = $table->filter('tbody tr')->first();
        
        if ($headerRow->count() > 0) {
            $headerRow->filter('td')->each(function (Crawler $cell, $index) use (&$headerIndexes) {
                // Get text from strong tag if it exists, otherwise use cell text
                $headerText = $cell->filter('strong')->count() > 0 
                    ? $cell->filter('strong')->text() 
                    : $cell->text();
                
                $headerText = strtolower(trim($headerText));
                
                if (stripos($headerText, 'sales phase') !== false) {
                    $headerIndexes['name'] = $index;
                } elseif (stripos($headerText, 'who can buy') !== false) {
                    $headerIndexes['who_can_buy'] = $index;
                } elseif (stripos($headerText, 'points required') !== false) {
                    $headerIndexes['points_required'] = $index;
                } elseif (stripos($headerText, 'sale date') !== false) {
                    $headerIndexes['sale_date'] = $index;
                } elseif (stripos($headerText, 'sale time') !== false) {
                    $headerIndexes['sale_time'] = $index;
                }
            });
            
            // Process each row for sales phases, skipping the header row
            $table->filter('tbody tr')->slice(1)->each(function (Crawler $row) use ($headerIndexes, $fixture, &$newSalesPhases, $forceSend) {
                $cells = $row->filter('td');
                
                if ($cells->count() > 0) {
                    // Extract data based on header indexes, getting text from strong tag if it exists
                    $rawName = isset($headerIndexes['name']) ? ($cells->eq($headerIndexes['name'])->filter('strong')->count() > 0 
                        ? $cells->eq($headerIndexes['name'])->filter('strong')->text() 
                        : $cells->eq($headerIndexes['name'])->text()) : null;
                    
                    // If name is missing, skip this row
                    if (empty($rawName)) {
                        return;
                    }
                    
                    $name = $this->cleanTextContent($rawName);
                    
                    $whoCanBuy = isset($headerIndexes['who_can_buy']) ? $this->cleanTextContent($cells->eq($headerIndexes['who_can_buy'])->filter('strong')->count() > 0 
                        ? $cells->eq($headerIndexes['who_can_buy'])->filter('strong')->text() 
                        : $cells->eq($headerIndexes['who_can_buy'])->text()) : null;
                    
                    $pointsRequired = isset($headerIndexes['points_required']) ? $this->cleanTextContent($cells->eq($headerIndexes['points_required'])->filter('strong')->count() > 0 
                        ? $cells->eq($headerIndexes['points_required'])->filter('strong')->text() 
                        : $cells->eq($headerIndexes['points_required'])->text()) : null;
                    
                    $saleDateText = isset($headerIndexes['sale_date']) ? $this->cleanTextContent($cells->eq($headerIndexes['sale_date'])->filter('strong')->count() > 0 
                        ? $cells->eq($headerIndexes['sale_date'])->filter('strong')->text() 
                        : $cells->eq($headerIndexes['sale_date'])->text()) : null;
                    
                    $saleTimeText = isset($headerIndexes['sale_time']) ? $this->cleanTextContent($cells->eq($headerIndexes['sale_time'])->filter('strong')->count() > 0 
                        ? $cells->eq($headerIndexes['sale_time'])->filter('strong')->text() 
                        : $cells->eq($headerIndexes['sale_time'])->text()) : null;
                    
                    // Parse sale date
                    $saleDate = null;
                    if ($saleDateText) {
                        // Remove all types of whitespace including non-breaking spaces
                        $saleDateText = preg_replace('/[\s\x{00A0}\x{2000}-\x{200A}\x{202F}\x{205F}\x{3000}]+/u', '', $saleDateText);
                        
                        try {
                            // First try DD/MM/YYYY format explicitly
                            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $saleDateText, $matches)) {
                                $saleDate = Carbon::createFromFormat('d/m/Y', $saleDateText)->toDateString();
                            } else {
                                // Fallback to Carbon's parse
                                $saleDate = Carbon::parse($saleDateText)->toDateString();
                            }
                        } catch (\Exception $e) {
                            $this->warn("Could not parse sale date: {$saleDateText}");
                        }
                    }
                    
                    // Clean sale time and remove any AM/PM indicators
                    $saleTime = null;
                    if ($saleTimeText) {
                        $saleTime = preg_replace('/\s*[aApP][mM]\s*$/', '', $saleTimeText);
                        // Make sure it's in HH:MM format
                        if (preg_match('/(\d{1,2})(?::(\d{2}))?/', $saleTime, $matches)) {
                            $hours = $matches[1];
                            $minutes = $matches[2] ?? '00';
                            $saleTime = "{$hours}:{$minutes}";
                            
                            // Adjust for AM/PM if present in the original text
                            if (preg_match('/[pP][mM]/', $saleTimeText) && $hours < 12) {
                                $hours = $hours + 12;
                                $saleTime = "{$hours}:{$minutes}";
                            }
                        }
                    }
                    
                    $this->processSalesPhase(
                        fixture: $fixture,
                        name: $name,
                        whoCanBuy: $whoCanBuy,
                        pointsRequired: $pointsRequired,
                        saleDate: $saleDate,
                        saleTime: $saleTime,
                        newSalesPhases: $newSalesPhases,
                        forceSend: $forceSend
                    );
                }
            });
        }
    }
    
    /**
     * Clean text content by removing extra whitespace, newlines, and strong tags
     *
     * @param string $text
     * @return string
     */
    protected function cleanTextContent($text)
    {
        // Remove HTML entities and decode
        $text = html_entity_decode($text);
        
        // Remove <strong> tags if they exist in the text
        $text = preg_replace('/<\/?strong>/', '', $text);
        
        // Remove extra whitespace and trim
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
    
    /**
     * Process a sales phase and determine if notification should be sent
     * 
     * @param Fixture $fixture
     * @param string|null $name
     * @param string|null $whoCanBuy
     * @param string|null $pointsRequired
     * @param string|null $saleDate
     * @param string|null $saleTime
     * @param int &$newSalesPhases
     * @param bool $forceSend
     * @return void
     */
    protected function processSalesPhase(
        Fixture $fixture,
        ?string $name,
        ?string $whoCanBuy,
        ?string $pointsRequired,
        ?string $saleDate,
        ?string $saleTime,
        int &$newSalesPhases,
        bool $forceSend
    ) {
        if (empty($name)) {
            return;
        }
        
        $this->info("Processing sales phase for fixture {$fixture->id}:");
        $this->info("Name: {$name}");
        $this->info("Who can buy: {$whoCanBuy}");
        $this->info("Points required: {$pointsRequired}");
        $this->info("Sale date: {$saleDate}");
        $this->info("Sale time: {$saleTime}");
        
        // Create or update the sales phase
        $salesPhase = TicketSalesPhase::firstOrNew([
            'fixture_id' => $fixture->id,
            'sales_phase' => $name,
        ]);
        
        $isNew = !$salesPhase->exists;
        
        $salesPhase->fill([
            'who_can_buy' => $whoCanBuy,
            'points_required' => $pointsRequired,
            'sale_date' => $saleDate,
            'sale_time' => $saleTime,
        ]);
        
        // Only save if new or changed
        if ($salesPhase->isDirty() || $isNew) {
            $salesPhase->save();
            
            if ($isNew) {
                $newSalesPhases++;
                $this->info("New sales phase for {$fixture->team}: {$name}");
                
                // Send notification for new sales phase
                if (!$salesPhase->notified || $forceSend) {
                    $this->sendSalesPhaseNotification($fixture, $salesPhase);
                    $salesPhase->notified = true;
                    $salesPhase->save();
                }
            }
        }
    }

    /**
     * Send notification about new ticket sales phase.
     *
     * @param Fixture $fixture
     * @param TicketSalesPhase $salesPhase
     * @return void
     */
    protected function sendSalesPhaseNotification(Fixture $fixture, TicketSalesPhase $salesPhase)
    {
        // Check if notifications are enabled via environment variable
        if (!env('SEND_TICKET_NOTIFICATIONS', true)) {
            $this->info("Notifications are disabled via SEND_TICKET_NOTIFICATIONS environment variable");
            return;
        }

        $this->info("Sending notification for {$fixture->team} - {$salesPhase->name}");
        
        try {
            // Get all users who should receive notifications
            $users = User::all();
            
            if ($users->isNotEmpty()) {
                Notification::send($users, new NewTicketSalesPhase($fixture, $salesPhase));
                $this->info("Notification sent to {$users->count()} users");
            } else {
                $this->warn("No users found to notify");
            }
        } catch (\Exception $e) {
            $this->error("Failed to send notification: {$e->getMessage()}");
            Log::error("Notification error: {$e->getMessage()}", [
                'exception' => $e,
            ]);
        }
    }

    /**
     * Extract ticket URL from a node.
     *
     * @param Crawler $node
     * @return string|null
     */
    protected function extractTicketUrl(Crawler $node): ?string
    {
        $ticketUrl = null;
        
        // First try to find URL in ticket-card__ctas
        $node->filter('.ticket-card__ctas a[href^="/tickets/"]')->each(function (Crawler $link) use (&$ticketUrl) {
            if (strpos($link->attr('href'), '/tickets/') === 0) {
                $ticketUrl = $this->baseUrl . $link->attr('href');
            }
        });
        
        // If not found, try any link containing /tickets/
        if (!$ticketUrl) {
            $node->filter('a')->each(function (Crawler $link) use (&$ticketUrl) {
                if ($ticketUrl === null && strpos($link->attr('href'), '/tickets/') === 0) {
                    $ticketUrl = $this->baseUrl . $link->attr('href');
                }
            });
        }
        
        return $ticketUrl;
    }

    /**
     * Parse date and time from text.
     *
     * @param string $dateText
     * @param string|null $timeText
     * @param string|null $ticketUrl
     * @return Carbon
     */
    protected function parseDateTime(string $dateText, ?string $timeText = null, ?string $ticketUrl = null): Carbon
    {
        $fullDateString = trim($dateText . ' ' . $timeText);
        
        try {
            // Add the year since it might be missing in the date string
            if (!preg_match('/\d{4}/', $fullDateString)) {
                // Determine if it's likely this year or next
                $currentMonth = Carbon::now()->month;
                $matchMonth = Carbon::parse($dateText)->month;
                
                // If the match month is earlier in the year than current month, it's likely next year
                $year = $matchMonth < $currentMonth ? Carbon::now()->year + 1 : Carbon::now()->year;
                $fullDateString .= ', ' . $year;
            }
            
            return Carbon::parse($fullDateString);
        } catch (\Exception $e) {
            $this->warn("Error parsing date '{$fullDateString}': " . $e->getMessage());
            
            // Try to extract date from ticket URL as fallback
            if ($ticketUrl && preg_match('/\/(\d{4})-([A-Za-z]+)-(\d{1,2})\//', $ticketUrl, $matches)) {
                $year = $matches[1];
                $month = $matches[2];
                $day = $matches[3];
                try {
                    return Carbon::parse("{$day} {$month} {$year} {$timeText}");
                } catch (\Exception $e2) {
                    $this->warn("Error parsing date from URL: " . $e2->getMessage());
                }
            }
            
            // Fallback to current date
            return Carbon::now();
        }
    }

    /**
     * Extract time from kickoff text.
     *
     * @param string $kickoffText
     * @return string|null
     */
    protected function extractTimeFromKickoff(string $kickoffText): ?string
    {
        if (preg_match('/(\d{1,2}:\d{2})/', $kickoffText, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Determine season from date.
     *
     * @param Carbon $date
     * @return string
     */
    protected function determineSeason(Carbon $date): string
    {
        return $date->month < 8 
            ? ($date->year - 1) . '-' . substr($date->year, -2)
            : $date->year . '-' . substr(($date->year + 1), -2);
    }
}
