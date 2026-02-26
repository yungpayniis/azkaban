<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('name');
        });

        $partyIds = DB::table('parties')->orderBy('id')->pluck('id');
        foreach ($partyIds as $index => $partyId) {
            DB::table('parties')
                ->where('id', $partyId)
                ->update(['position' => $index + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
