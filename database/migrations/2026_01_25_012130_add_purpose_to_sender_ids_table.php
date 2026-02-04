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
        Schema::table('sender_ids', function (Blueprint $table) {
            $table->text('purpose')->nullable()->after('sender_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sender_ids', function (Blueprint $table) {
            $table->dropColumn('purpose');
        });
    }
};
