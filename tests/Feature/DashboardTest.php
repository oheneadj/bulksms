<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $tenant = \App\Models\Tenant::factory()->create();
    $this->actingAs($user = User::factory()->create(['tenant_id' => $tenant->id]));

    $this->get('/dashboard')
        ->assertOk()
        ->assertViewHasAll([
            'statusCounts',
            'topGroups',
            'messageGrowth'
        ]);
});