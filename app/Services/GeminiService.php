<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService // RENAMED CLASS
{
    protected $apiKey;
    // New Gemini API Endpoint
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    protected $model = 'gemini-3-flash-preview'; // Recommended model for speed and capability

    protected $systemPrompt = <<<'PROMPT'
You are TaxOasis AI, a specialized assistant for UAE taxation matters. You are powered by Ecovis JRB Chartered Accountants, Dubai.

SCOPE - You answer questions about:
- UAE Corporate Tax (CT) - rates, registration, filing, payments, exemptions
- Value Added Tax (VAT) - registration, compliance, rates, returns
- Free Zone taxation - Qualifying Free Zone Person status, Qualifying Income
- Small Business Relief - eligibility, election, implications
- Tax registration deadlines and penalties
- Company formation and structuring (tax implications)
- Double Taxation Treaties involving UAE
- Transfer pricing basics
- Tax Groups and consolidation

OUT OF SCOPE - Politely decline and suggest professional consultation for:
- Personal tax advice for individuals
- Investment advice or recommendations
- Legal disputes or litigation matters
- Audit representation
- Specific tax calculations requiring detailed financial analysis
- Matters requiring professional judgment on complex arrangements

RESPONSE STYLE:
- Be concise. Answer in 2-4 paragraphs maximum for the initial response.
- Use clear, professional language accessible to business owners
- When citing thresholds, rates, or deadlines, be specific (e.g., "AED 375,000" not "around 375k")
- Structure information logically
- Do not use bullet points or lists in your initial response - use flowing prose

UNCERTAINTY HANDLING:
- If the question involves complex fact patterns, say so clearly
- If regulations may have changed or you're uncertain, acknowledge this
- If the matter requires professional judgment, state this explicitly
- Include phrases like "based on current regulations" or "this area requires careful analysis" when appropriate

IMPORTANT:
- Never provide advice that could be construed as formal tax or legal advice
- Always frame responses as general information
- The user will see a separate disclaimer about AI limitations
- Focus on being helpful while acknowledging limitations

Current knowledge: UAE Corporate Tax effective from June 1, 2023. Standard rate 9% on taxable income above AED 375,000. Small Business Relief available for revenue under AED 3 million through tax periods ending before January 1, 2027.
PROMPT;

    protected $detailedAddition = <<<'PROMPT'

The user has requested DETAILED ANALYSIS. Provide a comprehensive response with:
- Relevant legal/regulatory references where applicable (e.g., "Federal Decree-Law No. 47 of 2022")
- Step-by-step breakdown if procedural
- Specific thresholds, rates, and deadlines
- Potential exceptions or special cases
- You may use structured formatting including sections for clarity
- Aim for thoroughness while remaining accessible
PROMPT;

    public function __construct()
    {
        // This assumes your API key is configured in config/services.php under 'gemini'
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Send a tax question to Gemini and get a response
     */
    // In app/Services/GeminiService.php

    /**
     * Send a tax question to Gemini and get a response
     */
    public function askTaxQuestion(string $question, bool $detailed = false, array $history = []): string
    {
        $systemPrompt = $this->systemPrompt;

        if ($detailed) {
            $systemPrompt .= $this->detailedAddition;
        }

        // Use standard generateContent endpoint
        $endpoint = $this->baseUrl . $this->model . ':generateContent';

        // Max tokens configuration for Gemini
        $maxOutputTokens = $detailed ? 2000 : 2000;

        // --- 🛠️ MODIFICATION START: BUILDING THE CHAT HISTORY 🛠️ ---

        $contents = [];

        // 1. Add previous conversation turns to the contents array
        foreach ($history as $msg) {
            $contents[] = [
                // Gemini API expects 'model' instead of 'assistant'
                'role' => ($msg['role'] === 'assistant' || $msg['role'] === 'model') ? 'model' : 'user',
                'parts' => [
                    ['text' => $msg['content']]
                ]
            ];
        }

        // 2. Add the current question.
        // If this is the very first message in the chat, merge the System Instruction.
        if (empty($contents)) {
            $mergedQuestion = "SYSTEM INSTRUCTION: " . $systemPrompt . "\n\nUSER QUESTION: " . $question;
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $mergedQuestion]
                ]
            ];
        } else {
            // Otherwise, just append the user's follow-up question
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $question]
                ]
            ];
        }

        // --- 🛠️ MODIFICATION END 🛠️ ---

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(120)->post($endpoint . '?key=' . $this->apiKey, [
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => $maxOutputTokens,
            ]
        ]);

        if ($response->failed()) {
            $status = $response->status();
            $body = $response->body();
            throw new \Exception("Gemini API HTTP request failed with status {$status}: " . $body);
        }

        $data = $response->json();

        // Check for 'candidates' before processing
        if (!isset($data['candidates']) || empty($data['candidates'])) {
            $errorDetail = $data['error']['message'] ?? 'No candidate response returned from API. Check safety filters.';
            throw new \Exception("Gemini API failed to generate content. Detail: " . $errorDetail);
        }

        // Extract text from the first candidate
        $candidate = $data['candidates'][0];
        if (isset($candidate['content']['parts'][0]['text'])) {
             return $candidate['content']['parts'][0]['text'];
        }

        $finishReason = $candidate['finishReason'] ?? 'Unknown';
        throw new \Exception("Gemini API request failed (Parsing issue). Finish reason: " . $finishReason);
    }

}

