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
        // Table already exists from legacy system
        // Just add missing columns if needed
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Add remember_token if not exists
                if (!Schema::hasColumn('users', 'remember_token')) {
                    $table->rememberToken();
                }
                // Add Laravel timestamps if not exists
                if (!Schema::hasColumn('users', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        } else {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('username', 50)->unique();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop table as it contains existing data
    }
};
