<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketSalesPhase extends Model
{
    protected $fillable = [
        'fixture_id',
        'sales_phase',
        'who_can_buy',
        'points_required',
        'sale_date',
        'sale_time',
        'notified',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'notified' => 'boolean',
    ];

    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }
}
