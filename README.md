# Arsenal Away

Track Arsenal away fixture ticket information — points needed, allocations, sell-out thresholds, and more.

## Tech Stack

- **PHP 8.4** / **Laravel 13**
- **Filament 5** — Admin panel at `/admin`
- **Livewire 4** + **Alpine.js** — Public frontend
- **Tailwind CSS 4** — Styling
- **SQLite** — Database
- **Laravel Herd** — Local development server

## Setup

```bash
composer install
npm install
php artisan migrate
npm run build
php artisan make:filament-user   # Create admin login
```

## Development

```bash
composer run dev   # Runs server, queue, logs, and Vite concurrently
```

Or individually:

```bash
npm run dev        # Vite dev server with HMR
```

The site is served by Laravel Herd at `https://arsenal-away.test`.

## Importing Fixtures

Import Premier League away fixtures from CSV files (sourced from fixturedownload.com):

```bash
php artisan app:import-fixtures /path/to/epl-2025-arsenal-GMTStandardTime.csv
```

- Only Arsenal **away** games are imported (home games are skipped)
- Files with `UTC` in the filename have dates converted to UK time automatically
- Season is derived from the filename (e.g. `epl-2023` → `23/24`)
- The command uses `updateOrCreate` so it's safe to re-run
- Team names are normalised (e.g. `Nottingham Forest` → `Nott'm Forest`)

Currently imported: **9 seasons** (17/18 to 25/26), **171 PL away fixtures**.

## Key URLs

| URL | Description |
|-----|-------------|
| `/` | Public fixture table with season/competition filters |
| `/admin` | Filament admin panel (CRUD for fixtures) |

## Data Model

The core model is `Fixture` with three enum-backed fields:

- **Season** (`app/Enums/Season.php`) — `17/18` through `25/26`
- **Competition** (`app/Enums/Competition.php`) — Premier League, Champions League, FA Cup, Carabao Cup
- **Opposition** (`app/Enums/Opposition.php`) — 30 teams across all tracked seasons

Other fields: allocation, fixture_date, starting_sale_points, sell_out_points, arsenal_ticket_link, game_week, notes.

## Data Sources

- **Premier League fixtures**: [fixturedownload.com](https://fixturedownload.com/results/epl-2025) — download the "Arsenal" CSV in GMT Standard Time. Change the year in the URL for other seasons (e.g. `epl-2024`, `epl-2023`).
- **Ticket sale info**: Manually entered via the Filament admin panel.

## Testing

```bash
php artisan test --compact
```
