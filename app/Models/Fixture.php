<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fixture extends Model
{
    protected $fillable = [
        'team',
        'competition',
        'date',
        'is_home',
        'season',
        'ticket_url',
        'away_points_sold_out',
        'away_allocation_tickets',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_home' => 'boolean',
        'away_points_sold_out' => 'integer',
        'away_allocation_tickets' => 'integer',
    ];

    public function salesPhases()
    {
        return $this->hasMany(TicketSalesPhase::class);
    }

    public function isAway()
    {
        return !$this->is_home;
    }
}
