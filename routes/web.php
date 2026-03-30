<?php

use App\Livewire\FixtureTable;
use Illuminate\Support\Facades\Route;

Route::get('/', FixtureTable::class)->name('home');
