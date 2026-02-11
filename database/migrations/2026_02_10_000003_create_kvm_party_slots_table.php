<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kvm_party_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kvm_party_id')->constrained('kvm_parties')->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->foreignId('member_id')->nullable()->constrained('guild_members')->nullOnDelete();
            $table->timestamps();

            $table->unique(['kvm_party_id', 'position']);
            $table->unique('member_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kvm_party_slots');
    }
};
