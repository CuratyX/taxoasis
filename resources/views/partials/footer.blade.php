<style>
    @media (max-width: 470px) {
        .footer-links {
            margin-top: 30px;
            flex-direction: column;
            align-items: center;
            gap: 15px !important;
            margin-bottom: 20px !important;
        }

        .social-icons{
            gap: 2rem;
        }
    }

    @media (max-width: 767px) {
        .powered-by img{
            height: 50px;
            width: 100px;
        }
    }

    @media (min-width: 768px) {
        .powered-by img{
            height: 100px;
            width: 300px;
        }
    }

    @media (min-width: 471px) {
        .social-icons{
            margin-bottom:40px;
        }

        .social-icons{
            gap: 4rem;
        }
    }

    .social-link svg { fill: white; width: 18px; height: 18px; }
</style>

<footer class="site-footer" style="padding: 40px 24px 40px; text-align: center; color: rgba(255, 255, 255, 0.5);background-color: #4A5568">
    <div class="footer-content" style="max-width: 800px; margin: 0 auto;">

        {{-- Social Icons based on image --}}
        <div class="social-icons" style="display: flex; justify-content: center; align-items: center;">
            {{-- LinkedIn --}}
            <a href="https://www.linkedin.com/groups/10046172" target="_blank" rel="noopener noreferrer" class="social-link" style="width: 44px; height: 44px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; text-decoration: none;">
                <svg viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
            </a>
            {{-- Podcast/Mic --}}
            <a href="https://open.spotify.com/episode/2BwKArfpKaxmVisnwslO9O?si=cMfrqCSRT3iI75MlPI7pgw&t=23" target="_blank" rel="noopener noreferrer" class="social-link" style="width: 44px; height: 44px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; text-decoration: none;">
                <svg viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
            </a>
            {{-- YouTube --}}
            <a href="https://youtube.com/@taxoasis?si=BEzH8zbJbHlUPAKZ" target="_blank" rel="noopener noreferrer" class="social-link" style="width: 44px; height: 44px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; text-decoration: none;">
                <svg viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
            </a>
        </div>

        {{-- Legal Links --}}
        <nav class="footer-links" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 25px; margin-bottom: 40px; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
            <a href="#" style="color: inherit; text-decoration: none;">Privacy Policy</a>
            <a href="#" style="color: inherit; text-decoration: none;">Terms of Use</a>
            <a href="#" style="color: inherit; text-decoration: none;">Disclaimer</a>
        </nav>

        <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); width: 100px; margin: 0 auto 30px;">

        {{-- Powered By Ecovis --}}
        <div class="powered-by">
            <span style="font-size: 10px; text-transform: uppercase; letter-spacing: 2px; display: block; margin-bottom: 12px;">Powered by</span>
            <a href="https://www.ecovisjrb.ae" target="_blank" style="display: inline-flex; align-items: center; gap: 10px; text-decoration: none; color: white; font-weight: 700; font-size: 20px;">
                <img src="{{ asset('assets/logos/logo_ecovis_JRB.png') }}" alt="ECOVIS JRB Logo">

            </a>
        </div>

    </div>
</footer>
