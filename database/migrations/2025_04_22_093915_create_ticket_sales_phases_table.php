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
        Schema::create('ticket_sales_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained()->onDelete('cascade');
            $table->string('sales_phase');
            $table->string('who_can_buy')->nullable();
            $table->string('points_required')->nullable();
            $table->date('sale_date')->nullable();
            $table->string('sale_time')->nullable();
            $table->boolean('notified')->default(false);
            $table->timestamps();
            
            $table->unique(['fixture_id', 'sales_phase']);
        });
    }
};
