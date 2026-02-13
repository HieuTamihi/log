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
        Schema::table('notes', function (Blueprint $table) {
            $table->text('description')->nullable()->after('content');
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete()->after('description');
            $table->enum('status', ['draft', 'improving', 'standardized'])->default('draft')->after('manager_id');
            $table->integer('current_version')->default(1)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['description', 'manager_id', 'status', 'current_version']);
        });
    }
};
