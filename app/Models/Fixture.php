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
        'arsenal_url',
        'ticket_url',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_home' => 'boolean',
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
