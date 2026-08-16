<footer x-data class="site-footer py-8 md:py-12 pt-12 md:pt-20 bg-transparent">
    <div class="site-footer-container container mx-auto px-4">
        <!-- Logo -->
        <div class="text-center mb-6 md:mb-8">
            <h2 class="text-xl md:text-2xl font-extrabold">
                @if(app()->getLocale() === 'en')
                    <span style="color:#5C2D62">ESCORT</span><span style="color:#DD3888">-ONLINE</span><span style="color:#8C8C8C;opacity:0.78">.COM</span>
                @else
                    <span style="color:#5C2D62">ZAŠUKEJ</span><span style="color:#DD3888">SI</span><span style="color:#8C8C8C;opacity:0.78">.CZ</span>
                @endif
            </h2>
        </div>

        <!-- Footer Content -->
        <div class="footer-main mx-auto h-[275px] flex items-center justify-between mb-6 md:mb-8">
            {{-- Left: the design's 141x55 button.

                 It used to be a hardcoded "Registrace" wrapped in @guest, so for
                 a signed-in visitor the slot vanished entirely — and because
                 this row is `justify-content: space-between`, the links and the
                 security box then slid left.

                 Both states are configured in the admin (Nastavení systému ->
                 Patička): which page the button opens and what it says. The slot
                 is never empty — an unpublished or deleted target falls back to
                 the built-in behaviour rather than disappearing. --}}
            @php $footerButton = \App\Support\FooterButton::forCurrentVisitor(); @endphp

            <div class="flex-shrink-0">
                @if($footerButton['opensRegisterModal'])
                    <button @click="$dispatch('show-register-modal')" class="btn-primary footer-register px-6 md:px-8 py-2.5 md:py-3 rounded-lg font-semibold text-sm md:text-base">
                        {{ $footerButton['label'] }}
                    </button>
                @else
                    <a href="{{ $footerButton['url'] }}" class="btn-primary footer-register px-6 md:px-8 py-2.5 md:py-3 rounded-lg font-semibold text-sm md:text-base">
                        {{ $footerButton['label'] }}
                    </a>
                @endif
            </div>

            {{-- CMS-driven footer links.

                 These were six <a href="#"> anchors that went nowhere, even
                 though Page already had a `display_in_footer` column and
                 AppServiceProvider was already sharing $footerPages with this
                 component. Which pages appear here is now managed in the admin
                 (Stránky -> "Zobrazit v patičce"); the three-column layout is
                 unchanged and fills column by column. --}}
            @php
                // Already ordered by AppServiceProvider's view composer
                // (sort_order, then created_at) — same order as the header.
                $footerLinks = collect($footerPages ?? [])->filter(fn ($page) => filled($page->slug));
                $footerColumns = $footerLinks->isEmpty()
                    ? collect()
                    : $footerLinks->chunk((int) ceil($footerLinks->count() / 3));
            @endphp

            <div class="footer-links">
                @foreach($footerColumns as $column)
                    <div class="footer-col">
                        @foreach($column as $page)
                            <a href="{{ url('/' . ltrim($page->slug, '/')) }}" class="footer-link">{{ $page->title }}</a>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <!-- Right: Security box -->
            <div class="hidden lg:block flex-shrink-0">
                <div class="footer-security flex items-center" role="note">
                    <img src="{{ asset('images/icons/lock.svg') }}" alt="lock" width="25" height="25" />
                    <div class="ml-3 footer-security-text">{{ __('front.footer.discreet') }}</div>
                </div>
            </div>
        </div>

        <div class="footer-mobile-languages lg:hidden">
            {{-- Driven by config/locales.php — the design shows three languages
                 and this used to be hardcoded to two. --}}
            @foreach(\App\Support\Locales::all() as $code => $meta)
                <a href="{{ url()->current() }}?locale={{ $code }}" class="footer-lang-card {{ app()->getLocale() === $code ? 'is-active' : '' }}">
                    <img src="{{ asset($meta['flag']) }}" alt="{{ $meta['native'] }}" class="footer-lang-flag">
                    <span class="footer-lang-label">{{ $meta['native'] }}</span>
                </a>
            @endforeach
        </div>

        <!-- Security Info -->
        <div class="footer-meta pt-6 md:pt-8 border-t md:border-t-0">
            <!-- Mobile: Stacked Layout -->
            <div class="footer-mobile-meta flex flex-col items-center gap-2 lg:hidden">
                <div class="footer-security footer-security-mobile flex items-center" role="note">
                    <img src="{{ asset('images/icons/lock.svg') }}" alt="lock" width="27" height="30" />
                    <div class="ml-3 footer-security-text">{{ __('front.footer.discreet') }}</div>
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