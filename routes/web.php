<?php

use App\Http\Controllers\JobClassController;
use App\Http\Controllers\PartyPlannerController;
use App\Http\Controllers\KvmPartyPlannerController;
use App\Http\Controllers\GuildMemberController;
use App\Http\Controllers\GvgWeeklyStatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('guild-members.index');
});

Route::resource('job-classes', JobClassController::class);
Route::resource('guild-members', GuildMemberController::class);
Route::get('gvg-weekly-stats/summary', [GvgWeeklyStatController::class, 'summary'])
    ->name('gvg-weekly-stats.summary');
Route::resource('gvg-weekly-stats', GvgWeeklyStatController::class);

Route::get('party-planner', [PartyPlannerController::class, 'index'])->name('party-planner.index');
Route::post('party-planner/parties', [PartyPlannerController::class, 'store'])->name('party-planner.parties.store');
Route::patch('party-planner/parties/{party}', [PartyPlannerController::class, 'update'])
    ->name('party-planner.parties.update');
Route::delete('party-planner/parties/{party}', [PartyPlannerController::class, 'destroy'])->name('party-planner.parties.destroy');
Route::post('party-planner/slots', [PartyPlannerController::class, 'updateSlots'])
    ->name('party-planner.slots.update');
Route::post('party-planner/auto-assign', [PartyPlannerController::class, 'autoAssign'])
    ->name('party-planner.auto-assign');

Route::get('kvm-planner', [KvmPartyPlannerController::class, 'index'])->name('kvm-planner.index');
Route::post('kvm-planner/parties', [KvmPartyPlannerController::class, 'store'])->name('kvm-planner.parties.store');
Route::post('kvm-planner/slots', [KvmPartyPlannerController::class, 'updateSlots'])
    ->name('kvm-planner.slots.update');
