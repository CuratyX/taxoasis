<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiService; // UPDATED USE STATEMENT

class TaxController extends Controller
{
    protected $gemini; // UPDATED PROPERTY NAME

    // Phrases that indicate AI uncertainty - triggers Ecovis CTA
    protected $uncertaintyPhrases = [
        'requires careful analysis',
        'depends on specific circumstances',
        'professional advice',
        'consult with',
        'complex',
        'may vary',
        'should verify',
        'recommend seeking',
        'requires detailed review',
        'fact-specific',
        'case-by-case',
        'uncertain',
        'not straightforward',
        'nuanced',
        'it is advisable',
        'you should consider',
        'specific situation',
        'individual circumstances',
        'professional guidance'
    ];

    // UPDATED CONSTRUCTOR
    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * Display the main query interface
     */
    public function index()
    {
        return view('layouts.home');
    }

    /**
     * Handle a tax query
     */
    public function ask(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:1000',
            'history' => 'nullable|array'
        ]);

        $query = $request->input('query');
        $history = $request->input('history', []);

        $maxRetries = 3; // Total attempts
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                // Attempt to get the answer from Gemini
                $response = $this->gemini->askTaxQuestion($query, false, $history);

                // If successful, prepare the history and return the view
                $history[] = ['role' => 'user', 'content' => $query];
                $history[] = ['role' => 'model', 'content' => $response];

                $currentUncertainty = $this->detectUncertainty($response);
                $previouslyShown = false;
                foreach ($history as $msg) {
                    if ($msg['role'] === 'model' && $this->detectUncertainty($msg['content'])) {
                        $previouslyShown = true;
                        break;
                    }
                }

                return view('layouts.result', [
                    'query' => $query,
                    'answer' => $response,
                    'showEcovisCTA' => ($currentUncertainty || $previouslyShown),
                    'history' => $history
                ]);

            } catch (\Exception $e) {
                // Check for 503 (Overloaded) or 429 (Rate Limit)
                $errorCode = $e->getCode();

                if (($errorCode == 503 || $errorCode == 429) && $retryCount < $maxRetries - 1) {
                    $retryCount++;
                    sleep(2); // Wait 2 seconds before the next attempt
                    continue;
                }

                // If all retries fail or it's a different error, return the error view
                return view('layouts.result', [
                    'query' => $query,
                    'answer' => 'The TaxOasis servers are currently very busy. Please wait a moment and try again.',
                    'showEcovisCTA' => true,
                    'isError' => true,
                    'history' => $history
                ]);
            }
        }
    }

    /**
     * Get detailed analysis for a query
     */
    public function detailed(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:1000',
            'history' => 'nullable|array'
        ]);

        $query = $request->input('query');
        $history = $request->input('history', []);

        try {
            // We pass the history to give context to the detailed analysis
            $response = $this->gemini->askTaxQuestion($query, true, $history);

            return response()->json([
                'success' => true,
                'analysis' => $response
            ]);

        } catch (\Exception $e) {
            // Log the error for your own debugging
            \Log::error("Detailed Analysis Error: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'analysis' => 'Unable to load detailed analysis. Please try again.'
            ]);
        }
    }

    /**
     * Check if response contains uncertainty phrases
     */
    protected function detectUncertainty(string $text): bool
    {
        $lowerText = strtolower($text);

        foreach ($this->uncertaintyPhrases as $phrase) {
            if (str_contains($lowerText, $phrase)) {
                return true;
            }
        }

        return false;
    }
}
