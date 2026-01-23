<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('title');
            $table->string('mime_type')->nullable()->after('file_path');
            $table->string('original_name')->nullable()->after('mime_type');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'mime_type', 'original_name']);
            $table->dropSoftDeletes();
        });
    }
};
