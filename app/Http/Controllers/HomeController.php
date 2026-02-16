<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function subscribe(Request $request)
    {
        // 1. Validate basic input (Removed 'unique' rule to handle it manually below)
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid name and email address.'
            ], 422);
        }

        try {
            // 2. Check if email already exists
            $existingSubscriber = Subscriber::where('email', $request->input('email'))->first();

            if ($existingSubscriber) {
                return response()->json([
                    'success' => true, // We treat this as success (200 OK) for better UX
                    'message' => 'You had already subscribed. Thank you.'
                ]);
            }

            // 3. Create the new subscriber record
            Subscriber::create([
                'username' => $request->input('name'),
                'email' => $request->input('email'),
                'status' => 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Welcome to the caravan! You have successfully subscribed.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }
}
