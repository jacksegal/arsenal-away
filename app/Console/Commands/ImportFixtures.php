<?php

namespace App\Console\Commands;

use App\Enums\Competition;
use App\Enums\Opposition;
use App\Enums\Season;
use App\Models\Fixture;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:import-fixtures {files* : CSV file paths to import}')]
#[Description('Import Premier League away fixtures from CSV files')]
class ImportFixtures extends Command
{
    /** @var array<string, string> */
    private array $teamNameMap = [
        'Nottingham Forest' => "Nott'm Forest",
    ];

    public function handle(): int
    {
        $files = $this->argument('files');
        $totalImported = 0;

        foreach ($files as $file) {
            if (! file_exists($file)) {
                $this->error("File not found: {$file}");

                continue;
            }

            $imported = $this->importFile($file);
            $totalImported += $imported;
            $this->info("Imported {$imported} away fixtures from {$file}");
        }

        $this->info("Total: {$totalImported} away fixtures imported.");

        return self::SUCCESS;
    }

    private function importFile(string $filePath): int
    {
        $filename = basename($filePath);
        $season = $this->resolveSeasonFromFilename($filename);
        $isUtc = str_contains($filename, 'UTC');

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            if ($data['Away Team'] !== 'Arsenal') {
                continue;
            }

            $opposition = $this->normaliseTeamName($data['Home Team']);
            $oppositionEnum = Opposition::tryFrom($opposition);

            if (! $oppositionEnum) {
                $this->warn("Unknown opposition: {$opposition} — skipping");

                continue;
            }

            $fixtureDate = $this->parseDate($data['Date'], $isUtc);
            $gameWeek = 'GW'.$data['Round Number'];

            Fixture::updateOrCreate(
                [
                    'season' => $season,
                    'opposition' => $oppositionEnum,
                    'competition' => Competition::PremierLeague,
                ],
                [
                    'fixture_date' => $fixtureDate,
                    'game_week' => $gameWeek,
                ],
            );

            $imported++;
        }

        fclose($handle);

        return $imported;
    }

    private function resolveSeasonFromFilename(string $filename): Season
    {
        preg_match('/epl-(\d{4})/', $filename, $matches);
        $startYear = (int) $matches[1];
        $short = substr((string) $startYear, 2).'/'.substr((string) ($startYear + 1), 2);

        return Season::from($short);
    }

    private function normaliseTeamName(string $name): string
    {
        return $this->teamNameMap[$name] ?? $name;
    }

    private function parseDate(string $dateString, bool $isUtc): Carbon
    {
        if ($isUtc) {
            return Carbon::createFromFormat('d/m/Y H:i', $dateString, 'UTC')
                ->setTimezone('Europe/London');
        }

        return Carbon::createFromFormat('d/m/Y H:i', $dateString, 'Europe/London');
    }
}
