@extends('layouts.app')

@section('title', 'TaxOasis - Response')

@section('styles')
<style>
    .header {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: var(--white);
    }

    .main-content {
        max-width: 720px;
        margin: 0 auto;
        padding: 40px 24px 120px 24px;
    }

    .query-section {
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 1px solid var(--border);
    }

    .query-label {
        font-size: 13px;
        color: var(--gray);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .query-text {
        font-size: 20px;
        font-weight: 500;
        color: var(--slate);
        line-height: 1.5;
    }

    .answer-text {
        font-size: 16px;
        color: var(--slate-light);
        line-height: 1.8;
        white-space: pre-wrap;
    }

    .detailed-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0;
        font-size: 14px;
        font-weight: 500;
        color: var(--teal);
        background: transparent;
        border: none;
        cursor: pointer;
        margin-bottom: 32px;
        font-family: inherit;
    }

    .detailed-link:hover {
        color: var(--teal-dark);
    }

    .detailed-link svg {
        transition: transform 0.2s ease;
    }

    .detailed-link.expanded svg {
        transform: rotate(90deg);
    }

    .detailed-content {
        padding: 24px;
        background-color: var(--white);
        border-radius: 12px;
        border: 1px solid var(--border);
        margin-bottom: 32px;
        display: none;
    }

    .detailed-content.show {
        display: block;
    }

    .detailed-text {
        font-size: 15px;
        color: var(--slate-light);
        line-height: 1.8;
        white-space: pre-wrap;
    }

    .cta-card {
        margin-bottom: 32px;
    }

    .cta-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--slate);
        margin-bottom: 8px;
    }

    .cta-text {
        font-size: 14px;
        color: var(--slate-light);
        line-height: 1.6;
        margin-bottom: 16px;
    }

    .disclaimer {
        font-size: 12px;
        color: var(--gray-light);
        line-height: 1.6;
        padding: 16px;
        background-color: #F8FAFC;
        border-radius: 8px;
    }

    .disclaimer strong {
        color: var(--gray);
    }

    .footer-result {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 16px;
        background-color: var(--warm-white);
        border-top: 1px solid var(--border);
        text-align: center;
    }

    .loading-indicator {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--gray);
        padding: 16px 0;
    }
</style>
@endsection

@section('content')
<!-- Header -->
<header class="header">
    <a href="{{ route('home') }}" class="logo" style="text-decoration: none;">
        <div class="logo-mark small">
            <span>TO</span>
        </div>
        <span class="logo-text small">TaxOasis</span>
    </a>
    <a href="{{ route('home') }}" class="btn btn-outline">New question</a>
</header>

<!-- Main Content -->
<main class="main-content">
    <!-- User Query -->
    <div class="query-section">
        <p class="query-label">Your question</p>
        <p class="query-text">{{ $query }}</p>
    </div>

    <!-- Answer -->
    <div style="margin-bottom: 24px;">
        <p class="answer-text">{{ $answer }}</p>
    </div>

    @if(!isset($isError) || !$isError)
    <!-- Detailed Analysis Toggle -->
    <button type="button" class="detailed-link" id="detailedToggle" onclick="toggleDetailed()">
        <span id="toggleText">Show detailed analysis</span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
    </button>

    <!-- Detailed Content -->
    <div class="detailed-content" id="detailedContent">
        <div class="loading-indicator" id="detailedLoading">
            <div class="spinner" style="border-color: var(--teal); border-top-color: transparent;"></div>
            Loading detailed analysis...
        </div>
        <p class="detailed-text" id="detailedText"></p>
    </div>
    @endif

    @if($showEcovisCTA)
    <!-- Ecovis CTA -->
    <div class="card card-warning cta-card">
        <h3 class="cta-title">Need certainty?</h3>
        <p class="cta-text">
            Tax situations vary based on specific circumstances. Speak with an Ecovis JRB expert for advice tailored to your situation.
        </p>
        <a href="https://ecovis.ae/contact" target="_blank" rel="noopener noreferrer" class="btn btn-gold">
            Book a Consultation
        </a>
    </div>
    @endif

    <!-- Disclaimer -->
    <p class="disclaimer">
        <strong>Disclaimer:</strong> TaxOasis AI provides general information only and does not constitute professional tax, legal, or financial advice. AI can make mistakes. Responses are based on available information and may not reflect the most recent regulatory changes. Always consult a qualified tax professional for advice specific to your circumstances.
    </p>
</main>

<!-- Footer -->
<footer class="footer footer-result">
    Powered by <span class="footer-highlight">Ecovis JRB</span> · DIFC Innovation Hub
</footer>
@endsection

@section('scripts')
<script>
    let detailedLoaded = false;
    let detailedVisible = false;
    const query = @json($query);
    const detailedTextElement = document.getElementById('detailedText');
    const detailedLoadingElement = document.getElementById('detailedLoading');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;


    // --- 💡 New Formatting Function ---
    function formatMarkdown(markdownText) {
        // This is a simple implementation. For production, use a library like marked.js.

        // 1. Convert **Bold** to <strong>
        let html = markdownText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // 2. Convert ## Headings to <h2> (Using ### for h3 and ## for h2 based on common usage)
        html = html.replace(/^###\s*(.*)$/gm, '<h3>$1</h3>');
        html = html.replace(/^##\s*(.*)$/gm, '<h2>$1</h2>');

        // 3. Convert - Lists to <ul><li>
        // (This is complex to do perfectly without a library, so we'll do a simple list conversion)
        html = html.replace(/^\*\s*(.*)$/gm, '<li>$1</li>');
        html = html.replace(/^-\s*(.*)$/gm, '<li>$1</li>');
        // Wrap lists if they exist
        if (html.includes('<li>')) {
            html = '<ul>' + html + '</ul>';
        }

        // 4. Convert double line breaks into paragraphs (simple p-tag replacement)
        html = html.replace(/\n\s*\n/g, '</p><p>');
        html = '<p>' + html + '</p>';

        // Return the HTML content
        return html;
    }


    function toggleDetailed() {
        // ... (rest of the toggle function remains the same)
        const content = document.getElementById('detailedContent');
        const toggle = document.getElementById('detailedToggle');
        const toggleText = document.getElementById('toggleText');

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

    function loadDetailedAnalysis() {
        detailedLoadingElement.style.display = 'flex';
        detailedTextElement.innerHTML = ''; // Clear previous content

        fetch('{{ route("detailed") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken // Use the defined variable
            },
            body: JSON.stringify({ query: query })
        })
        .then(response => response.json())
        .then(data => {
            detailedLoadingElement.style.display = 'none';
            detailedLoaded = true;

            if (data.success) {
                // --- 🌟 KEY CHANGE HERE 🌟 ---
                // Use innerHTML to insert the converted HTML
                detailedTextElement.innerHTML = formatMarkdown(data.analysis);
            } else {
                detailedTextElement.textContent = data.analysis;
            }
        })
        .catch(error => {
            detailedLoadingElement.style.display = 'none';
            detailedTextElement.textContent = 'Unable to load detailed analysis due to a network error. Please try again.';
            detailedLoaded = true;
        });
    }
</script>
@endsection
