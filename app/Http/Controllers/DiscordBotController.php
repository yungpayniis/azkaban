<?php

namespace App\Http\Controllers;

use App\Models\GuildMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscordBotController extends Controller
{
    public function nameChange(Request $request)
    {
        $apiKey = env('DISCORD_BOT_API_KEY');
        if ($apiKey && $request->header('X-API-KEY') !== $apiKey) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $data = $request->validate([
            'old_name' => ['required', 'string', 'max:255'],
            'new_name' => ['required', 'string', 'max:255'],
        ]);

        $oldName = trim($data['old_name']);
        $newName = trim($data['new_name']);

        if ($oldName === '' || $newName === '') {
            return response()->json(['error' => 'invalid_name'], 422);
        }

        $matches = GuildMember::where('name', $oldName)->get();
        if ($matches->count() === 0) {
            return response()->json(['error' => 'not_found'], 404);
        }
        if ($matches->count() > 1) {
            return response()->json(['error' => 'multiple_matches'], 409);
        }

        $member = $matches->first();

        if ($member->name === $newName) {
            return response()->json(['ok' => true, 'message' => 'no_change']);
        }

        DB::transaction(function () use ($member, $newName) {
            $member->update(['name' => $newName]);
            $member->nameHistories()->create([
                'name' => $newName,
            ]);
        });

        return response()->json(['ok' => true]);
    }
}
