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
        Schema::create('birthday_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->foreignId('message_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sender_id')->nullable();
            $table->time('send_time')->default('09:00:00');
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('birthday_settings');
    }
};
