<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Log;

class ImportContactsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $userId;
    protected $tenantId;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $userId, $tenantId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
        $this->tenantId = $tenantId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->tenantId = $this->tenantId ?: 1;
        $path = Storage::path($this->filePath);
        if (!file_exists($path)) return;

        $user = User::find($this->userId); // Ensure we get the user model
        if (!$user) {
             Storage::delete($this->filePath);
             return;
        }

        // Stats
        $imported = 0;
        $skipped = 0;
        $chunkSize = 500;
        $buffer = [];

        // Open stream
        if (($handle = fopen($path, "r")) !== FALSE) {
            // Get Header
            $header = fgetcsv($handle);
            if (!$header) {
                 fclose($handle);
                 Storage::delete($this->filePath);
                 return;
            }
            $header = array_map(fn($h) => strtolower(trim($h)), $header);
            
            // Map Columns
            $titleIndex = array_search('title', $header);
            $firstNameIndex = array_search('first_name', $header);
            $surnameIndex = array_search('surname', $header);
            $phoneIndex = array_search('phone', $header);
            $emailIndex = array_search('email', $header);
            $dobIndex = array_search('dob', $header);
            $groupIndex = array_search('group', $header);

             if ($phoneIndex === false) {
                // We need at least phone. Ideally first_name too but let's be loose.
                Log::error('Missing required columns in CSV import', ['header' => $header]);
                 fclose($handle);
                 Storage::delete($this->filePath);
                 return; // Log error: Missing required columns
            }

            // Cache Group IDs to avoid repeats
            $groupCache = [];

            while (($row = fgetcsv($handle)) !== FALSE) {
                // Validation
                if (count($row) !== count($header)) {
                    $skipped++;
                    continue;
                }
                
                $phone = $row[$phoneIndex] ?? null;
                // Basic phone validation
                if (!$phone || !preg_match('/^\+?[1-9]\d{1,14}$/', $phone)) {
                    $skipped++;
                    continue;
                }
                // Group Logic
                $groupId = null;
                $groupName = $groupIndex !== false ? ($row[$groupIndex] ?? null) : null;
                
                if ($groupName) {
                    if (!isset($groupCache[$groupName])) {
                        $group = Group::firstOrCreate(
                            ['name' => $groupName, 'tenant_id' => $this->tenantId],
                            ['description' => 'Imported via CSV', 'created_by_user_id' => $this->userId]
                        );
                        $groupCache[$groupName] = $group->id;
                    }
                    $groupId = $groupCache[$groupName];
                }

                // Prepare Data
                $buffer[] = [
                    'tenant_id' => $this->tenantId,
                    'created_by_user_id' => $this->userId,
                    'group_id' => $groupId,
                    'title' => $titleIndex !== false ? ($row[$titleIndex] ?? null) : null,
                    'first_name' => $firstNameIndex !== false ? ($row[$firstNameIndex] ?? null) : null,
                    'surname' => $surnameIndex !== false ? ($row[$surnameIndex] ?? null) : null,
                    'phone' => $phone,
                    'email' => $emailIndex !== false ? ($row[$emailIndex] ?? null) : null,
                    'dob' => $dobIndex !== false ? ($row[$dobIndex] ?? null) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Flush Chunk
                if (count($buffer) >= $chunkSize) {
                    $this->flushChunk($buffer, $groupCache);
                    $imported += count($buffer);
                    $buffer = [];
                }
            }
            
            // Flush remaining
            if (!empty($buffer)) {
                $this->flushChunk($buffer, $groupCache);
                $imported += count($buffer);
            }

            fclose($handle);
        }

        Storage::delete($this->filePath);

        // Notify User
        $user->notify(new \App\Notifications\ImportCompletedNotification($imported, $skipped));
    }

    protected function flushChunk(array $chunk, array $groupCache)
    {
        DB::transaction(function () use ($chunk, $groupCache) {
            Contact::insert($chunk);
            
            // Increment Group Counts manually since bulk insert doesn't fire events
            // This is an estimation/optimization trade-off. 
            // Better approach: Aggregate group counts in the loop and update once.
            $groupCounts = [];
            foreach ($chunk as $c) {
                if ($c['group_id']) {
                    $groupCounts[$c['group_id']] = ($groupCounts[$c['group_id']] ?? 0) + 1;
                }
            }
            foreach ($groupCounts as $gid => $count) {
                 Group::where('id', $gid)->increment('contacts_count', $count);
            }
        });
    }
}
