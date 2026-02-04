<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SenderId;
use Illuminate\Http\Request;

class SenderIdController extends Controller
{
    public function index()
    {
        $senderIds = SenderId::with('user.tenant')->latest()->paginate(20);
        return view('admin.sender-ids.index', compact('senderIds'));
    }

    public function approve(SenderId $senderId)
    {
        $senderId->update(['status' => 'payment_pending']);
        
        // Notify user (implementation dependent, e.g. Notification::send...)
        
        return back()->with('success', 'Sender ID approved.');
    }

    public function reject(SenderId $senderId)
    {
        $senderId->update(['status' => 'rejected']);
        
        return back()->with('success', 'Sender ID rejected.');
    }
}
