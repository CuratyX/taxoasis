<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ asset('assets/favicon/taxoasis-icon-64.png') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TaxOasis - UAE Tax Intelligence powered by Ecovis JRB. Ask questions about UAE Corporate Tax, VAT, and Free Zone taxation.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'TaxOasis - Your Tax Caravan Stops Here')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        :root {
            --warm-white: #FDFCFB;
            --teal: #0D9488;
            --teal-dark: #0F766E;
            --teal-light: #14B8A6;
            --gold: #D97706;
            --gold-light: #F59E0B;
            --slate: #1E293B;
            --slate-light: #475569;
            --gray: #64748B;
            --gray-light: #94A3B8;
            --border: #E2E8F0;
            --white: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--warm-white);
            color: var(--slate);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Logo styles */
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-mark {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--teal) 0%, var(--teal-dark) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
        }

        .logo-mark.small {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            box-shadow: none;
        }

        .logo-mark span {
            color: white;
            font-weight: 700;
            font-size: 20px;
            letter-spacing: -0.5px;
        }

        .logo-mark.small span {
            font-size: 14px;
        }

        .logo-text {
            font-size: 32px;
            font-weight: 600;
            color: var(--slate);
            letter-spacing: -0.5px;
        }

        .logo-text.small {
            font-size: 20px;
        }

        /* Query input */
        .query-container {
            position: relative;
            width: 100%;
            max-width: 600px;
        }

        .query-input::placeholder {
            color: var(--gray-light);
        }

        .submit-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            border-radius: 12px;
            border: none;
            background-color: var(--teal);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .submit-btn:hover {
            background-color: #3d5369;
        }

        .submit-btn:disabled {
            background-color: var(--border);
            cursor: not-allowed;
        }

        /* Example queries */
        .examples {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-top: 32px;
        }

        .example-btn {
            padding: 8px 16px;
            font-size: 13px;
            font-family: inherit;
            color: var(--gray);
            background-color: var(--white);
            border: 1px solid var(--border);
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .example-btn:hover {
            border-color: var(--teal);
            color: var(--teal);
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 13px;
            color: var(--gray-light);
            padding: 24px;
        }

        .footer-highlight {
            color: var(--slate-light);
            font-weight: 500;
        }

        /* Button styles */
        .btn {
            display: inline-block;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-outline {
            color: var(--teal);
            background-color: transparent;
            border: 1px solid var(--teal);
        }

        .btn-outline:hover {
            background-color: var(--teal);
            color: white;
        }

        .btn-gold {
            color: white;
            background-color: var(--gold);
            border: none;
        }

        .btn-gold:hover {
            background-color: var(--gold-light);
        }

        /* Card styles */
        .card {
            padding: 24px;
            border-radius: 12px;
            background-color: var(--white);
            border: 1px solid var(--border);
        }

        .card-warning {
            background-color: #FFFBEB;
            border-color: #FDE68A;
        }

        .card-gray {
            background-color: #F8FAFC;
        }

        /* Loading spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Utility classes */
        .text-center { text-align: center; }
        .mt-4 { margin-top: 16px; }
        .mt-6 { margin-top: 24px; }
        .mt-8 { margin-top: 32px; }
        .mb-4 { margin-top: 16px; }
        .mb-6 { margin-bottom: 24px; }
    </style>

    @yield('styles')
</head>
<body>
    @yield('content')

    @yield('scripts')
</body>
</html>
