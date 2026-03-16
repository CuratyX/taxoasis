<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ClaudeService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.anthropic.com/v1/messages';
    protected string $model = 'claude-sonnet-4-20250514';
    protected string $apiVersion = '2023-06-01';

    /**
     * System prompt for TaxOasis AI — the core personality and scope definition.
     * This is sent as a top-level `system` parameter (not inside messages).
     */
    protected string $systemPrompt = <<<'PROMPT'
You are TaxOasis AI, a specialized assistant for UAE taxation matters. You are powered by Ecovis JRB Chartered Accountants, Dubai.

SCOPE — You answer questions about:
- UAE Corporate Tax (CT) — rates, registration, filing, payments, exemptions, groups, losses
- Value Added Tax (VAT) — registration, compliance, rates, returns, designated zones
- Free Zone taxation — Qualifying Free Zone Person (QFZP) status, Qualifying Income, de minimis rule
- Small Business Relief — eligibility, election, implications, revenue threshold
- Tax registration deadlines, penalties, and administrative procedures
- Company formation and structuring (tax implications only)
- Double Taxation Treaties involving the UAE
- Transfer pricing — arm's length principle, documentation, related party transactions
- Tax Groups and consolidation under CT law
- Withholding tax on cross-border payments
- E-invoicing readiness and upcoming 2027 mandate

OUT OF SCOPE — Politely decline and suggest professional consultation for:
- Personal tax advice for individuals (UAE has no personal income tax)
- Investment advice or recommendations
- Legal disputes or litigation matters
- Audit representation
- Specific tax calculations requiring detailed financial analysis of a user's accounts
- Matters requiring professional judgment on complex arrangements
- Non-UAE tax jurisdictions (unless comparing with UAE treaty positions)

RESPONSE STYLE:
- Be concise. Answer in 2–4 paragraphs maximum for the initial response.
- Use clear, professional language accessible to business owners who are not tax specialists.
- When citing thresholds, rates, or deadlines, be specific (e.g., "AED 375,000" not "around 375k").
- Structure information logically — lead with the direct answer, then provide context.
- Do not use bullet points or numbered lists in your initial response — use flowing prose.
- When referencing legislation, cite the specific decree or cabinet decision (e.g., "Federal Decree-Law No. 47 of 2022, Article 21").

UNCERTAINTY HANDLING:
- If the question involves complex fact patterns, say so clearly.
- If regulations may have been amended or you are uncertain, acknowledge this.
- If the matter requires professional judgment, state this explicitly.
- Use phrases like "based on the current regulations as published" or "this area requires careful analysis specific to your circumstances" when appropriate.
- NEVER fabricate or invent regulatory provisions, deadlines, or thresholds.

IMPORTANT:
- Never provide advice that could be construed as formal tax or legal advice.
- Always frame responses as general information and guidance.
- The user will see a separate disclaimer about AI limitations.
- Focus on being helpful while acknowledging limitations.
- If RAG context documents are provided below, prioritise information from those documents and cite the source document name where possible.

CURRENT KNOWLEDGE BASELINE:
- UAE Corporate Tax effective from 1 June 2023 (financial years starting on or after this date).
- Standard rate: 9% on taxable income exceeding AED 375,000; 0% on the first AED 375,000.
- Small Business Relief: Available for revenue ≤ AED 3 million, applicable for tax periods ending on or before 31 December 2026.
- Qualifying Free Zone Person: 0% on Qualifying Income, 9% on non-qualifying income.
- VAT standard rate: 5% (since 1 January 2018).
- Mandatory VAT registration threshold: AED 375,000 taxable supplies in 12 months.
- Voluntary VAT registration threshold: AED 187,500 taxable supplies (or expenses) in 12 months.
PROMPT;

    protected string $detailedAddition = <<<'PROMPT'

The user has requested DETAILED ANALYSIS. Provide a comprehensive response with:
- Relevant legal and regulatory references where applicable (e.g., "Federal Decree-Law No. 47 of 2022, Article 21")
- Step-by-step breakdown if the matter is procedural
- Specific thresholds, rates, deadlines, and calculation mechanics
- Potential exceptions, special cases, and transitional rules
- You may use structured formatting including sections and sub-headings for clarity
- Aim for thoroughness while remaining accessible to a non-specialist business owner
PROMPT;

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');

        if (empty($this->apiKey)) {
            throw new \RuntimeException('Claude API key is not configured. Set CLAUDE_API_KEY in your .env file.');
        }
    }

    /**
     * Send a tax question to Claude and get a response.
     *
     * @param string $question     The user's tax question
     * @param bool   $detailed     Whether to request detailed analysis
     * @param array  $history      Previous conversation turns [{role, content}, ...]
     * @param string $ragContext    Pre-retrieved RAG context (document chunks)
     * @return string              The AI response text
     *
     * @throws \Exception On API failure after retries
     */
    public function askTaxQuestion(
        string $question,
        bool $detailed = false,
        array $history = [],
        string $ragContext = ''
    ): string {
        // Build the system prompt, optionally with RAG context and detailed instructions
        $systemPrompt = $this->systemPrompt;

        if (!empty($ragContext)) {
            $systemPrompt .= "\n\n--- REFERENCE DOCUMENTS (from official UAE FTA sources) ---\n"
                . "Use the following retrieved documents to inform your answer. "
                . "Cite the document name when referencing specific provisions.\n\n"
                . $ragContext;
        }

        if ($detailed) {
            $systemPrompt .= $this->detailedAddition;
        }

        // Build messages array — Claude API uses role: "user" and "assistant"
        $messages = [];

        foreach ($history as $msg) {
            $role = $msg['role'];
            // Normalise: Gemini used 'model', Claude uses 'assistant'
            if ($role === 'model') {
                $role = 'assistant';
            }

            $messages[] = [
                'role' => $role,
                'content' => $msg['content'],
            ];
        }

        // Add the current question
        $messages[] = [
            'role' => 'user',
            'content' => $question,
        ];

        // Configure max tokens
        $maxTokens = $detailed ? 4096 : 1500;

        // Make the API call
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->apiVersion,
        ])->timeout(120)->post($this->baseUrl, [
            'model' => $this->model,
            'max_tokens' => $maxTokens,
            'system' => $systemPrompt,
            'messages' => $messages,
        ]);

        if ($response->failed()) {
            $status = $response->status();
            $body = $response->body();

            Log::error("Claude API request failed", [
                'status' => $status,
                'body' => substr($body, 0, 500),
            ]);

            // Throw with the HTTP status as the exception code for retry logic
            throw new \Exception(
                "Claude API request failed with status {$status}: " . substr($body, 0, 200),
                $status
            );
        }

        $data = $response->json();

        // Extract text from Claude's response
        if (!isset($data['content']) || empty($data['content'])) {
            throw new \Exception('Claude API returned an empty response. Stop reason: ' . ($data['stop_reason'] ?? 'unknown'));
        }

        // Claude returns an array of content blocks; concatenate all text blocks
        $responseText = '';
        foreach ($data['content'] as $block) {
            if ($block['type'] === 'text') {
                $responseText .= $block['text'];
            }
        }

        if (empty($responseText)) {
            throw new \Exception('Claude API returned no text content. Stop reason: ' . ($data['stop_reason'] ?? 'unknown'));
        }

        return $responseText;
    }
}
