<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\SenderId;
use App\Jobs\SendMessageJob;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    public function send(Request $request, SmsService $smsService)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|string',
            'recipient' => 'required|string',
            'message' => 'required|string|max:480',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $tenant = $user->tenant;

        // Verify Sender ID
        $senderId = SenderId::where('user_id', '=', $user->id)
            ->where('sender_id', '=', $request->sender_id)
            ->where('status', '=', 'active')
            ->first();

        if (!$senderId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive Sender ID.',
            ], 403);
        }

        // Calculate parts and cost
        $parts = $smsService->calculateParts($request->message);
        $cost = $parts; // 1 credit per part

        // Check credits
        if ($tenant->sms_credits < $cost) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient credits.',
                'needed' => $cost,
                'available' => $tenant->sms_credits,
            ], 402);
        }

        try {
            $messageRecord = DB::transaction(function () use ($user, $tenant, $request, $parts, $cost) {
                // Deduct credits
                $tenant->decrement('sms_credits', $cost);

                // Log Usage Transaction
                \App\Models\Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'usage',
                    'amount' => $cost,
                    'description' => 'API SMS to ' . $request->recipient,
                    'reference' => 'API-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10)),
                    'balance_after' => $tenant->sms_credits,
                ]);

                // Create message record
                return Message::create([
                    'user_id' => $user->id,
                    'sender_id' => $request->sender_id,
                    'recipient' => $request->recipient,
                    'body' => $request->message,
                    'parts' => $parts,
                    'cost' => $cost,
                    'status' => 'queued',
                ]);
            });

            // Dispatch job
            SendMessageJob::dispatch($messageRecord);

            return response()->json([
                'success' => true,
                'message' => 'Message queued successfully.',
                'data' => [
                    'message_id' => $messageRecord->id,
                    'recipient' => $messageRecord->recipient,
                    'parts' => $messageRecord->parts,
                    'cost' => $messageRecord->cost,
                    'balance_remaining' => $tenant->fresh()->sms_credits,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request.',
            ], 500);
        }
    }
}
