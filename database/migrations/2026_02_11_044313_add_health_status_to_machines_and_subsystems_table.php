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
        Schema::table('machines', function (Blueprint $table) {
            $table->string('health_status')->nullable()->after('icon');
        });

        Schema::table('subsystems', function (Blueprint $table) {
            $table->string('health_status')->nullable()->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn('health_status');
        });

        Schema::table('subsystems', function (Blueprint $table) {
            $table->dropColumn('health_status');
        });
    }
};
