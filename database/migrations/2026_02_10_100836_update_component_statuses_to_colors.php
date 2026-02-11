<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Temporarily change to string to allow new values
        Schema::table('components', function (Blueprint $table) {
            $table->string('health_status')->change();
        });

        // Update existing statuses to new color values
        \DB::table('components')->where('health_status', 'smooth')->update(['health_status' => 'green']);
        \DB::table('components')->where('health_status', 'on_fire')->update(['health_status' => 'red']);
        \DB::table('components')->where('health_status', 'needs_love')->update(['health_status' => 'yellow']);
        
        // Change back to enum with new values
        Schema::table('components', function (Blueprint $table) {
            $table->enum('health_status', ['green', 'red', 'yellow'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Temporarily change to string
        Schema::table('components', function (Blueprint $table) {
            $table->string('health_status')->change();
        });

        // Revert statuses back to original values
        \DB::table('components')->where('health_status', 'green')->update(['health_status' => 'smooth']);
        \DB::table('components')->where('health_status', 'red')->update(['health_status' => 'on_fire']);
        \DB::table('components')->where('health_status', 'yellow')->update(['health_status' => 'needs_love']);

        // Revert enum definition
        Schema::table('components', function (Blueprint $table) {
            $table->enum('health_status', ['smooth', 'on_fire', 'needs_love'])->change();
        });
    }
};
