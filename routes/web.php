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
Route::get('guild-members/left', [GuildMemberController::class, 'leftMembers'])
    ->name('guild-members.left');
Route::resource('guild-members', GuildMemberController::class);
Route::post('guild-members/{guildMember}/red-cards', [GuildMemberController::class, 'issueRedCard'])
    ->name('guild-members.red-cards.store');
Route::get('gvg-weekly-stats/summary', [GvgWeeklyStatController::class, 'summary'])
    ->name('gvg-weekly-stats.summary');
Route::delete('gvg-weekly-stats/summary', [GvgWeeklyStatController::class, 'destroyWeek'])
    ->name('gvg-weekly-stats.summary.destroy-week');
Route::get('gvg-weekly-stats/import-json', [GvgWeeklyStatController::class, 'importJsonForm'])
    ->name('gvg-weekly-stats.import-json.form');
Route::post('gvg-weekly-stats/import-json', [GvgWeeklyStatController::class, 'importJsonStore'])
    ->name('gvg-weekly-stats.import-json.store');
Route::resource('gvg-weekly-stats', GvgWeeklyStatController::class);

Route::get('party-planner', [PartyPlannerController::class, 'index'])->name('party-planner.index');
Route::post('party-planner/parties', [PartyPlannerController::class, 'store'])->name('party-planner.parties.store');
Route::delete('party-planner/parties', [PartyPlannerController::class, 'destroyAll'])->name('party-planner.parties.destroy-all');
Route::patch('party-planner/parties/{party}', [PartyPlannerController::class, 'update'])
    ->name('party-planner.parties.update');
Route::delete('party-planner/parties/{party}', [PartyPlannerController::class, 'destroy'])->name('party-planner.parties.destroy');
Route::post('party-planner/slots', [PartyPlannerController::class, 'updateSlots'])
    ->name('party-planner.slots.update');
Route::post('party-planner/auto-assign', [PartyPlannerController::class, 'autoAssign'])
    ->name('party-planner.auto-assign');

Route::get('kvm-planner', [KvmPartyPlannerController::class, 'index'])->name('kvm-planner.index');
Route::post('kvm-planner/parties', [KvmPartyPlannerController::class, 'store'])->name('kvm-planner.parties.store');
Route::post('kvm-planner/parties/{kvmParty}/slots', [KvmPartyPlannerController::class, 'addMemberSlot'])
    ->name('kvm-planner.parties.slots.store');
Route::post('kvm-planner/slots', [KvmPartyPlannerController::class, 'updateSlots'])
    ->name('kvm-planner.slots.update');
