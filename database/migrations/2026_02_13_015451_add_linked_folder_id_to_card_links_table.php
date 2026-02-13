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
        Schema::table('card_links', function (Blueprint $table) {
            $table->foreignId('linked_folder_id')->nullable()->after('linked_note_id')->constrained('folders')->onDelete('cascade');
            $table->foreignId('linked_note_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_links', function (Blueprint $table) {
            $table->dropForeign(['linked_folder_id']);
            $table->dropColumn('linked_folder_id');
        });
    }
};
