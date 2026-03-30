<?php

namespace App\Console\Commands;

use App\Enums\Competition;
use App\Enums\Opposition;
use App\Enums\Season;
use App\Models\Fixture;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:import-cup-fixtures {files* : CSV file paths to import}')]
#[Description('Import cup and European away fixtures from CSV files')]
class ImportCupFixtures extends Command
{
    /** @var array<string, string> */
    private array $teamNameMap = [
        'Brentford FC' => 'Brentford',
        'West Ham United' => 'West Ham',
        'Manchester City' => 'Man City',
        'Sevilla FC' => 'Sevilla',
        'Club Brugges' => 'Club Brugge',
        'Oxford United' => 'Oxford United',
        'Port Vale' => 'Port Vale',
        'PSV Eindhoven' => 'PSV Eindhoven',
        'RC Lens' => 'RC Lens',
        'FC Porto' => 'FC Porto',
        'Slavia Prague' => 'Slavia Prague',
    ];

    /**
     * Fixture dates and rounds from research.
     * Key: "season|opposition|competition"
     *
     * @var array<string, array{date: string, round: string}>
     */
    private array $fixtureLookup = [
        // Cups
        '25/26|Mansfield|FA Cup' => ['date' => '2026-01-03', 'round' => 'R3'],
        '23/24|Brentford|Carabao Cup' => ['date' => '2023-09-27', 'round' => 'R3'],
        '22/23|Oxford United|FA Cup' => ['date' => '2023-01-09', 'round' => 'R3'],
        '25/26|Portsmouth|FA Cup' => ['date' => '2026-01-24', 'round' => 'R4'],
        '25/26|Port Vale|Carabao Cup' => ['date' => '2025-09-17', 'round' => 'R3'],
        '25/26|Chelsea|Carabao Cup' => ['date' => '2025-10-29', 'round' => 'R4'],
        '25/26|Southampton|FA Cup' => ['date' => '2026-02-14', 'round' => 'R5'],
        '24/25|Newcastle|Carabao Cup' => ['date' => '2025-02-05', 'round' => 'SF'],
        '23/24|West Ham|Carabao Cup' => ['date' => '2023-11-01', 'round' => 'R4'],
        '24/25|Preston|Carabao Cup' => ['date' => '2024-10-30', 'round' => 'R4'],
        '22/23|Man City|FA Cup' => ['date' => '2023-01-27', 'round' => 'R4'],

        // Champions League 23/24
        '23/24|RC Lens|Champions League' => ['date' => '2023-10-03', 'round' => 'MD2'],
        '23/24|Sevilla|Champions League' => ['date' => '2023-10-24', 'round' => 'MD3'],
        '23/24|PSV Eindhoven|Champions League' => ['date' => '2023-12-12', 'round' => 'MD6'],
        '23/24|FC Porto|Champions League' => ['date' => '2024-02-21', 'round' => 'R16'],
        '23/24|Bayern|Champions League' => ['date' => '2024-04-17', 'round' => 'QF'],

        // Champions League 24/25
        '24/25|Atalanta|Champions League' => ['date' => '2024-09-19', 'round' => 'MD1'],
        '24/25|Inter|Champions League' => ['date' => '2024-11-06', 'round' => 'MD4'],
        '24/25|Sporting|Champions League' => ['date' => '2024-11-26', 'round' => 'MD5'],
        '24/25|Girona|Champions League' => ['date' => '2025-01-22', 'round' => 'MD7'],
        '24/25|PSV Eindhoven|Champions League' => ['date' => '2025-01-29', 'round' => 'MD8'],
        '24/25|Madrid|Champions League' => ['date' => '2025-04-08', 'round' => 'QF'],
        '24/25|PSG|Champions League' => ['date' => '2025-05-07', 'round' => 'SF'],

        // Champions League 25/26
        '25/26|Bilbao|Champions League' => ['date' => '2025-09-16', 'round' => 'MD1'],
        '25/26|Slavia Prague|Champions League' => ['date' => '2025-11-04', 'round' => 'MD4'],
        '25/26|Club Brugge|Champions League' => ['date' => '2025-12-10', 'round' => 'MD6'],
        '25/26|Inter|Champions League' => ['date' => '2026-01-20', 'round' => 'MD7'],
        '25/26|Leverkusen|Champions League' => ['date' => '2026-03-11', 'round' => 'R16'],
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
            $this->info("Imported {$imported} fixtures from {$file}");
        }

        $this->info("Total: {$totalImported} fixtures imported.");

        return self::SUCCESS;
    }

    private function importFile(string $filePath): int
    {
        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);
        $headers = array_map('trim', $headers);
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            $data = array_map('trim', $data);

            $season = $this->parseSeason($data['Season']);
            $competition = $this->parseCompetition($data['Cup'] ?? $data['Competition'] ?? '');
            $opposition = $this->resolveOpposition($data['Opposition'] ?? $data['team'] ?? '');

            if (! $opposition) {
                $this->warn('Unknown opposition: '.($data['Opposition'] ?? $data['team'] ?? 'empty').' — skipping');

                continue;
            }

            $lookupKey = "{$season->value}|{$opposition->value}|{$competition->value}";
            $fixtureInfo = $this->fixtureLookup[$lookupKey] ?? null;

            Fixture::updateOrCreate(
                [
                    'season' => $season,
                    'opposition' => $opposition,
                    'competition' => $competition,
                ],
                array_filter([
                    'allocation' => $this->parseNumber($data['Allocation'] ?? null),
                    'sell_out_points' => $this->parseNumber($data['Points sell out'] ?? null),
                    'starting_sale_points' => $this->parseNumber($data['Initial points'] ?? null),
                    'fixture_date' => $fixtureInfo['date'] ?? null,
                    'game_week' => $fixtureInfo['round'] ?? null,
                ], fn ($v) => $v !== null),
            );

            $imported++;
        }

        fclose($handle);

        return $imported;
    }

    private function parseSeason(string $value): Season
    {
        // "2025/2026" → "25/26"
        preg_match('/(\d{4})\/(\d{4})/', $value, $matches);

        return Season::from(substr($matches[1], 2).'/'.substr($matches[2], 2));
    }

    private function parseCompetition(string $value): Competition
    {
        return match (strtolower(trim($value))) {
            'fa cup' => Competition::FaCup,
            'carabao' => Competition::CarabaoCup,
            'cl' => Competition::ChampionsLeague,
            default => Competition::ChampionsLeague,
        };
    }

    private function resolveOpposition(string $name): ?Opposition
    {
        $normalised = $this->teamNameMap[$name] ?? $name;

        return Opposition::tryFrom($normalised);
    }

    private function parseNumber(?string $value): ?int
    {
        $value = trim($value ?? '');

        if ($value === '') {
            return null;
        }

        return (int) str_replace(',', '', $value);
    }
}
