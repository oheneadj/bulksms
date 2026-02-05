<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Contact;
use App\Models\Group;
use App\Models\SenderId;
use App\Models\Message;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Create Super Admin
        $this->call(AdminUserSeeder::class);

        // 1. Create Demo Tenant
        $tenant = Tenant::create([
            'name' => 'Acme Marketing Ltd',
            'slug' => 'acme-marketing',
            'email' => 'admin@acmemarketing.com',
            'plan_type' => 'business',
            'status' => 'active',
            'sms_credits' => 5000,
            'monthly_message_count' => 1250,
        ]);

        // 2. Create Tenant Admin User
        $admin = User::factory()->create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah@acmemarketing.com',
            'tenant_id' => $tenant->id,
        ]);

        // 3. Create Transaction History
        Transaction::create([
            'user_id' => $admin->id,
            'type' => 'deposit',
            'amount' => 10000,
            'description' => 'Initial credit purchase via Stripe',
            'reference' => 'TOP-' . strtoupper(Str::random(10)),
            'balance_after' => 10000,
            'created_at' => now()->subDays(30),
        ]);

        Transaction::create([
            'user_id' => $admin->id,
            'type' => 'usage',
            'amount' => 5000,
            'description' => 'Campaign: Holiday Sale 2025',
            'reference' => 'USE-' . strtoupper(Str::random(10)),
            'balance_after' => 5000,
            'created_at' => now()->subDays(15),
        ]);

        // 4. Create Approved Sender IDs
        SenderId::create([
            'user_id' => $admin->id,
            'sender_id' => 'ACME',
            'status' => 'approved',
            'created_at' => now()->subDays(25),
        ]);

        SenderId::create([
            'user_id' => $admin->id,
            'sender_id' => 'AcmeSales',
            'status' => 'approved',
            'created_at' => now()->subDays(20),
        ]);

        SenderId::create([
            'user_id' => $admin->id,
            'sender_id' => 'SUPPORT',
            'status' => 'pending',
            'created_at' => now()->subDays(2),
        ]);

        // 5. Create Contact Groups
        $vipGroup = Group::create([
            'tenant_id' => $tenant->id,
            'name' => 'VIP Customers',
            'description' => 'High-value repeat customers',
            'created_by_user_id' => $admin->id,
        ]);

        $newsletterGroup = Group::create([
            'tenant_id' => $tenant->id,
            'name' => 'Newsletter Subscribers',
            'description' => 'Users who opted in for weekly updates',
            'created_by_user_id' => $admin->id,
        ]);

        // 6. Create Realistic Contacts
        $contacts = [
            ['name' => 'Michael Chen', 'phone' => '+447700900123', 'email' => 'michael.chen@example.com'],
            ['name' => 'Emma Williams', 'phone' => '+447700900456', 'email' => 'emma.w@example.com'],
            ['name' => 'James Taylor', 'phone' => '+447700900789', 'email' => 'j.taylor@example.com'],
            ['name' => 'Olivia Brown', 'phone' => '+447700900321', 'email' => 'olivia.brown@example.com'],
            ['name' => 'Noah Davis', 'phone' => '+447700900654', 'email' => 'noah.davis@example.com'],
            ['name' => 'Sophia Miller', 'phone' => '+447700900987', 'email' => 'sophia.m@example.com'],
            ['name' => 'Liam Wilson', 'phone' => '+447700900147', 'email' => 'liam.wilson@example.com'],
            ['name' => 'Ava Martinez', 'phone' => '+447700900258', 'email' => 'ava.martinez@example.com'],
        ];

        foreach ($contacts as $index => $contactData) {
            $contact = Contact::create([
                'tenant_id' => $tenant->id,
                'name' => $contactData['name'],
                'phone' => $contactData['phone'],
                'email' => $contactData['email'],
                'created_by_user_id' => $admin->id,
            ]);

            // Assign to groups
            if ($index < 3) {
                $contact->groups()->attach($vipGroup->id);
            }
            if ($index % 2 === 0) {
                $contact->groups()->attach($newsletterGroup->id);
            }
        }

        // 7. Create Sample Messages (Sent)
        Message::create([
            'user_id' => $admin->id,
            'sender_id' => 'ACME',
            'recipient' => '+447700900123',
            'body' => 'Hi Michael! Your order #12345 has been shipped. Track it here: acme.co/track/12345',
            'parts' => 1,
            'cost' => 0.05,
            'status' => 'delivered',
            'sent_at' => now()->subDays(5),
            'delivered_at' => now()->subDays(5)->addMinutes(2),
            'created_at' => now()->subDays(5),
        ]);

        Message::create([
            'user_id' => $admin->id,
            'sender_id' => 'AcmeSales',
            'recipient' => '+447700900456',
            'body' => 'Flash Sale! 30% off all items today only. Use code FLASH30 at checkout. Shop now: acme.co/sale',
            'parts' => 1,
            'cost' => 0.05,
            'status' => 'delivered',
            'sent_at' => now()->subDays(3),
            'delivered_at' => now()->subDays(3)->addMinutes(1),
            'created_at' => now()->subDays(3),
        ]);

        // 8. Create Scheduled Message
        Message::create([
            'user_id' => $admin->id,
            'sender_id' => 'ACME',
            'recipient' => '+447700900789',
            'body' => 'Reminder: Your appointment is tomorrow at 2 PM. Reply CONFIRM to verify.',
            'parts' => 1,
            'cost' => 0.05,
            'status' => 'scheduled',
            'scheduled_at' => now()->addHours(12),
            'created_at' => now(),
        ]);

        $this->command->info('✅ Demo environment seeded successfully!');
        $this->command->info('📧 Login: sarah@acmemarketing.com');
        $this->command->info('🔑 Password: password');
    }
}
