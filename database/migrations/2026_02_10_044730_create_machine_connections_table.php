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
        Schema::create('machine_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_machine_id')->constrained('machines')->onDelete('cascade');
            $table->foreignId('to_machine_id')->constrained('machines')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('label')->nullable();
            $table->string('color')->default('#6366f1');
            $table->timestamps();
            
            // Prevent duplicate connections
            $table->unique(['from_machine_id', 'to_machine_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_connections');
    }
};
