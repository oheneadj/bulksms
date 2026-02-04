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
        Schema::create('system_credits', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('balance')->default(0); // Current inventory
            $table->bigInteger('total_purchased')->default(0); // Lifetime bought from providers
            $table->bigInteger('total_sold')->default(0); // Lifetime sold to users
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_credits');
    }
};
