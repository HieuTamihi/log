<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->enum('status', ['none', 'draft', 'improving', 'standardized'])->default('none')->change();
        });
        
        Schema::table('folders', function (Blueprint $table) {
            $table->enum('status', ['none', 'draft', 'improving', 'standardized'])->default('none')->change();
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->enum('status', ['draft', 'improving', 'standardized'])->default('draft')->change();
        });
        
        Schema::table('folders', function (Blueprint $table) {
            $table->enum('status', ['draft', 'improving', 'standardized'])->default('draft')->change();
        });
    }
};
