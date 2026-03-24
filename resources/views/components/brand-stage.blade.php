{{-- =====================================================================
Brand Stage — Cycling brand logo carousel (6 visible slots, 12 brands)
Scroll-triggered with staggered fade-in and continuous rotation.
===================================================================== --}}
<section class="w-full py-10 transition-colors duration-300"
    :class="$store.ui.darkMode ? 'bg-gray-950' : 'bg-white'">

    <div class="brand-stage mx-auto flex max-w-300 items-center justify-between px-10" id="brandStage">
        <div class="brand-slot" id="brandSlot0"></div>
        <div class="brand-slot" id="brandSlot1"></div>
        <div class="brand-slot" id="brandSlot2"></div>
        <div class="brand-slot" id="brandSlot3"></div>
        <div class="brand-slot" id="brandSlot4"></div>
        <div class="brand-slot" id="brandSlot5"></div>
    </div>

    {{-- Brand Templates --}}
    <template id="tpl-moment">
        <div class="bs-brand bs-moment"><span>Moment</span></div>
    </template>

    <template id="tpl-switcher">
        <div class="bs-brand bs-switcher">
            <div class="bs-icon"><svg viewBox="0 0 100 100" fill="none">
                <circle cx="50" cy="50" r="48" fill="#F5F5F5" stroke="#ddd" stroke-width="1" />
                <path d="M50 15 L80 70 L20 70 Z" fill="#F37021" opacity="0.9" />
                <path d="M50 85 L20 30 L80 30 Z" fill="#F37021" opacity="0.5" />
            </svg></div><span>switcher</span>
        </div>
    </template>

    <template id="tpl-brainfm">
        <div class="bs-brand bs-brainfm">
            <div class="bs-icon"><svg viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="48" fill="#111" />
                <circle cx="50" cy="50" r="30" fill="none" stroke="white" stroke-width="6" />
                <circle cx="50" cy="50" r="12" fill="white" />
                <rect x="46" y="2" width="8" height="18" rx="4" fill="#111" />
                <rect x="46" y="80" width="8" height="18" rx="4" fill="#111" />
            </svg></div><span>brain.fm</span>
        </div>
    </template>

    <template id="tpl-vimeo">
        <div class="bs-brand bs-vimeo"><span>vimeo</span></div>
    </template>

    <template id="tpl-privy">
        <div class="bs-brand bs-privy"><span>privy</span></div>
    </template>

    <template id="tpl-betterhelp">
        <div class="bs-brand bs-betterhelp">
            <div class="bs-icon"><svg viewBox="0 0 60 40" fill="none">
                <ellipse cx="20" cy="20" rx="18" ry="12" fill="#5cb85c" opacity="0.3" />
                <path d="M5 20 Q20 5 35 20 Q20 35 5 20Z" fill="#5cb85c" />
                <path d="M25 20 Q40 8 55 20 Q40 32 25 20Z" fill="#7dc87d" />
            </svg></div>
            <div class="bs-bh-text"><span class="bs-green">better</span>help</div>
        </div>
    </template>

    <template id="tpl-mixmax">
        <div class="bs-brand bs-mixmax">
            <div class="bs-icon"><svg viewBox="0 0 100 100" fill="none">
                <path d="M10 75 L30 25 L50 60 L70 25 L90 75" stroke="#7B2D8E" stroke-width="12" stroke-linecap="round" stroke-linejoin="round" fill="none" />
            </svg></div><span>Mixmax</span>
        </div>
    </template>

    <template id="tpl-clockwise">
        <div class="bs-brand bs-clockwise">
            <div class="bs-icon"><svg viewBox="0 0 100 100" fill="none">
                <polygon points="50,5 61,40 98,40 68,62 79,97 50,75 21,97 32,62 2,40 39,40" fill="#2D8C3C" />
            </svg></div><span>clockwise</span>
        </div>
    </template>

    <template id="tpl-gusto">
        <div class="bs-brand bs-gusto"><span>gusto</span></div>
    </template>

    <template id="tpl-buffer">
        <div class="bs-brand bs-buffer">
            <div class="bs-icon"><svg viewBox="0 0 100 100" fill="none">
                <path d="M50 10 L90 30 L50 50 L10 30 Z" fill="#111" opacity="0.9" />
                <path d="M50 38 L90 58 L50 78 L10 58 Z" fill="#111" opacity="0.6" />
                <path d="M50 58 L90 78 L50 98 L10 78 Z" fill="#111" opacity="0.35" />
            </svg></div><span>Buffer</span>
        </div>
    </template>

    <template id="tpl-litmus">
        <div class="bs-brand bs-litmus">
            <div class="bs-icon"><svg viewBox="0 0 100 100">
                <defs>
                    <linearGradient id="litG" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#f7a623" />
                        <stop offset="25%" stop-color="#e74c3c" />
                        <stop offset="50%" stop-color="#9b59b6" />
                        <stop offset="75%" stop-color="#3498db" />
                        <stop offset="100%" stop-color="#2ecc71" />
                    </linearGradient>
                </defs>
                <circle cx="50" cy="50" r="44" fill="url(#litG)" opacity="0.85" />
                <circle cx="50" cy="50" r="18" fill="#fff" />
            </svg></div><span>litmus</span>
        </div>
    </template>

    <template id="tpl-efficientapp">
        <div class="bs-brand bs-efficientapp">
            <div class="bs-icon"><svg viewBox="0 0 100 100" fill="none">
                <path d="M20 80 L50 20 L80 80" stroke="#E91E63" stroke-width="10" stroke-linecap="round" fill="none" />
                <circle cx="50" cy="55" r="6" fill="#E91E63" />
            </svg></div>
            <div class="bs-ea-text">efficient<span class="bs-light">&nbsp;app</span></div>
        </div>
    </template>
