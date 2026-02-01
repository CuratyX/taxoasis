@extends('layouts.app')

@section('title', 'TaxOasis — Your Tax Caravan Stops Here')

@section('styles')
    <style>
        :root {
            --brand-gold: #B8860B;
            --light-gold: #D4A855;
            --brand-dark: #2C3E50;
            --bg-dark: #2C3E50;
            --white-glass: rgba(255, 255, 255, 0.06);
        }

        body {
            /* Applied the radial glow effect seen in the image background */
            background: radial-gradient(circle at center, #26384a 0%, var(--bg-dark) 100%);
            font-family: 'Inter', -apple-system, sans-serif;
            margin: 0;
            color: white;
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

        /* Logo & Wordmark matching image */
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

        .wordmark {
            font-size: 42px;
            letter-spacing: 1px;
        }

        .wordmark-tax {
            font-weight: 500;
            opacity: 0.9;
        }

        .wordmark-oasis {
            color: var(--brand-gold);
            font-family: "DM Serif Display", Georgia, serif;
        }

        /* Tagline Style from Image */
        .hero-subtitle {
            font-size: 22px;
            color: rgba(255, 255, 255, 0.85);
            text-align: center;
            font-weight: 300;
            margin-bottom: 2rem;
        }

        .hero-subtitle span {
            color: var(--brand-gold);
            font-family: "DM Serif Display", Georgia, serif;
            font-style: italic;
        }

        /* Search Bar - Exact Image Styling */
        .query-container {
            width: 100%;
            background: #fff;
            border-radius: 14px;
            padding: 8px 12px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            /* margin-bottom: 25px; */
        }

        .query-input {
            flex: 1;
            border: none;
            padding: 15px 0px;
            font-size: 18px;
            outline: none;
            color: #333;
            background: transparent;
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
        }

        .example-btn:hover {
            border-color: var(--brand-gold);
            color: var(--brand-gold);
            background: rgba(255, 255, 255, 0.1);
        }

        /* Newsletter Section Styling from Image */
        .newsletter-section {
            width: 100%;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .news-title {
            color: var(--brand-gold);
            font-size: 26px;
            font-family: "DM Serif Display", serif;
            margin-bottom: 12px;
        }

        .news-sub {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            margin-bottom: 35px;
        }

        .news-input {
            width: 100%;
            background: white;
            border: none;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 15px;
            font-size: 16px;
            box-sizing: border-box;
        }

        /* Footer Bottom Styling */
        .footer-nav {
            padding: 40px 24px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

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
            color: var(--brand-gold);
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
                        fill="var(--brand-gold)">O</text>
                    <line x1="17" y1="34" x2="51" y2="34" stroke="var(--light-gold)" stroke-width="2.5"
                        stroke-linecap="round" />
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
            <input type="text" name="query" class="query-input" placeholder="What do you need to know?"
                autocomplete="off" required maxlength="1000" id="queryInput">
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

        {{-- Newsletter Section - Kept your 'newsletter.subscribe' route --}}
        {{-- Newsletter Section --}}
        <section class="newsletter-section"
            style="border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: 40px; padding-top: 50px;">
            <div class="newsletter-container" style="max-width: 850px; margin: 0 auto; text-align: center;">
                <h3 class="newsletter-title"
                    style="color: var(--brand-gold); font-weight:900;font-size: 2rem; font-family: 'DM Serif Display', serif; margin-bottom: 12px;">
                    Tax Caravan Despatch</h3>
                <p class="newsletter-description"
                    style="font-size: 16px; color: rgba(255, 255, 255, 0.7); line-height: 1.6; margin-bottom: 35px;">
                    UAE tax innovations and compliance strategies.<br>
                    Expert insights delivered monthly.
                </p>

                <form action="#" method="POST" class="newsletter-form" id="newsletterForm"
                    style="display: flex; flex-direction: column; gap: 15px;">
                    @csrf
                    <input type="text" name="name" class="newsletter-input" placeholder="Your Name" required
                        style="width: 100%; padding: 16px 20px; border-radius: 8px; border: none; font-size: 16px;">
                    <input type="email" name="email" class="newsletter-input" placeholder="Your Email" required
                        style="width: 100%; padding: 16px 20px; border-radius: 8px; border: none; font-size: 16px;">
                    <button type="submit" class="newsletter-submit"
                        style="width: 100%; background: var(--light-gold); color: var(--brand-dark); font-weight: 700; padding: 16px; border-radius: 8px; border: none; font-size: 16px; cursor: pointer; transition: opacity 0.2s;">
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
    </script>
    <style>
        .loader {
            width: 20px;
            height: 20px;
            border: 2px solid var(--brand-gold);
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
