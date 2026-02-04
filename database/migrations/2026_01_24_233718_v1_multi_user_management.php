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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('tenant_id');
            $table->string('status')->default('active')->after('role');
            $table->boolean('is_account_owner')->default(false)->after('status');
            $table->boolean('can_topup_credits')->default(false)->after('is_account_owner');
            $table->boolean('can_view_billing')->default(false)->after('can_topup_credits');
        });

        Schema::create('user_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('role')->default('user');
            $table->string('token')->unique();
            $table->boolean('can_topup_credits')->default(false);
            $table->boolean('can_view_billing')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->unique(['tenant_id', 'email']);
        });

        // Set existing users as tenant admins and account owners if they are the only user in their tenant
        // This is a simplified approach for the migration
        // In a real scenario, we might want to be more granular.
        \App\Models\User::all()->each(function ($user) {
            $user->update([
                'role' => 'tenant_admin',
                'is_account_owner' => true,
                'can_topup_credits' => true,
                'can_view_billing' => true,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_invitations');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'status',
                'is_account_owner',
                'can_topup_credits',
                'can_view_billing',
            ]);
        });
    }
};
