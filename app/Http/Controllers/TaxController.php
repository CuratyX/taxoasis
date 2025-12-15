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
            'query' => 'required|string|max:1000'
        ]);

        $query = $request->input('query');

        try {
            // UPDATED METHOD CALL
            $response = $this->gemini->askTaxQuestion($query, false);

            $showEcovisCTA = $this->detectUncertainty($response);

            return view('layouts.result', [
                'query' => $query,
                'answer' => $response,
                'showEcovisCTA' => $showEcovisCTA
            ]);

        } catch (\Exception $e) {
            // Log the error $e->getMessage() for debugging
            return view('layouts.result', [
                'query' => $query,
                'answer' => 'I apologize, but I encountered an issue processing your question. Please try again or contact Ecovis JRB directly for assistance.',
                'showEcovisCTA' => true,
                'isError' => true
            ]);
        }
    }

    /**
     * Get detailed analysis for a query
     */
    public function detailed(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:1000'
        ]);

        $query = $request->input('query');

        try {
            // UPDATED METHOD CALL
            $response = $this->gemini->askTaxQuestion($query, true);

            return response()->json([
                'success' => true,
                'analysis' => $response
            ]);

        } catch (\Exception $e) {
            // Log the error $e->getMessage() for debugging
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
