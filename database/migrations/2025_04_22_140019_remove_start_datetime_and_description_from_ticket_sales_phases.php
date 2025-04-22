<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ticket_sales_phases', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_sales_phases', 'start_datetime')) {
                $table->dropColumn('start_datetime');
            }
            if (Schema::hasColumn('ticket_sales_phases', 'description')) {
                $table->dropColumn('description');
            }
        });
    }


}; 