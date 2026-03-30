<?php

namespace App\Enums;

enum Opposition: string
{
    // Domestic
    case AstonVilla = 'Aston Villa';
    case Bournemouth = 'Bournemouth';
    case Brentford = 'Brentford';
    case Brighton = 'Brighton';
    case Burnley = 'Burnley';
    case Cardiff = 'Cardiff';
    case Chelsea = 'Chelsea';
    case CrystalPalace = 'Crystal Palace';
    case Everton = 'Everton';
    case Fulham = 'Fulham';
    case Huddersfield = 'Huddersfield';
    case Ipswich = 'Ipswich';
    case Leeds = 'Leeds';
    case Leicester = 'Leicester';
    case Liverpool = 'Liverpool';
    case Luton = 'Luton';
    case ManCity = 'Man City';
    case Mansfield = 'Mansfield';
    case ManUtd = 'Man Utd';
    case Newcastle = 'Newcastle';
    case Norwich = 'Norwich';
    case NottmForest = "Nott'm Forest";
    case OxfordUnited = 'Oxford United';
    case Portsmouth = 'Portsmouth';
    case PortVale = 'Port Vale';
    case Preston = 'Preston';
    case SheffieldUtd = 'Sheffield Utd';
    case Southampton = 'Southampton';
    case Spurs = 'Spurs';
    case Stoke = 'Stoke';
    case Sunderland = 'Sunderland';
    case Swansea = 'Swansea';
    case Watford = 'Watford';
    case WestBrom = 'West Brom';
    case WestHam = 'West Ham';
    case Wolves = 'Wolves';

    // European
    case Atalanta = 'Atalanta';
    case Bayern = 'Bayern';
    case Bilbao = 'Bilbao';
    case ClubBrugge = 'Club Brugge';
    case FCPorto = 'FC Porto';
    case Girona = 'Girona';
    case Inter = 'Inter';
    case Leverkusen = 'Leverkusen';
    case Madrid = 'Madrid';
    case PSG = 'PSG';
    case PSVEindhoven = 'PSV Eindhoven';
    case RCLens = 'RC Lens';
    case Sevilla = 'Sevilla';
    case SlaviaPrague = 'Slavia Prague';
    case Sporting = 'Sporting';

    public function opponentKey(): string
    {
        return match ($this) {
            self::AstonVilla => 'aston-villa',
            self::Bournemouth => 'bournemouth',
            self::Brentford => 'brentford',
            self::Brighton => 'brighton-hove-albion',
            self::Burnley => 'burnley',
            self::Cardiff => 'cardiff-city',
            self::Chelsea => 'chelsea',
            self::CrystalPalace => 'crystal-palace',
            self::Everton => 'everton',
            self::Fulham => 'fulham',
            self::Huddersfield => 'huddersfield-town',
            self::Ipswich => 'ipswich-town-fc',
            self::Leeds => 'leeds-united',
            self::Leicester => 'leicester-city',
            self::Liverpool => 'liverpool',
            self::Luton => 'luton-town',
            self::ManCity => 'manchester-city',
            self::Mansfield => 'mansfield-town',
            self::ManUtd => 'manchester-united',
            self::Newcastle => 'newcastle-united',
            self::Norwich => 'norwich-city',
            self::NottmForest => 'nottingham-forest',
            self::OxfordUnited => 'oxford-united',
            self::Portsmouth => 'portsmouth',
            self::PortVale => 'port-vale',
            self::Preston => 'preston-north-end',
            self::SheffieldUtd => 'sheffield-united',
            self::Southampton => 'southampton',
            self::Spurs => 'tottenham-hotspur',
            self::Stoke => 'stoke-city',
            self::Sunderland => 'sunderland',
            self::Swansea => 'swansea-city',
            self::Watford => 'watford',
            self::WestBrom => 'west-bromwich-albion',
            self::WestHam => 'west-ham-united',
            self::Wolves => 'wolves',
            self::Atalanta => 'atalanta-bergamasca-calcio',
            self::Bayern => 'bayern-munich',
            self::Bilbao => 'athletic-club',
            self::ClubBrugge => 'club-brugge-kv',
            self::FCPorto => 'fc-porto',
            self::Girona => 'girona',
            self::Inter => 'inter-milan',
            self::Leverkusen => 'bayer-leverkusen',
            self::Madrid => 'real-madrid-cf',
            self::PSG => 'paris-saint-germain-fc',
            self::PSVEindhoven => 'psv-eindhoven',
            self::RCLens => 'rc-lens',
            self::Sevilla => 'sevilla',
            self::SlaviaPrague => 'sk-slavia-praha',
            self::Sporting => 'sporting-clube-de-portugal',
        };
    }
}
