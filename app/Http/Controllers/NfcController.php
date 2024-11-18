<?php

namespace App\Http\Controllers;

use App\Events\NfcCardDetected;
use Illuminate\Http\Request;

class NfcController extends Controller
{
    public function detect(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'card_id' => 'required|string|max:255',
        ]);

        // Dispatch the event
        event(new NfcCardDetected($validated['card_id']));

        // Return a response
        return response()->json([
            'success' => true,
            'message' => 'NFC Card Detected event broadcasted successfully!',
            'card_id' => $validated['card_id'],
        ]);
    }
}
