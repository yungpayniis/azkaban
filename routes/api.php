<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiscordBotController;

Route::post('discord/name-change', [DiscordBotController::class, 'nameChange']);