</section>

<style>
    /* ── Brand Stage Layout ── */
    .brand-slot {
        width: 180px;
        height: 50px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bs-brand {
        position: absolute;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        opacity: 0;
        transform: translateY(12px);
        transition: opacity 0.5s ease, transform 0.5s ease;
    }

    .bs-brand.show {
        opacity: 1;
        transform: translateY(0);
    }

    .bs-brand.fade-out {
        opacity: 0;
        transform: translateY(-12px);
    }

    .bs-icon {
        flex-shrink: 0;
    }

    .bs-icon svg {
        width: 100%;
        height: 100%;
    }

    /* ── Individual Brand Styles ── */
    .bs-moment span { font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 16px; letter-spacing: 5px; text-transform: uppercase; }
    .bs-switcher { gap: 6px !important; }
    .bs-switcher .bs-icon { width: 26px; height: 26px; }
    .bs-switcher span { font-family: 'Raleway', sans-serif; font-size: 17px; font-weight: 500; }
    .bs-brainfm .bs-icon { width: 28px; height: 28px; }
    .bs-brainfm span { font-family: 'Montserrat', sans-serif; font-size: 16px; font-weight: 600; }
    .bs-vimeo span { font-family: 'Montserrat', sans-serif; font-size: 24px; font-weight: 700; font-style: italic; color: #1AB7EA; letter-spacing: -0.5px; }
    .bs-privy span { font-family: 'DM Sans', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: -0.3px; }
    .bs-betterhelp .bs-icon { width: 30px; height: 22px; }
    .bs-bh-text { font-family: 'Raleway', sans-serif; font-size: 16px; font-weight: 600; display: flex; align-items: center; }
    .bs-green { color: #5cb85c; font-weight: 700; }
    .bs-mixmax .bs-icon { width: 28px; height: 28px; }
    .bs-mixmax span { font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700; color: #2d1a4a; }
    .bs-clockwise .bs-icon { width: 22px; height: 22px; }
    .bs-clockwise span { font-family: 'Inter', sans-serif; font-size: 17px; font-weight: 700; }
    .bs-gusto span { font-family: 'DM Sans', sans-serif; font-size: 26px; font-weight: 700; color: #f45d48; letter-spacing: -0.5px; }
    .bs-buffer .bs-icon { width: 24px; height: 24px; }
    .bs-buffer span { font-family: 'Space Grotesk', sans-serif; font-size: 20px; font-weight: 700; }
    .bs-litmus .bs-icon { width: 26px; height: 26px; }
    .bs-litmus span { font-family: 'Sora', sans-serif; font-size: 18px; font-weight: 600; }
    .bs-efficientapp .bs-icon { width: 22px; height: 22px; }
    .bs-ea-text { font-family: 'Inter', sans-serif; font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 4px; }
    .bs-light { font-weight: 400; }

    /* Dark-mode text colors (applied via .dark on <html>) */
    .dark .bs-moment span,
    .dark .bs-switcher span,
    .dark .bs-brainfm span,
    .dark .bs-privy span,
    .dark .bs-bh-text,
    .dark .bs-clockwise span,
    .dark .bs-buffer span,
    .dark .bs-litmus span,
    .dark .bs-ea-text { color: #e5e5e5; }

    .dark .bs-light { color: #999; }

    /* Responsive: hide outer slots on smaller screens */
    @media (max-width: 860px) {
        .brand-slot:first-child,
        .brand-slot:last-child { display: none; }
        .brand-stage { justify-content: center; gap: 12px; }
    }

    @media (max-width: 640px) {
        .brand-slot { width: 140px; }
    }
</style>

<script>
    (function () {
        var BRANDS = [
            'moment', 'switcher', 'brainfm', 'vimeo', 'privy', 'betterhelp',
            'mixmax', 'clockwise', 'gusto', 'buffer', 'litmus', 'efficientapp'
        ];
        var SLOT_COUNT = 6;
        var FADE_IN_STAGGER = 200;
        var PAUSE_AFTER_SIX = 2000;
        var CYCLE_INTERVAL = 10;
        var FADE_MS = 180;

        var slots = [];
        for (var i = 0; i < SLOT_COUNT; i++) {
            slots.push(document.getElementById('brandSlot' + i));
        }

        var showing = BRANDS.slice(0, 6);
        var bench = BRANDS.slice(6);
        var swapCount = 0;
        var cycleSlot = 5;
        var started = false;

        function createBrand(key) {
            var tpl = document.getElementById('tpl-' + key);
            return tpl.content.firstElementChild.cloneNode(true);
        }

        function placeBrand(slotIndex, key) {
            var slot = slots[slotIndex];
            slot.innerHTML = '';
            var el = createBrand(key);
            slot.appendChild(el);
            return el;
        }

        function revealBrands() {
            var brandEls = slots.map(function (s) { return s.querySelector('.bs-brand'); });
            brandEls.reverse().forEach(function (el, i) {
                setTimeout(function () { el.classList.add('show'); }, i * FADE_IN_STAGGER);
            });
            setTimeout(cycle, SLOT_COUNT * FADE_IN_STAGGER + PAUSE_AFTER_SIX);
        }

        function cycle() {
            var slot = slots[cycleSlot];
            var current = slot.querySelector('.bs-brand');
            var nextKey = bench.shift();

            current.classList.remove('show');
            current.classList.add('fade-out');

            setTimeout(function () {
                bench.push(showing[cycleSlot]);
                var el = placeBrand(cycleSlot, nextKey);
                showing[cycleSlot] = nextKey;

                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        el.classList.add('show');
                    });
                });

                cycleSlot = (cycleSlot - 1 + SLOT_COUNT) % SLOT_COUNT;
                swapCount++;

                if (swapCount >= SLOT_COUNT) {
                    swapCount = 0;
                    setTimeout(cycle, PAUSE_AFTER_SIX);
                } else {
                    setTimeout(cycle, CYCLE_INTERVAL);
                }
            }, FADE_MS);
        }

        function init() {
            showing.forEach(function (key, i) { placeBrand(i, key); });

            var observer = new IntersectionObserver(function (entries) {
                if (entries[0].isIntersecting && !started) {
                    started = true;
                    observer.disconnect();
                    revealBrands();
                }
            }, { threshold: 0.3 });

            observer.observe(document.getElementById('brandStage'));
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>
