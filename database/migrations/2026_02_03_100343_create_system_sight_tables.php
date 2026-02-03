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
        // 1. Machines (Level 1) - Hard-coded: Demand, Sales, Delivery
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Demand, Sales, Delivery
            $table->string('slug')->unique(); // demand, sales, delivery
            $table->text('description');
            $table->string('icon')->nullable(); // emoji or icon class
            $table->string('color')->default('#60a5fa'); // theme color
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 2. Subsystems (Level 2) - Content Engine, Distribution, etc.
        Schema::create('subsystems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Content Engine, Distribution Engine
            $table->string('slug');
            $table->text('description');
            $table->string('icon')->nullable();
            $table->string('color')->default('#60a5fa');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 3. Components (Level 3) - Hooks, Scripts, Filming, etc.
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsystem_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Hooks, Scripts, Filming
            $table->string('slug');
            $table->text('description');
            $table->string('icon')->nullable();
            $table->enum('health_status', ['smooth', 'on_fire', 'needs_love'])->default('needs_love');
            $table->text('current_issue')->nullable(); // "Hooks feel stale"
            $table->integer('metric_value')->nullable(); // 5 Hooks, 6 Scripts
            $table->string('metric_label')->nullable(); // "Hooks", "Scripts"
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 4. Upgrades (Level 4) - The main entity (SOP/Checklist)
        Schema::create('upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // "Hook Writing Upgrade"
            $table->text('purpose')->nullable(); // What this upgrade is for
            $table->text('trigger')->nullable(); // When to use this upgrade
            $table->json('steps')->nullable(); // Array of steps (checklist)
            $table->text('definition_of_done')->nullable(); // Success looks like...
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->timestamp('shipped_at')->nullable(); // When user clicked "Ship upgrade"
            $table->timestamps();
        });

        // 5. Streaks - Track user momentum
        Schema::create('streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('current_streak')->default(0); // Weeks
            $table->integer('longest_streak')->default(0);
            $table->date('last_ship_date')->nullable();
            $table->integer('total_upgrades_shipped')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streaks');
        Schema::dropIfExists('upgrades');
        Schema::dropIfExists('components');
        Schema::dropIfExists('subsystems');
        Schema::dropIfExists('machines');
    }
};
