<?php

namespace App\Enums;

enum Competition: string
{
    case PremierLeague = 'Premier League';
    case ChampionsLeague = 'Champions League';
    case FaCup = 'FA Cup';
    case CarabaoCup = 'Carabao Cup';
}
