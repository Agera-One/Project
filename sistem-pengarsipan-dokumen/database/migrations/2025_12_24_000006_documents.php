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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('status')->cascadeOnDelete();
            $table->string('title');
            $table->integer('size');
            $table->string('file_path')->nullable()->after('title');
            $table->string('mime_type')->nullable()->after('file_path');
            $table->string('original_name')->nullable()->after('mime_type');
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
