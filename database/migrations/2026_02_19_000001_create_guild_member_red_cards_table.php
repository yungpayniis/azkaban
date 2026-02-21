<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guild_member_red_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_member_id')->constrained('guild_members')->cascadeOnDelete();
            $table->string('reason');
            $table->timestamp('issued_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guild_member_red_cards');
    }
};
