@once
    <style>
        @keyframes nb-slide-up {
            from {
                opacity: 0;
                transform: translateY(28px) scale(.94);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes nb-slide-down {
            to {
                opacity: 0;
                transform: translateY(20px) scale(.95);
            }
        }

        @keyframes nb-shimmer-border {
            0% {
                background-position: 0% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        .nb-banner-entering {
            animation: nb-slide-up .48s cubic-bezier(.34, 1.56, .64, 1) both;
        }

        .nb-banner-leaving {
            animation: nb-slide-down .35s cubic-bezier(.55, .06, .68, .19) forwards;
        }

        .nb-shimmer-border::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1.5px;
            background: linear-gradient(90deg, #219653, #1b7a44, #4ade80, #219653);
            background-size: 200% 100%;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            animation: nb-shimmer-border 4s linear infinite;
            pointer-events: none;
        }
    </style>
@endonce

<div x-data="{
        show: false,
        isDark: document.documentElement.classList.contains('dark'),
        _autoHideId: null,
        _cycleId: null,
        _hovered: false,
        _observer: null,
        init() {
            this._observer = new MutationObserver(() => {
                this.isDark = document.documentElement.classList.contains('dark');
            });
            this._observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            setTimeout(() => this._reveal(), 3500);
        },
        _reveal() {
            if (this.show) { return; }
            this.show = true;
            this._scheduleHide();
        },
        _scheduleHide() {
            if (this._autoHideId) { clearTimeout(this._autoHideId); }
            if (!this._hovered) {
                this._autoHideId = setTimeout(() => this._hide(), 8000);
            }
        },
        _hide() {
            if (this._autoHideId) { clearTimeout(this._autoHideId); this._autoHideId = null; }
            this.show = false;
            if (this._cycleId) { clearTimeout(this._cycleId); }
            this._cycleId = setTimeout(() => this._reveal(), 20000);
        },
        onMouseEnter() {
            this._hovered = true;
            if (this._autoHideId) { clearTimeout(this._autoHideId); this._autoHideId = null; }
        },
        onMouseLeave() {
            this._hovered = false;
            this._scheduleHide();
        },
        openChat() {
            this._hide();
            window.dispatchEvent(new CustomEvent('open-chatbot-widget'));
        }
    }" x-show="show" x-transition:enter="nb-banner-entering" x-transition:leave="nb-banner-leaving"
    @mouseenter="onMouseEnter()" @mouseleave="onMouseLeave()"
    class="fixed bottom-8 left-0 right-0 z-[9998] mx-auto w-max max-w-[calc(100vw-32px)]" style="display:none"
    role="status" aria-live="polite">
    {{-- Banner card --}}
    <div class="nb-shimmer-border relative flex items-center gap-3.5 rounded-full px-3.5 py-2.5"
        :class="isDark ? 'bg-[#1e2130] shadow-[0_0_0_1px_rgba(255,255,255,.08),0_8px_32px_rgba(0,0,0,.45),0_2px_8px_rgba(0,0,0,.3)]' : 'bg-white shadow-[0_0_0_1px_rgba(0,0,0,.08),0_8px_32px_rgba(0,0,0,.12),0_2px_8px_rgba(0,0,0,.06)]'">


        {{-- Avatar stack --}}
        <div class="flex shrink-0 items-center">
            <img src="{{ asset('images/Personnes/walid_photo.jpeg') }}" alt="Support agent"
                class="size-[38px] rounded-full object-cover border-[2.5px]"
                :class="isDark ? 'border-[#1e2130]' : 'border-white'">
            <img src="{{ asset('images/Personnes/Younes_Photo.jpg') }}" alt="Support agent"
                class="size-[38px] rounded-full object-cover border-[2.5px] -ml-2.5"
                :class="isDark ? 'border-[#1e2130]' : 'border-white'">
            <img src="{{ asset('images/Personnes/bilal_photo.jpeg') }}" alt="Support agent"
                class="size-[38px] rounded-full object-cover border-[2.5px] -ml-2.5"
                :class="isDark ? 'border-[#1e2130]' : 'border-white'">
        </div>

        {{-- Message --}}
        <p class="shrink-0 whitespace-nowrap font-[DM_Sans,ui-sans-serif,system-ui,sans-serif] text-[14.5px] font-normal leading-snug tracking-[.01em]"
            :class="isDark ? 'text-[#d8dce8]' : 'text-[#374151]'">
            <span x-text="$store.ui.t('notificationBannerLine')"></span>
            <strong class="font-semibold" :class="isDark ? 'text-white' : 'text-[#111827]'"
                x-text="$store.ui.t('notificationBannerStrong')"></strong>
        </p>

        {{-- CTA button --}}
        <button type="button" @click="openChat()"
            class="shrink-0 rounded-full px-[18px] py-[9px] font-[DM_Sans,ui-sans-serif,system-ui,sans-serif] text-[13.5px] font-semibold tracking-[.02em] text-white bg-linear-to-br from-[#219653] to-[#1b7a44] shadow-[0_2px_12px_rgba(33,150,83,.5)] transition-all duration-150 hover:-translate-y-[1.5px] hover:shadow-[0_6px_22px_rgba(33,150,83,.68)] active:scale-[.96]">
            <span x-text="$store.ui.t('notificationBannerCta')"></span>
        </button>

        {{-- Close button --}}
        <button type="button" @click="_hide()" :aria-label="$store.ui.t('notificationBannerDismiss')"
            class="ml-0.5 flex size-[30px] shrink-0 items-center justify-center rounded-full transition-all duration-200 hover:rotate-90"
            :class="isDark ? 'bg-[rgba(255,255,255,.07)] text-[#8892a4] hover:bg-white/15 hover:text-white' : 'bg-[rgba(0,0,0,.06)] text-[#6b7280] hover:bg-black/10 hover:text-black'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                stroke-linejoin="round" class="size-3.5" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>

    </div>
</div>