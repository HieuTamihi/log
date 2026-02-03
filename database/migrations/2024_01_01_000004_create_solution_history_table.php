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
        if (!Schema::hasTable('solution_history')) {
            Schema::create('solution_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('solution_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('content');
                $table->string('version', 50);
                $table->enum('status', ['draft', 'testing', 'done']);
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamp('changed_at')->useCurrent();
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
