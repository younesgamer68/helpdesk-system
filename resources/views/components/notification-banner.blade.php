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
            background: linear-gradient(90deg, #7c6bff, #c87dff, #6bceff, #7c6bff);
            background-size: 200% 100%;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            animation: nb-shimmer-border 4s linear infinite;
            pointer-events: none;
        }

        .nb-cta-btn {
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .nb-cta-btn:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 6px 22px rgba(100, 80, 255, .68) !important;
        }

        .nb-cta-btn:active {
            transform: scale(.96);
        }

        .nb-close-btn {
            transition: background .2s ease, color .2s ease, transform .2s ease;
        }

        .nb-close-btn:hover {
            background: rgba(255, 255, 255, .15) !important;
            color: #fff !important;
            transform: rotate(90deg);
        }

        .nb-avatar-img {
            transition: transform .2s ease;
        }

        .nb-avatar-img:hover {
            transform: translateY(-2px) scale(1.08);
        }
    </style>
@endonce

<div x-data="{
        show: false,
        _autoHideId: null,
        _cycleId: null,
        init() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            setTimeout(() => this._reveal(), 3500);
            this._cycleId = setInterval(() => this._reveal(), 10000);
        },
        _reveal() {
            if (this.show) { return; }
            this.show = true;
            this._autoHideId = setTimeout(() => this._hide(), 8000);
        },
        _hide() {
            if (this._autoHideId) { clearTimeout(this._autoHideId); this._autoHideId = null; }
            this.show = false;
        },
        openChat() {
            this._hide();
            window.dispatchEvent(new CustomEvent('open-chatbot-widget'));
        }
    }" x-show="show" x-transition:enter="nb-banner-entering" x-transition:leave="nb-banner-leaving"
    style="position:fixed;bottom:32px;left:0;right:0;margin:0 auto;width:max-content;max-width:calc(100vw - 32px);z-index:9998;display:none"
    role="status" aria-live="polite">
    {{-- Banner card --}}
    <div class="nb-shimmer-border relative flex items-center gap-3.5 rounded-full bg-[#1e2130] px-3.5 py-2.5"
        style="box-shadow: 0 0 0 1px rgba(255,255,255,.08), 0 8px 32px rgba(0,0,0,.45), 0 2px 8px rgba(0,0,0,.3);">

        {{-- Avatar stack --}}
        <div class="flex shrink-0 items-center">
            <img src="https://i.pravatar.cc/80?img=11" alt="Support agent"
                class="nb-avatar-img size-[38px] rounded-full object-cover" style="border: 2.5px solid #1e2130;">
            <img src="https://i.pravatar.cc/80?img=5" alt="Support agent"
                class="nb-avatar-img size-[38px] rounded-full object-cover"
                style="border: 2.5px solid #1e2130; margin-left: -10px;">
            <img src="https://i.pravatar.cc/80?img=32" alt="Support agent"
                class="nb-avatar-img size-[38px] rounded-full object-cover"
                style="border: 2.5px solid #1e2130; margin-left: -10px;">
        </div>

        {{-- Message --}}
        <p class="shrink-0 whitespace-nowrap font-[DM_Sans,ui-sans-serif,system-ui,sans-serif] text-[14.5px] font-normal leading-snug tracking-[.01em]"
            style="color: #d8dce8;">
            We'd love to help. Talk to our
            <strong style="color: #fff; font-weight: 600;">support heroes.</strong>
        </p>

        {{-- CTA button --}}
        <button type="button" @click="openChat()"
            class="nb-cta-btn shrink-0 rounded-full px-[18px] py-[9px] font-[DM_Sans,ui-sans-serif,system-ui,sans-serif] text-[13.5px] font-semibold tracking-[.02em] text-white"
            style="background: linear-gradient(135deg, #4f5bff 0%, #7a4fff 100%); box-shadow: 0 2px 12px rgba(100,80,255,.5);">
            Chat now →
        </button>

        {{-- Close button --}}
        <button type="button" @click="_hide()" aria-label="Dismiss notification"
            class="nb-close-btn ml-0.5 flex size-[30px] shrink-0 items-center justify-center rounded-full"
            style="background: rgba(255,255,255,.07); color: #8892a4;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                stroke-linejoin="round" class="size-3.5" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>

    </div>
</div>