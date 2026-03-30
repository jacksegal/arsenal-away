<?php

use App\Livewire\FixtureTable;
use Illuminate\Support\Facades\Route;

Route::livewire('/', FixtureTable::class)->name('home');
