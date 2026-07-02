<footer x-data class="site-footer py-8 md:py-12 pt-12 md:pt-20">
    <div class="site-footer-container container mx-auto px-4">
        <!-- Logo -->
        <div class="text-center mb-6 md:mb-8">
            <h2 class="text-xl md:text-2xl font-extrabold">
                <span style="color:#5C2D62">ZAŠUKEJ</span><span style="color:#DD3888">SI</span><span style="color:#8C8C8C;opacity:0.78">.CZ</span>
            </h2>
        </div>

        <!-- Footer Content -->
        <div class="footer-main mx-auto h-[275px] flex items-center justify-between mb-6 md:mb-8">
            <!-- Left: Registration Button -->
            <div class="flex-shrink-0">
                @guest
                <button @click="$dispatch('show-register-modal')" class="btn-primary footer-register px-6 md:px-8 py-2.5 md:py-3 rounded-lg font-semibold text-sm md:text-base">
                    {{ __('front.footer.registration') }}
                </button>
                @endguest
            </div>

            <!-- Center: Static Links (3 columns, left-aligned vertically stacked) -->
            <div class="footer-links">
                <div class="footer-col">
                    <a href="#" class="footer-link">Časté dotazy</a>
                    <a href="#" class="footer-link">Kontakt</a>
                </div>
                <div class="footer-col">
                    <a href="#" class="footer-link">Ochrana osobních údajů</a>
                    <a href="#" class="footer-link">Etika a bezpečnost</a>
                </div>
                <div class="footer-col">
                    <a href="#" class="footer-link">VIP účet pro dívky</a>
                    <a href="#" class="footer-link">Prémium účet pro pány</a>
                </div>
            </div>

            <!-- Right: Security box -->
            <div class="hidden lg:block flex-shrink-0">
                <div class="footer-security flex items-center" role="note">
                    <img src="{{ asset('images/icons/lock.svg') }}" alt="lock" width="25" height="25" />
                    <div class="ml-3 footer-security-text">Jsme 100% diskrétní platforma s<br>profesionální ochranou osobních údajů</div>
                </div>
            </div>
        </div>

        <div class="footer-mobile-languages lg:hidden">
            <a href="{{ url()->current() }}?locale=cs" class="footer-lang-card {{ app()->getLocale() === 'cs' ? 'is-active' : '' }}">
                <img src="{{ asset('flags/cs.png') }}" alt="Czech" class="footer-lang-flag">
                <span class="footer-lang-label">{{ __('front.nav.czech') }}</span>
            </a>
            <a href="{{ url()->current() }}?locale=en" class="footer-lang-card {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">
                <img src="{{ asset('flags/en.png') }}" alt="English" class="footer-lang-flag">
                <span class="footer-lang-label">{{ __('front.nav.english') }}</span>
            </a>
        </div>

        <!-- Security Info -->
        <div class="footer-meta pt-6 md:pt-8 border-t md:border-t-0">
            <!-- Mobile: Stacked Layout -->
            <div class="footer-mobile-meta flex flex-col items-center gap-2 lg:hidden">
                <div class="footer-security footer-security-mobile flex items-center" role="note">
                    <img src="{{ asset('images/icons/lock.svg') }}" alt="lock" width="27" height="30" />
                    <div class="ml-3 footer-security-text">Jsme 100% diskrétní platforma s<br>profesionální ochranou osobních údajů</div>
                </div>

                <div class="footer-eco-card" role="note">
                    <div class="footer-eco-icon-wrap">
                        <x-icons name="eco" class="footer-eco-icon" />
                    </div>
                    <div class="footer-eco-copy">
                        <span class="footer-eco-title">{{ __('front.footer.ecological') }}</span>
                        <span class="footer-eco-sub">{{ ltrim(__('front.footer.verification'), '- ') }}</span>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="footer-copyright footer-copyright-mobile text-center leading-relaxed pt-5 px-4">
                    {{ __('front.footer.copyright') }}
                </div>
            </div>

            <!-- Desktop: Horizontal Layout -->
            <div class="hidden lg:flex footer-meta-row">
                <div class="footer-meta-copy">
                    <span class="footer-eco-title">{{ __('front.footer.ecological') }}</span>
                    <span class="footer-eco-sub">{{ __('front.footer.verification') }}</span>
                </div>

                <hr class="footer-sep">

                <div class="footer-copyright">
                    {{ __('front.footer.copyright') }}
                </div>
            </div>
        </div>
    </div>
</footer>