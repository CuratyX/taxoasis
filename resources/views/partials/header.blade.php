<style>
    .main-header {
        position: absolute;
        top: 0;
        width: 100%;
        padding: 30px 20px;
        z-index: 100;
        box-sizing: border-box;
    }

    .header-container {
        display: flex;
        /* Fix: Push logo to the left end and menu to the right end */
        justify-content: space-between;
        align-items: center;
        max-width: 1400px;
        margin: 0 auto;
    }

    .brand-link {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }

    .header-logo-box {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.06);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .header-wordmark {
        font-size: 20px;
        letter-spacing: 0.5px;
    }

    .menu-trigger {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.3s;
    }

    .menu-trigger:hover { background: rgba(255, 255, 255, 0.1); }

    .hamburger-icon {
        width: 24px;
        height: 18px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .hamburger-icon span {
        display: block;
        height: 2px;
        width: 100%;
        background: #fff;
        border-radius: 2px;
    }

    /* Side Drawer Styles */
    .side-drawer {
        position: fixed;
        top: 0;
        right: -320px;
        width: 320px;
        height: 100%;
        background: #1a2634;
        z-index: 1001;
        transition: 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        padding: 40px 24px;
    }
    .side-drawer.active { right: 0; }
    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(4px);
        display: none;
        z-index: 1000;
    }
    .drawer-overlay.active { display: block; }
    .drawer-links { display: flex; flex-direction: column; gap: 15px; margin-top: 50px; }
    .drawer-links a {
        color: #fff;
        text-decoration: none;
        font-size: 20px;
        padding: 10px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .close-btn { background: none; border: none; color: white; font-size: 35px; cursor: pointer; }
</style>

<header class="main-header">
    <div class="header-container">
        {{-- Logo added to the left --}}
        <a href="{{ route('home') }}" class="brand-link" style="text-decoration: none;">
            {{-- <div class="header-logo-box">
                <svg width="24" height="24" viewBox="0 0 68 68" fill="none">
                    <text x="34" y="47" text-anchor="middle" font-family="serif" font-size="40" fill="var(--brand-gold)">O</text>
                    <line x1="17" y1="34" x2="51" y2="34" stroke="var(--light-gold)" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div> --}}
            <div class="header-wordmark">
                <span style="font-weight: 500; color: white; opacity: 0.9;">TAX</span><span style="color: var(--light-gold); font-family: 'DM Serif Display', serif;">Oasis</span>
            </div>
        </a>

        <button class="menu-trigger" id="menuToggle" onclick="toggleMenu()" aria-label="Open Menu">
            <div class="hamburger-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>
    </div>
</header>

<div id="sideDrawer" class="side-drawer">
    <div class="drawer-header">
        <button class="close-btn" onclick="toggleMenu()">&times;</button>
    </div>
    <nav class="drawer-links">
        <a href="/about">About Us</a>
        <a href="/services">Services</a>
        <a href="/resources">Resources</a>
        <a href="/contact">Contact</a>
    </nav>
</div>
<div id="drawerOverlay" class="drawer-overlay" onclick="toggleMenu()"></div>

{{-- <style>
    .main-header {
        position: absolute;
        top: 0;
        width: 100%;
        padding: 30px 40px;
        z-index: 100;
        box-sizing: border-box;
    }
    .header-container {
        display: flex;
        justify-content: flex-end;
        max-width: 1400px;
        margin: 0 auto;
    }
    .menu-trigger {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.3s;
    }
    .menu-trigger:hover { background: rgba(255, 255, 255, 0.1); }

    .hamburger-icon {
        width: 24px;
        height: 18px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .hamburger-icon span {
        display: block;
        height: 2px;
        width: 100%;
        background: #fff;
        border-radius: 2px;
    }

    /* Drawer Styles */
    .side-drawer {
        position: fixed;
        top: 0;
        right: -320px;
        width: 320px;
        height: 100%;
        background: #1a2634; /* Darker blue to match theme */
        z-index: 1001;
        transition: 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        padding: 40px 24px;
    }
    .side-drawer.active { right: 0; }
    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(4px);
        display: none;
        z-index: 1000;
    }
    .drawer-overlay.active { display: block; }
    .drawer-links { display: flex; flex-direction: column; gap: 15px; margin-top: 50px; }
    .drawer-links a {
        color: #fff;
        text-decoration: none;
        font-size: 20px;
        padding: 10px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .close-btn { background: none; border: none; color: white; font-size: 35px; cursor: pointer; }
</style> --}}

{{-- <script>
    function toggleMenu() {
        document.getElementById('sideDrawer').classList.toggle('active');
        document.getElementById('drawerOverlay').classList.toggle('active');
    }
</script> --}}
