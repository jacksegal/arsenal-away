<?php

namespace App\Console\Commands;

use App\Enums\Competition;
use App\Enums\Opposition;
use App\Enums\Season;
use App\Models\Fixture;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:import-away-points {files* : CSV file paths to import}')]
#[Description('Import away ticket sale data (points, allocations, links) from CSV files')]
class ImportAwayPoints extends Command
{
    /** @var array<string, string> */
    private array $teamNameMap = [
        'Leicester City' => 'Leicester',
        'AFC Bournemouth' => 'Bournemouth',
        'Wolverhampton Wanderers' => 'Wolves',
        'Brighton & Hove Albion' => 'Brighton',
        'Tottenham Hotspur' => 'Spurs',
        'Manchester City' => 'Man City',
        'Manchester United' => 'Man Utd',
        'Newcastle United' => 'Newcastle',
        'Stoke City' => 'Stoke',
        'Southampton FC' => 'Southampton',
        'West Bromwich Albion' => 'West Brom',
        'Crystal Palace' => 'Crystal Palace',
        'West Ham United' => 'West Ham',
        'Chelsea FC' => 'Chelsea',
        'Everton FC' => 'Everton',
        'Burnley FC' => 'Burnley',
        'Watford FC' => 'Watford',
        'Swansea City' => 'Swansea',
        'Huddersfield Town' => 'Huddersfield',
        'Cardiff City' => 'Cardiff',
        'Norwich City' => 'Norwich',
        'Leeds United' => 'Leeds',
        'Sheffield United' => 'Sheffield Utd',
        'Fulham FC' => 'Fulham',
        'Liverpool FC' => 'Liverpool',
        'Brentford FC' => 'Brentford',
        'Luton Town' => 'Luton',
        'Nottingham Forest' => "Nott'm Forest",
        'Ipswich Town' => 'Ipswich',
        'Sunderland' => 'Sunderland',
        // 25/26 lowercase/mixed variants
        'man utd' => 'Man Utd',
        'liverpool' => 'Liverpool',
        'newcastle' => 'Newcastle',
        'fulham' => 'Fulham',
        'chelsea' => 'Chelsea',
        'Bournemouth' => 'Bournemouth',
        'Leeds' => 'Leeds',
        'Brentford' => 'Brentford',
        'Brighton' => 'Brighton',
        'Wolves' => 'Wolves',
        'Man City' => 'Man City',
        'West Ham' => 'West Ham',
        'Burnley' => 'Burnley',
        'Sunderland' => 'Sunderland',
    ];

    public function handle(): int
    {
        $files = $this->argument('files');
        $totalUpdated = 0;

        foreach ($files as $file) {
            if (! file_exists($file)) {
                $this->error("File not found: {$file}");

                continue;
            }

            $updated = $this->importFile($file);
            $totalUpdated += $updated;
            $this->info("Updated {$updated} fixtures from {$file}");
        }

        $this->info("Total: {$totalUpdated} fixtures updated.");

        return self::SUCCESS;
    }

    private function importFile(string $filePath): int
    {
        $filename = basename($filePath);
        $season = $this->resolveSeasonFromFilename($filename);

        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);

        $is2526 = $season === Season::Season_25_26 && in_array('team', $headers);
        $hasDuplicatePoints = count(array_keys($headers, 'points')) > 1;

