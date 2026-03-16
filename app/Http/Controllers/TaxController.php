<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ClaudeService;
use App\Services\RagService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class TaxController extends Controller
{
    protected ClaudeService $claude;
    protected RagService $rag;

    /**
     * Phrases indicating AI uncertainty — triggers the Ecovis JRB consultation CTA.
     */
    protected array $uncertaintyPhrases = [
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
        'professional guidance',
        'beyond the scope',
        'qualified tax professional',
        'Ecovis JRB',
    ];

    public function __construct(ClaudeService $claude, RagService $rag)
    {
        $this->claude = $claude;
        $this->rag = $rag;
    }

    /**
     * Display the main query interface (homepage).
     */
    public function index()
    {
        return view('layouts.home');
    }

    /**
     * Handle a tax query submission.
     *
     * Flow:
     * 1. Validate & rate-limit the request
     * 2. Retrieve relevant RAG context for the question
     * 3. Send question + context + history to Claude
     * 4. Detect uncertainty in the response
     * 5. Return the result view with conversation history
     */
    public function ask(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:1000',
            'history' => 'nullable|array',
        ]);

        $query = trim($request->input('query'));
        $history = $request->input('history', []);

        // ── Rate Limiting ────────────────────────────
        // 20 queries per minute per IP to control API costs
        $rateLimitKey = 'tax_query_' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 20)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return view('layouts.result', [
                'query' => $query,
                'answer' => "You're asking questions faster than we can brew the answers. Please wait {$seconds} seconds and try again.",
                'showEcovisCTA' => false,
                'isError' => true,
                'history' => $history,
            ]);
        }
        RateLimiter::hit($rateLimitKey, 60);

        // ── Pre-screening ────────────────────────────
        // Quick check: is this even a tax-related question?
        if ($this->isOffTopic($query)) {
            $offTopicResponse = "TaxOasis is designed to answer questions about UAE taxation — Corporate Tax, VAT, Free Zone taxation, and related compliance matters. Your question appears to fall outside this scope. If you believe this is a tax question, please rephrase it with more specific tax terminology.";

            $history[] = ['role' => 'user', 'content' => $query];
            $history[] = ['role' => 'assistant', 'content' => $offTopicResponse];

            return view('layouts.result', [
                'query' => $query,
                'answer' => $offTopicResponse,
                'showEcovisCTA' => false,
                'history' => $history,
            ]);
        }

        // ── RAG Retrieval ────────────────────────────
        $ragContext = $this->rag->retrieveContext($query);

        // ── Claude API Call with Retry ───────────────
        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                $response = $this->claude->askTaxQuestion(
                    $query,
                    false,
                    $history,
                    $ragContext
                );

                // Build conversation history for multi-turn
                // Note: Claude uses 'assistant', but we store as 'assistant' uniformly now
                $history[] = ['role' => 'user', 'content' => $query];
                $history[] = ['role' => 'assistant', 'content' => $response];

                // Detect if the CTA should show
                $currentUncertainty = $this->detectUncertainty($response);

                // Also check if any previous response in history already showed uncertainty
                $previouslyShown = false;
                foreach ($history as $msg) {
                    if ($msg['role'] === 'assistant' && $this->detectUncertainty($msg['content'])) {
                        $previouslyShown = true;
                        break;
                    }
                }

                return view('layouts.result', [
                    'query' => $query,
                    'answer' => $response,
                    'showEcovisCTA' => ($currentUncertainty || $previouslyShown),
                    'history' => $history,
                ]);

            } catch (\Exception $e) {
                $errorCode = $e->getCode();

                // Retry on overloaded (529), server error (500), or rate-limit (429)
                $retryableCodes = [429, 500, 503, 529];

                if (in_array($errorCode, $retryableCodes) && $retryCount < $maxRetries - 1) {
                    $retryCount++;
                    $delay = $retryCount * 2; // Exponential-ish: 2s, 4s
                    sleep($delay);
                    continue;
                }

                Log::error('TaxController: Claude API call failed after retries', [
                    'error' => $e->getMessage(),
                    'code' => $errorCode,
                    'query' => $query,
                ]);

                return view('layouts.result', [
                    'query' => $query,
                    'answer' => 'The TaxOasis AI is temporarily unavailable. Please try again in a moment, or contact Ecovis JRB directly for assistance.',
                    'showEcovisCTA' => true,
                    'isError' => true,
                    'history' => $history,
                ]);
            }
        }
    }

    /**
     * Get detailed analysis for a query (AJAX endpoint).
     */
    public function detailed(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:1000',
            'history' => 'nullable|array',
        ]);

        $query = $request->input('query');
        $history = $request->input('history', []);

        try {
            // Retrieve RAG context for the detailed analysis too
            $ragContext = $this->rag->retrieveContext($query);

            $response = $this->claude->askTaxQuestion(
                $query,
                true,
                $history,
                $ragContext
            );

            return response()->json([
                'success' => true,
                'analysis' => $response,
            ]);

        } catch (\Exception $e) {
            Log::error('Detailed Analysis Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'analysis' => 'Unable to load detailed analysis. Please try again.',
            ]);
        }
    }

    /**
     * Check if response contains uncertainty phrases that warrant a consultation CTA.
     */
    protected function detectUncertainty(string $text): bool
    {
        $lowerText = strtolower($text);

        foreach ($this->uncertaintyPhrases as $phrase) {
            if (str_contains($lowerText, strtolower($phrase))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Basic pre-screening to filter clearly off-topic queries.
     * Keeps the Claude API budget focused on tax-related questions.
     */
    protected function isOffTopic(string $query): bool
    {
        $lower = strtolower($query);

        // Very short queries are likely not meaningful
        if (strlen($lower) < 5) {
            return true;
        }

        // Explicitly non-tax patterns
        $offTopicPatterns = [
            '/\b(recipe|cook|weather|sport|game|movie|music|joke|poem|story)\b/i',
            '/\b(who is the president|capital of|translate|write me a)\b/i',
            '/\b(code|python|javascript|html|css|programming)\b/i',
        ];

        foreach ($offTopicPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return true;
            }
        }

        return false;
    }
}
