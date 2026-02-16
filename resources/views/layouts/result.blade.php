@extends('layouts.app')

@section('title', 'TaxOasis - Response')

@section('styles')
    <style>
        :root {
            --brand-gold: #B8860B;
            --light-gold: #D4A855;
            --brand-dark: #2C3E50;
            --bg-dark: #2C3E50;
            --white-glass: rgba(255, 255, 255, 0.06);
            --border-light: rgba(255, 255, 255, 0.1);
        }

        body {
            background: radial-gradient(circle at center, #26384a 0%, var(--bg-dark) 100%);
            color: white;
            /* UPDATED: Matches Home & Guidelines */
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            margin: 0;
            min-height: 100vh;
            font-weight: 400;
        }

        .header {
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: transparent;
            z-index: 100;
        }

        /* --- UPDATED BRANDING TYPOGRAPHY --- */
        .logo-text {
            font-size: 24px;
            letter-spacing: 1px;
            color: white;
            display: flex;
            align-items: baseline;
            gap: 2px; /* Slight gap between TAX and Oasis */
        }

        .wordmark-tax {
            /* Guide: Plus Jakarta Sans, Bold (700), Uppercase */
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            opacity: 1;
            letter-spacing: 0.02em;
        }

        .wordmark-oasis {
            font-family: 'Libre Baskerville', serif;
            font-weight: 400;
            color: var(--light-gold);
        }

        .main-content {
            max-width: 850px;
            margin: 0 auto;
            padding: 40px 24px 40px 24px;
        }

        .query-section {
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border-light);
            margin-top: 4rem;
        }

        .query-label {
            font-size: 13px;
            color: var(--light-gold);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .query-text {
            font-size: 24px;
            font-weight: 500; /* Medium weight for questions */
            color: white;
            line-height: 1.4;
        }

        .answer-text {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.8;
            height: auto !important;
            overflow: visible !important;
            font-weight: 400; /* Regular weight for body text */
        }

        /* Follow-up Container */
        .query-container {
            width: 100%;
            background: white;
            border-radius: 14px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .query-input {
            flex: 1;
            border: none;
            padding: 15px 20px;
            font-size: 17px;
            outline: none;
            color: #333;
            background: transparent;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .submit-btn {
            background-color: #4A5568;
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* CTA Card Alignment */
        .cta-card {
            background: rgba(212, 168, 85, 0.1) !important;
            border: 1px solid var(--light-gold) !important;
            padding: 30px;
            border-radius: 16px;
        }

        .cta-title {
            color: var(--light-gold);
            margin-bottom: 10px;
            font-family: 'DM Serif Display', serif;
            font-size: 22px;
        }

        .cta-text {
            color: white;
            margin-bottom: 20px;
        }

        /* Button Styling */
        .btn {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
        }

        .disclaimer {
            margin-top: 24px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
            line-height: 1.6;
            padding: 20px;
            background: var(--white-glass);
            border-radius: 12px;
            border: 1px solid var(--border-light);
        }

        /* Spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection

@section('content')
    {{-- <header class="header">
        <a href="{{ route('home') }}" class="logo" style="text-decoration: none;">
            <div class="logo-text">
                <span class="wordmark-tax">TAX</span><span class="wordmark-oasis">Oasis</span>
            </div>
        </a>
        <a href="{{ route('home') }}" class="example-btn" style="text-decoration: none; padding: 8px 20px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.2); color: white; font-size: 14px;">New chat</a>
    </header> --}}
    @include('partials.header')

    <main class="main-content">
        @if (isset($history))
            @foreach ($history as $index => $msg)
                @if ($index < count($history) - 2)
                    {{-- Show older messages in a simpler style --}}
                    <div class="query-section" style="margin-bottom: 16px;">
                        <p class="query-label">{{ $msg['role'] == 'user' ? 'Question' : 'TaxOasis' }}</p>
                        <p class="{{ $msg['role'] == 'user' ? 'query-text' : 'history-content' }}"
                            @if ($msg['role'] != 'user') data-raw="{{ $msg['content'] }}" @endif
                            style="font-size: {{ $msg['role'] == 'user' ? '24px' : '18px' }};">
                            {{ $msg['content'] }}
                        </p>
                    </div>
                @endif
            @endforeach
        @endif

        <div class="query-section" id="latest-response">
            <p class="query-label">Question</p>
            <p class="query-text">{{ $query }}</p>
        </div>

        <div style="margin-bottom: 24px;">
            <p class="answer-text" id="latest-answer-text" data-raw="{{ $answer }}">{{ $answer }}</p>
        </div>

        {{-- @if (!isset($isError) || !$isError)
            <button type="button" class="detailed-link" id="detailedToggle" onclick="toggleDetailed()">
                <span id="toggleText">Show detailed analysis</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>

            <div class="detailed-content" id="detailedContent">
                <div class="loading-indicator" id="detailedLoading">
                    <div class="spinner" style="border-color: var(--teal); border-top-color: transparent;"></div>
                    Loading detailed analysis...
                </div>
                <p class="detailed-text" id="detailedText"></p>
            </div>
        @endif --}}

        <div style="margin: 40px 0; padding-top: 32px; border-top: 1px solid var(--border);">
            <form action="{{ route('ask') }}" method="POST" id="followUpForm">
                @csrf
                {{-- Pass the entire history forward to the next turn --}}
                @foreach ($history ?? [] as $i => $msg)
                    <input type="hidden" name="history[{{ $i }}][role]" value="{{ $msg['role'] }}">
                    <input type="hidden" name="history[{{ $i }}][content]" value="{{ $msg['content'] }}">
                @endforeach

                <div class="query-container" style="max-width: 100%;">
                    <input type="text" name="query" class="query-input" placeholder="Ask a follow-up question..."
                        required autocomplete="off">
                    <button type="submit" class="submit-btn" id="followUpSubmit">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        @if ($showEcovisCTA)
            <div class="card card-warning cta-card">
                <h3 class="cta-title">Need certainty?</h3>
                <p class="cta-text">Tax situations vary based on specific circumstances. Speak with an Ecovis JRB expert for
                    advice tailored to your situation.</p>
                <a href="https://ecovis.ae/contact" target="_blank" class="btn btn-gold">Book a Consultation</a>
            </div>
        @endif

        <p class="disclaimer">
            <strong>Disclaimer:</strong> TaxOasis AI provides general information only and does not constitute professional
            tax, legal, or financial advice. AI can make mistakes. Responses are based on available information and may not
            reflect the most recent regulatory changes. Always consult a qualified tax professional for advice specific to
            your circumstances.
        </p>
    </main>
    @include('partials.footer')

@endsection

@section('scripts')
    <script>
        let detailedLoaded = false;
        let detailedVisible = false;

        // The current query passed from the Controller
        const query = @json($query);
        // The current history passed from the Controller
        const history = @json($history ?? []);

        const detailedTextElement = document.getElementById('detailedText');
        const detailedLoadingElement = document.getElementById('detailedLoading');
        const content = document.getElementById('detailedContent');
        const toggle = document.getElementById('detailedToggle');
        const toggleText = document.getElementById('toggleText');

        /**
         * Formatting Function
         * Converts basic Markdown (Bold, Headings, Lists) into HTML
         */
        function formatMarkdown(markdownText) {
            if (!markdownText) return '';

            let html = markdownText
                // 1. Headers (### to h3, ## to h2)
                .replace(/^###\s*(.*)$/gm, '<h3>$1</h3>')
                .replace(/^##\s*(.*)$/gm, '<h2>$1</h2>')

                // 2. Bold (**text**) and Italics (*text*)
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')

                // 3. Unordered Lists (- or *)
                .replace(/^\s*[\-\*]\s*(.*)$/gm, '<li>$1</li>');

            // Wrap list items in <ul> tags
            if (html.includes('<li>')) {
                html = html.replace(/(<li>.*<\/li>)+/s, '<ul>$&</ul>');
            }

            // 4. Paragraphs and line breaks
            html = html.split(/\n\n+/).map(para => {
                if (para.trim().startsWith('<h') || para.trim().startsWith('<ul')) return para;
                return `<p>${para.replace(/\n/g, '<br>')}</p>`;
            }).join('');

            return html;
        }

        window.addEventListener('pageshow', function(event) {
            const btn = document.getElementById('followUpSubmit');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>`;
            }
        });

        // NEW: Apply formatting to history on page load
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Format the latest answer immediately
            const latestAns = document.getElementById('latest-answer-text');
            if (latestAns) {
                const rawText = latestAns.getAttribute('data-raw');
                latestAns.innerHTML = formatMarkdown(rawText);
                latestAns.classList.add('answer-text');
            }

            // 2. Format any historical messages if they exist
            document.querySelectorAll('.history-content').forEach(el => {
                const rawHistory = el.getAttribute('data-raw');
                if (rawHistory) {
                    el.innerHTML = formatMarkdown(rawHistory);
                    // Apply the exact same class used for the main answer
                    el.classList.add('answer-text');
                    el.style.opacity = "1"; // Ensure it is fully visible like the main answer
                }
            });

            // 3. Scroll to the latest response for better UX
            const latestResponse = document.getElementById('latest-response');
            if (latestResponse) {
                latestResponse.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });

        document.getElementById('followUpForm').addEventListener('submit', function() {
            const btn = document.getElementById('followUpSubmit');
            btn.disabled = true;
            // Set color to gold or white to ensure it shows up in the dark button
            btn.innerHTML =
                '<div class="spinner" style="border-color: var(--light-gold); border-top-color: transparent;"></div>';
        });

        /**
         * Toggles the visibility of the Detailed Analysis card
         */
        function toggleDetailed() {
            if (!detailedLoaded) {
                content.classList.add('show');
                toggle.classList.add('expanded');
                toggleText.textContent = 'Hide detailed analysis';
                detailedVisible = true;
                loadDetailedAnalysis();
            } else {
                detailedVisible = !detailedVisible;
                content.classList.toggle('show', detailedVisible);
                toggle.classList.toggle('expanded', detailedVisible);
                toggleText.textContent = detailedVisible ? 'Hide detailed analysis' : 'Show detailed analysis';
            }
        }

        /**
         * Performs the AJAX call to get the detailed analysis
         */
        function loadDetailedAnalysis() {
            // Ensure we have a fresh CSRF token
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('{{ route('detailed') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        query: query,
                        history: history
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Server returned ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    detailedLoadingElement.style.display = 'none';
                    detailedLoaded = true;

                    if (data.success) {
                        // Use innerHTML to render the formatted HTML from Markdown
                        detailedTextElement.innerHTML = formatMarkdown(data.analysis);
                    } else {
                        detailedTextElement.textContent = data.analysis || 'Unable to load analysis.';
                    }
                })
                .catch(error => {
                    console.error('Detailed Analysis Error:', error);
                    detailedLoadingElement.style.display = 'none';
                    detailedTextElement.textContent =
                        'Unable to load detailed analysis due to a network error. Please try again.';
                    detailedLoaded = true;
                });

            window.addEventListener('load', () => {
                const latestResponse = document.getElementById('latest-response');
                if (latestResponse) {
                    latestResponse.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        }
    </script>
@endsection
