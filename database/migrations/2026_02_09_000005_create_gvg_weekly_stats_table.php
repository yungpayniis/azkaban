<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gvg_weekly_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_member_id')->constrained('guild_members')->cascadeOnDelete();
            $table->date('week_start_date');
            $table->unsignedInteger('kills')->default(0);
            $table->unsignedInteger('deaths')->default(0);
            $table->unsignedInteger('revives')->default(0);
            $table->unsignedInteger('war_score')->default(0);
            $table->timestamps();

            $table->unique(['guild_member_id', 'week_start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gvg_weekly_stats');
    }
};
