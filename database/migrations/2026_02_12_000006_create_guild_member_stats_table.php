<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guild_member_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_member_id')
                ->constrained('guild_members')
                ->cascadeOnDelete()
                ->unique();
            $table->unsignedInteger('str')->default(0);
            $table->unsignedInteger('vit')->default(0);
            $table->unsignedInteger('luk')->default(0);
            $table->unsignedInteger('agi')->default(0);
            $table->unsignedInteger('dex')->default(0);
            $table->unsignedInteger('int')->default(0);
            $table->unsignedInteger('hp')->default(0);
            $table->unsignedInteger('sp')->default(0);
            $table->unsignedInteger('patk')->default(0);
            $table->unsignedInteger('matk')->default(0);
            $table->unsignedInteger('pdef')->default(0);
            $table->unsignedInteger('mdef')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guild_member_stats');
    }
};
