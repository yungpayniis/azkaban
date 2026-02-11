<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_classes', function (Blueprint $table) {
            $table->string('color', 15)->after('name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_classes', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
