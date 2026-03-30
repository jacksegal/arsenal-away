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

Import ticket sale data (points, allocations) from Google Sheets exports:

```bash
php artisan app:import-away-points /path/to/Away\ Points\ Calculator\ -\ 25_26.csv
```

Import cup and European away fixtures:

```bash
php artisan app:import-cup-fixtures /path/to/Cups.csv /path/to/Europe.csv
```

Currently imported: **199 away fixtures** — 171 PL (9 seasons), 17 Champions League, 6 Carabao Cup, 5 FA Cup.

## Key URLs

| URL | Description |
|-----|-------------|
| `/` | Public fixture table with season/competition filters |
| `/admin` | Filament admin panel (CRUD for fixtures) |

## Data Model

The core model is `Fixture` with three enum-backed fields:

- **Season** (`app/Enums/Season.php`) — `17/18` through `25/26`
- **Competition** (`app/Enums/Competition.php`) — Premier League, Champions League, FA Cup, Carabao Cup
- **Opposition** (`app/Enums/Opposition.php`) — 51 teams (31 domestic + 15 European) with `opponentKey()` method for arsenal.com URL slugs

Other fields: allocation, fixture_date, starting_sale_points, sell_out_points, arsenal_ticket_link, game_week, notes.

## Data Sources

- **Premier League fixtures**: [fixturedownload.com](https://fixturedownload.com/results/epl-2025) — download the "Arsenal" CSV in GMT Standard Time. Change the year in the URL for other seasons (e.g. `epl-2024`, `epl-2023`).
- **Ticket sale data**: Google Sheets exports ("Away Points Calculator" spreadsheets) imported via `app:import-away-points`.
- **Cup/European fixtures**: Google Sheets exports imported via `app:import-cup-fixtures`. Fixture dates researched manually or via web search.
- **Arsenal ticket links**: Found by searching "Arsenal vs {opponent} AWAY TICKETS" and looking for `arsenal.com/tickets/arsenal/{date}/{opponent-key}`. Always verify: (1) it's an **away** match, (2) correct opponent, (3) correct competition/season. The URL format is `https://www.arsenal.com/tickets/arsenal/{YYYY-Mon-DD}/{opponent-key}`.

## Testing

```bash
php artisan test --compact
```
