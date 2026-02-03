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
        if (!Schema::hasTable('solutions')) {
            Schema::create('solutions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('log_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('content');
                $table->string('version', 50)->default('1.0');
                $table->enum('status', ['draft', 'testing', 'done'])->default('draft');
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
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
