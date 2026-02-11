<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guild_members', function (Blueprint $table) {
            $table->foreignId('job_class_id')
                ->nullable()
                ->after('name')
                ->constrained('job_classes')
                ->nullOnDelete();
            $table->enum('tier', ['low', 'middle', 'top'])->after('job_class_id')->default('low');
        });
    }

    public function down(): void
    {
        Schema::table('guild_members', function (Blueprint $table) {
            $table->dropForeign(['job_class_id']);
            $table->dropColumn(['job_class_id', 'tier']);
        });
    }
};
