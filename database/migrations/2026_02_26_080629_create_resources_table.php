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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name
            $table->string('filename'); // Actual filename on disk
            $table->string('original_filename'); // Original uploaded filename
            $table->string('mime_type');
            $table->integer('size'); // in bytes
            $table->string('path');
            $table->enum('type', ['image', 'document', 'video', 'audio', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // e.g., 'templates', 'images', 'documents'
            $table->json('tags')->nullable(); // For searching
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->integer('download_count')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
