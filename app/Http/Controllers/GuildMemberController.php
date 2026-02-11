<?php

namespace App\Http\Controllers;

use App\Models\GuildMember;
use App\Models\GuildMemberNameHistory;
use App\Models\JobClass;
use Illuminate\Http\Request;

class GuildMemberController extends Controller
{
    public function index()
    {
        $members = GuildMember::with('jobClass.parent')
            ->orderBy('status')
            ->orderBy('name')
            ->get();

        return view('guild-members.index', compact('members'));
    }

    public function create()
    {
        $jobClasses = JobClass::where('tier', 4)->orderBy('name')->get();

        return view('guild-members.create', compact('jobClasses'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $stats = $this->validateStats($request);
        $guildMember = GuildMember::create($data);
        $guildMember->nameHistories()->create([
            'name' => $guildMember->name,
        ]);
        $guildMember->stat()->create($stats);
        $guildMember->statHistories()->create($stats);

        return redirect()
            ->route('guild-members.index')
            ->with('status', 'เพิ่มสมาชิกเรียบร้อย');
    }

    public function show(GuildMember $guildMember)
    {
        $guildMember->load('jobClass', 'gvgWeeklyStats', 'nameHistories', 'stat', 'statHistories');

        return view('guild-members.show', compact('guildMember'));
    }

    public function edit(GuildMember $guildMember)
    {
        $jobClasses = JobClass::where('tier', 4)->orderBy('name')->get();

        return view('guild-members.edit', compact('guildMember', 'jobClasses'));
    }

    public function update(Request $request, GuildMember $guildMember)
    {
        $data = $this->validatePayload($request);
        $stats = $this->validateStats($request);
        $originalName = $guildMember->name;
        $guildMember->update($data);
        $guildMember->stat()->updateOrCreate(
            ['guild_member_id' => $guildMember->id],
            $stats
        );
        $guildMember->statHistories()->create($stats);
        if ($originalName !== $guildMember->name) {
            $guildMember->nameHistories()->create([
                'name' => $guildMember->name,
            ]);
        }

        return redirect()
            ->route('guild-members.index')
            ->with('status', 'อัปเดตสมาชิกเรียบร้อย');
    }

    public function destroy(GuildMember $guildMember)
    {
        $guildMember->delete();

        return redirect()
            ->route('guild-members.index')
            ->with('status', 'ลบสมาชิกเรียบร้อย');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'job_class_id' => ['nullable', 'integer', 'exists:job_classes,id'],
            'tier' => ['required', 'in:low,middle,top'],
            'role' => ['required', 'in:dps,support,tank'],
            'nationality' => ['required', 'in:thai,foreign'],
            'status' => ['required', 'in:'.GuildMember::STATUS_ACTIVE.','.GuildMember::STATUS_LEFT],
            'joined_at' => ['required', 'date'],
        ]);
    }

    private function validateStats(Request $request): array
    {
        $data = $request->validate([
            'stats' => ['required', 'array'],
            'stats.str' => ['required', 'integer', 'min:0'],
            'stats.vit' => ['required', 'integer', 'min:0'],
            'stats.luk' => ['required', 'integer', 'min:0'],
            'stats.agi' => ['required', 'integer', 'min:0'],
            'stats.dex' => ['required', 'integer', 'min:0'],
            'stats.int' => ['required', 'integer', 'min:0'],
            'stats.hp' => ['required', 'integer', 'min:0'],
            'stats.sp' => ['required', 'integer', 'min:0'],
            'stats.patk' => ['required', 'integer', 'min:0'],
            'stats.matk' => ['required', 'integer', 'min:0'],
            'stats.pdef' => ['required', 'integer', 'min:0'],
            'stats.mdef' => ['required', 'integer', 'min:0'],
        ]);

        return $data['stats'];
    }
}
