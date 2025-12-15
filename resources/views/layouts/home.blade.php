@extends('layouts.app')

@section('title', 'TaxOasis - Your Tax Caravan Stops Here')

@section('styles')
<style>
    .home-container {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .tagline {
        font-size: 24px;
        font-weight: 500;
        color: var(--slate-light);
        margin-bottom: 48px;
        text-align: center;
        font-style: italic;
    }

    .footer-fixed {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: var(--warm-white);
    }
</style>
@endsection

@section('content')
<div class="home-container">
    <!-- Logo -->
    <div class="logo" style="margin-bottom: 16px;">
        <div class="logo-mark">
            <span>TO</span>
        </div>
        <span class="logo-text">TaxOasis</span>
    </div>

    <!-- Tagline -->
    <h1 class="tagline">Your tax caravan stops here.</h1>

    <!-- Query Form -->
    <form action="{{ route('ask') }}" method="POST" class="query-container" id="queryForm">
        @csrf
        <input 
            type="text" 
            name="query" 
            class="query-input" 
            placeholder="What do you need to know?"
            autocomplete="off"
            required
            maxlength="1000"
            id="queryInput"
        >
        <button type="submit" class="submit-btn" id="submitBtn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </button>
    </form>

    <!-- Example Queries -->
    <div class="examples">
        <button type="button" class="example-btn" onclick="setQuery('Do free zone companies pay corporate tax?')">
            Do free zone companies pay corporate tax?
        </button>
        <button type="button" class="example-btn" onclick="setQuery('What is Small Business Relief?')">
            What is Small Business Relief?
        </button>
        <button type="button" class="example-btn" onclick="setQuery('When must I register for CT?')">
            When must I register for CT?
        </button>
    </div>
</div>

<!-- Footer -->
<div class="footer footer-fixed">
    Powered by <span class="footer-highlight">Ecovis JRB</span> · DIFC Innovation Hub
</div>
@endsection

@section('scripts')
<script>
    function setQuery(text) {
        document.getElementById('queryInput').value = text;
        document.getElementById('queryInput').focus();
    }

    // Show loading state on form submit
    document.getElementById('queryForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<div class="spinner"></div>';
    });
</script>
@endsection
