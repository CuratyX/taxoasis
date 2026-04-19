@extends('layouts.app')

@section('title', 'TaxOasis — Your Tax Caravan Stops Here')

@section('styles')
    <style>
        :root {
            --light-gold: #B8860B;
            --light-gold: #D4A855;
            --brand-dark: #2C3E50;
            --bg-dark: #2C3E50;
            --white-glass: rgba(255, 255, 255, 0.06);
        }

        body {
            background: radial-gradient(circle at center, #26384a 0%, var(--bg-dark) 100%);
            /* Updated Body Font per Guide: Plus Jakarta Sans */
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            margin: 0;
            color: white;
            font-weight: 400;
        }

        .home-container {
            min-height: 85vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            max-width: 850px;
            margin: 0 auto;
        }

        /* Logo & Wordmark */
        .logo-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            margin-top: 4rem;
        }

        .logo-icon-box {
            width: 80px;
            height: 80px;
            background: var(--white-glass);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Updated Icon Font per Guide: DM Serif Display */
        .logo-icon-text {
            font-family: 'DM Serif Display', serif;
            font-size: 40px;
            fill: var(--light-gold);
        }

        .wordmark {
            font-size: 2rem;
            /* letter-spacing removed here as it varies by part */
        }

        /* Updated Wordmark "TAX" per Guide: Plus Jakarta Sans, Bold, Uppercase, +0.02em */
        .wordmark-tax {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            opacity: 1;
            /* Guide shows it solid dark/white */
            color: white;
        }

        /* Updated Wordmark "Oasis" per Guide: Libre Baskerville, Regular */
        .wordmark-oasis {
            font-family: 'Libre Baskerville', serif;
            font-weight: 400;
            color: var(--light-gold);
        }

        /* Hero Subtitle */
        .hero-subtitle {
            color: rgba(255, 255, 255, 0.85);
            text-align: center;
            font-weight: 300;
            /* Light weight for contrast */
            margin-bottom: 2rem;
        }

        .hero-subtitle span {
            color: var(--light-gold);
            font-family: "DM Serif Display", serif;
            font-style: italic;
        }

        /* Search Bar */
        .query-container {
            width: 100%;
            background: #fff;
            border-radius: 14px;
            padding: 8px 12px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
        }

        .query-input {
            flex: 1;
            border: none;
            padding: 15px 42px 15px 0px;
            font-size: 1rem;
            outline: none;
            color: #333;
            background: transparent;
            font-family: 'Plus Jakarta Sans', sans-serif;
            resize: none;
            overflow-y: hidden;
            line-height: 1.5;
            min-height: 24px;
            max-height: 150px;
        }

        .submit-btn {
            background-color: #4A5568;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .submit-btn:hover {
            background-color: #3d5369;
        }

        /* Pill/Example Buttons */
        .examples {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            color: rgba(255, 255, 255, 0.7);
        }

        .example-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 10px 22px;
            border-radius: 50px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 500;
        }

        .example-btn:hover {
            border-color: var(--light-gold);
            color: var(--light-gold);
            background: rgba(255, 255, 255, 0.1);
        }

        /* Newsletter Section */
        .newsletter-section {
            width: 100%;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            margin-top: 40px;
            padding-top: 50px;
        }

        .newsletter-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
        }

        .newsletter-title {
            font-size: 1.5rem;
        }

        .newsletter-description {
            font-size: .8rem;
        }

        .newsletter-input {
            width: 100%;
            padding: 10px 10px;
            border-radius: 8px;
            border: none;
            font-size: .8rem;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .newsletter-submit {
            width: 100%;
            background: var(--light-gold);
            color: var(--brand-dark);
            font-weight: 700;
            padding: 10px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            cursor: pointer;
            transition: opacity 0.2s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .newsletter-section {
            width: 100%;
            /* Gradient transition from Body to Footer */
            padding: 80px 24px;
            text-align: center;
            margin-top: 60px;
        }

        .newsletter-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .newsletter-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            /* font-size: 15px; */
            font-weight: 600;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--light-gold);
            margin-bottom: 16px;
        }

        .newsletter-description {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 16px;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 36px;
            line-height: 1.6;
        }

        /* Inline Form Layout */
        .newsletter-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .newsletter-input {
            flex: 1;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            color: white;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 300;
            outline: none;
            transition: border-color 0.25s;
            width: 100%;
            /* Default to full width on mobile */
            box-sizing: border-box;
        }

        .newsletter-input:focus {
            border-color: var(--light-gold);
        }

        .newsletter-input::placeholder {
            color: rgba(255, 255, 255, 0.45);
        }

        .newsletter-submit {
            padding: 14px 32px;
            background: var(--light-gold);
            border: none;
            border-radius: 6px;
            color: white;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.8px;
            cursor: pointer;
            transition: background 0.25s, transform 0.15s;
            white-space: nowrap;
            width: 100%;
        }

        .newsletter-submit:hover {
            background: #D4A017;
            transform: translateY(-1px);
        }

        /* Tablet & Desktop: Switch to Inline Row */
        @media (min-width: 768px) {
            .newsletter-form {
                flex-direction: row;
                align-items: stretch;
            }

            .newsletter-submit {
                width: auto;
            }
        }

        @media (min-width: 768px) {

            .wordmark {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 22px;
            }

            .query-input {
                font-size: 18px;
            }

            .newsletter-section {
                margin-top: 180px;
            }

            .newsletter-form {
                flex-direction: row;
                align-items: center;
            }

            .newsletter-input {
                flex: 1;
                padding: 16px 20px;
                font-size: 16px;
            }

            .newsletter-submit {
                width: auto;
                min-width: 180px;
                flex-shrink: 0;
                padding: 16px;
            }

            .newsletter-title {
                font-size: 1.5rem;
            }

            .newsletter-description {
                font-size: 1rem;
            }
        }

        .news-title {
            color: var(--light-gold);
            font-size: 26px;
            font-family: "DM Serif Display", serif;
            /* Display font for main heading */
            margin-bottom: 12px;
        }

        /* Footer styling ... */
        .footer-nav {
            padding: 40px 24px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        /* ... existing footer styles ... */
        .footer-links {
            display: flex;
            gap: 32px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
        }

        .footer-links a {
            text-decoration: none;
            color: inherit;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: var(--light-gold);
        }

        .ecovis-branding {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
            opacity: 0.8;
        }
    </style>
@endsection

@section('content')
    @include('partials.header')

    <div class="home-container">
        {{-- Logo Section --}}
        <div class="logo-section">
            <div class="logo-icon-box">
                <svg width="45" height="45" viewBox="0 0 68 68" fill="none">
                    <text x="34" y="47" text-anchor="middle" font-family="DM Serif Display, serif" font-size="40"
                        fill="var(--light-gold)">O</text>
                    <line x1="17" y1="34" x2="51" y2="34" stroke="var(--light-gold)"
                        stroke-width="2.5" stroke-linecap="round" />
                </svg>
            </div>
            <div class="wordmark">
                <span class="wordmark-tax">TAX</span><span class="wordmark-oasis">Oasis</span>
            </div>
        </div>

        <p class="hero-subtitle">Your tax caravan <span>stops here</span></p>

        {{-- Main Query Form - Kept your 'ask' route --}}
        <form action="{{ route('ask') }}" method="POST" class="query-container" id="queryForm">
            @csrf
            <textarea name="query" class="query-input" placeholder="What do you need to know?"
                required maxlength="1000" id="queryInput" rows="1"></textarea>
            <button type="submit" class="submit-btn" id="submitBtn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </form>

        <div class="examples">
            <button type="button" class="example-btn" onclick="setQuery('Do I need to register for CT?')">Do I need to
                register for CT?</button>
            <button type="button" class="example-btn" onclick="setQuery('VAT registration thresholds')">VAT registration
                thresholds</button>
            <button type="button" class="example-btn" onclick="setQuery('Small Business Relief')">Small Business
                Relief</button>
            <button type="button" class="example-btn" onclick="setQuery('Free Zone tax benefits')">Free Zone tax
                benefits</button>
        </div>

        {{-- Newsletter Section --}}
        <section class="newsletter-section">
    <div class="newsletter-container">
        {{-- Updated Text Hierarchy --}}
        <h3 class="newsletter-title">Stay Informed</h3>
        <p class="newsletter-description">
            Expert tax insights for UAE professionals and businesses — delivered to your inbox.
        </p>

        {{-- Feedback Container (Keeps functionality) --}}
        <div id="subscription-feedback" style="display:none; margin-bottom: 15px; padding: 10px; border-radius: 6px; font-size: 14px;"></div>

        {{-- Updated Form Layout (Keeps IDs for AJAX) --}}
        <form id="newsletterForm" class="newsletter-form">
            @csrf
            <input type="text" name="name" class="newsletter-input" placeholder="Your name" required>
            <input type="email" name="email" class="newsletter-input" placeholder="Your email" required>
            <button type="submit" id="newsletterBtn" class="newsletter-submit">
                Subscribe
            </button>
        </form>
    </div>
</section>

        {{-- Finally, include the footer partial --}}
    </div>
    @include('partials.footer')
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/lottie-web@5.12.2/build/player/lottie.min.js"></script>
    <script>
        function setQuery(text) {
            const input = document.getElementById('queryInput');
            input.value = text;
            input.focus();
        }

        function resetButton() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = false;
            btn.innerHTML = `
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>`;
        }

        document.getElementById('queryForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="loader"></span>';
        });

        window.addEventListener('pageshow', function(event) {
            resetButton();
        });

        $(document).ready(function() {
            $('#newsletterForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                let formData = $(this).serialize();
                let btn = $('#newsletterBtn');
                let feedback = $('#subscription-feedback');

                // Disable button and show loading state
                btn.prop('disabled', true).text('Signing up...');
                feedback.hide().removeClass('success-msg error-msg');

                $.ajax({
                    url: "{{ route('subscribe') }}",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        // Success Feedback (includes "Already subscribed" case)
                        feedback.text(response.message)
                            .css({
                                'background-color': 'rgba(13, 148, 136, 0.2)',
                                'color': '#D4A855',
                                'border': '1px solid #D4A855',
                                'margin-bottom': '1rem',
                                'padding': '0.5rem 1rem',
                                'border-radius': '0.25rem'
                            })
                            .fadeIn();

                        // Clear inputs
                        $('#newsletterForm')[0].reset();

                        // Remove message and styling after 3 seconds
                        setTimeout(function() {
                            feedback.fadeOut(500, function() {
                                $(this).text('').removeAttr('style').hide();
                            });
                        }, 3000);
                    },
                    error: function(xhr) {
                        // Error Feedback
                        let errorMsg = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        feedback.text(errorMsg)
                            .css({
                                'background-color': 'rgba(220, 38, 38, 0.2)',
                                'color': '#ff6b6b',
                                'border': '1px solid #ff6b6b'
                            })
                            .fadeIn();

                        // Remove message and styling after 3 seconds
                        setTimeout(function() {
                            feedback.fadeOut(500, function() {
                                $(this).text('').removeAttr('style').hide();
                            });
                        }, 3000);
                    },
                    complete: function() {
                        // Reset button
                        btn.prop('disabled', false).text('Subscribe');
                    }
                });
            });
        });

        const queryInput = document.getElementById('queryInput');
        const queryForm = document.getElementById('queryForm');

        // Auto-expand textarea as user types
        queryInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Submit on 'Enter' (unless Shift is held)
        queryInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim() !== '') {
                    document.getElementById('submitBtn').click(); // Triggers existing loading state logic
                }
            }
        });
    </script>
    <style>
        .loader {
            width: 20px;
            height: 20px;
            border: 2px solid var(--light-gold);
            border-top: 2px solid transparent;
            border-radius: 50%;
            display: inline-block;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection
