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
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->string('season')->default('25/26');
            $table->string('competition');
            $table->string('opposition');
            $table->unsignedInteger('allocation')->nullable();
            $table->date('fixture_date')->nullable();
            $table->unsignedInteger('starting_sale_points')->nullable();
            $table->unsignedInteger('sell_out_points')->nullable();
            $table->string('arsenal_ticket_link')->nullable();
            $table->string('game_week')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