        $updated = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if ($is2526) {
                $updated += $this->import2526Row($row, $headers, $season);
            } elseif ($hasDuplicatePoints) {
                $updated += $this->importDuplicatePointsRow($row, $headers, $season);
            } else {
                $updated += $this->importStandardRow($row, $headers, $season);
            }
        }

        fclose($handle);

        return $updated;
    }

    /**
     * 25/26 format: team, allocation, day of week, starting points, sell out points, link
     */
    private function import2526Row(array $row, array $headers, Season $season): int
    {
        $data = array_combine($headers, $row);
        $opposition = $this->resolveOpposition(trim($data['team']));

        if (! $opposition) {
            return 0;
        }

        $fixture = $this->findFixture($season, $opposition);

        if (! $fixture) {
            $this->warn("No fixture found for {$season->value} {$opposition->value}");

            return 0;
        }

        $updates = array_filter([
            'allocation' => $this->parseAllocation($data['allocation'] ?? null),
            'starting_sale_points' => $this->parsePoints($data['starting points'] ?? null),
            'sell_out_points' => $this->parsePoints($data['sell out points'] ?? null),
            'arsenal_ticket_link' => $this->parseLink($data['link '] ?? $data['link'] ?? null),
        ], fn ($v) => $v !== null);

        if ($updates) {
            $fixture->update($updates);
        }

        return 1;
    }

    /**
     * 18/19 and 19/20: duplicate 'points' headers — use index-based access.
     * Col 4 = Opponent, col 5 = points (sell out), col 9 = arsenal ticket link.
     */
    private function importDuplicatePointsRow(array $row, array $headers, Season $season): int
    {
        $opponentName = trim($row[4] ?? '');
        $opposition = $this->resolveOpposition($opponentName);

        if (! $opposition) {
            return 0;
        }

        $fixture = $this->findFixture($season, $opposition);

        if (! $fixture) {
            $this->warn("No fixture found for {$season->value} {$opposition->value}");

            return 0;
        }

        $pointsRaw = trim($row[5] ?? '');
        $linkRaw = trim($row[9] ?? '');

        $isCovid = strtoupper($pointsRaw) === 'COVID';
        $sellOutPoints = $isCovid ? null : $this->parsePoints($pointsRaw);

        $updates = array_filter([
            'sell_out_points' => $sellOutPoints,
            'arsenal_ticket_link' => $this->parseLink($linkRaw),
        ], fn ($v) => $v !== null);

        if ($isCovid) {
            $updates['sell_out_points'] = null;
            $updates['notes'] = 'COVID';
        }

        if ($updates) {
            $fixture->update($updates);
        }

        return 1;
    }

    /**
     * Standard format: uses named headers (no duplicate points).
     * Covers 17/18, 20/21, 21/22, 22/23, 23/24, 24/25.
     */
    private function importStandardRow(array $row, array $headers, Season $season): int
    {
        $data = array_combine($headers, $row);
        $opponentName = trim($data['Opponent'] ?? '');
        $opposition = $this->resolveOpposition($opponentName);

        if (! $opposition) {
            return 0;
        }

        $fixture = $this->findFixture($season, $opposition);

        if (! $fixture) {
            $this->warn("No fixture found for {$season->value} {$opposition->value}");

            return 0;
        }

        $pointsRaw = trim($data['points'] ?? '');
        $isCovid = strtoupper($pointsRaw) === 'COVID';
        $sellOutPoints = $isCovid ? null : $this->parsePoints($pointsRaw);

        $updates = array_filter([
            'sell_out_points' => $sellOutPoints,
            'starting_sale_points' => $this->parsePoints($data['initial points'] ?? null),
            'allocation' => $this->parseAllocation($data['allocation'] ?? null),
            'arsenal_ticket_link' => $this->parseLink($data['url'] ?? null),
        ], fn ($v) => $v !== null);

        if ($isCovid) {
            $updates['sell_out_points'] = null;
            $updates['notes'] = 'COVID';
        }

        if ($updates) {
            $fixture->update($updates);
        }

        return 1;
    }

    private function resolveSeasonFromFilename(string $filename): Season
    {
        preg_match('/(\d{2})_(\d{2})/', $filename, $matches);

        return Season::from("{$matches[1]}/{$matches[2]}");
    }

    private function resolveOpposition(string $name): ?Opposition
    {
        $normalised = $this->teamNameMap[$name] ?? $name;

        return Opposition::tryFrom($normalised);
    }

    private function findFixture(Season $season, Opposition $opposition): ?Fixture
    {
        return Fixture::where('season', $season)
            ->where('opposition', $opposition)
            ->where('competition', Competition::PremierLeague)
            ->first();
    }

    private function parsePoints(?string $value): ?int
    {
        $value = trim($value ?? '');

        if ($value === '' || strtoupper($value) === 'COVID') {
            return null;
        }

        return (int) str_replace(',', '', $value);
    }

    private function parseAllocation(?string $value): ?int
    {
        $value = trim($value ?? '');

        if ($value === '') {
            return null;
        }

        return (int) str_replace(',', '', $value);
    }

    private function parseLink(?string $value): ?string
    {
        $value = trim($value ?? '');

        return $value !== '' ? $value : null;
    }
}
