<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';

$solarItems = $solar['items'] ?? [];
$solarTotal = (int)($solar['total'] ?? 0);
$solarTerm = $solar['term'] ?? null;
$studentImage = $_SESSION['USER']->profileimage ?? $CFG->siteiconurl;
$takenCategories = count($solarItems);
$categoryMax = 0;
$dominantCategory = '-';
$uniqueTimes = [];
foreach ($solarItems as $it) {
    $cnt = (int)($it['count'] ?? 0);
    if ($cnt > $categoryMax) {
        $categoryMax = $cnt;
        $dominantCategory = (string)($it['category'] ?? '-');
    }
    foreach (($it['classes'] ?? []) as $cls) {
        foreach (explode(',', (string)($cls['time'] ?? '')) as $t) {
            $t = trim($t);
            if ($t !== '') $uniqueTimes[$t] = true;
        }
    }
}
$uniqueTimesCount = count($uniqueTimes);
$avgPerCategory = $takenCategories > 0 ? round($solarTotal / $takenCategories, 1) : 0;
?>

<div class="max-w-7xl mx-auto px-4 py-8 sm:py-10">
    <section class="nebula-card rounded-3xl p-5 sm:p-7 mb-6 overflow-hidden">
        <div class="relative z-10 flex flex-col gap-4">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-cyan-300/40 bg-cyan-400/10 px-3 py-1 text-xs text-cyan-100 mb-3">
                    نمای منظومه دانش‌آموز
                </div>
                <h2 class="text-2xl sm:text-4xl font-black text-white leading-tight">منظومه دروس من</h2>
                <p class="text-sm text-slate-200 mt-2">
                    <?= !empty($solarTerm['name']) ? ('ترم: ' . htmlspecialchars($solarTerm['name'])) : 'ترمی برای نمایش پیدا نشد' ?>
                </p>
            </div>
            <div class="relative z-10 mt-1 grid grid-cols-2 lg:grid-cols-5 gap-3 text-sm">
                <div class="metric-card"><span>کلاس‌های اخذشده</span><strong><?= $solarTotal ?></strong></div>
                <div class="metric-card"><span>دسته‌های انتخاب‌شده</span><strong><?= $takenCategories ?></strong></div>
                <div class="metric-card"><span>میانگین هر دسته</span><strong><?= $avgPerCategory ?></strong></div>
                <div class="metric-card"><span>زنگ‌های فعال</span><strong><?= $uniqueTimesCount ?></strong></div>
                <div class="metric-card"><span>دسته غالب</span><strong><?= htmlspecialchars($dominantCategory) ?></strong></div>
            </div>
        </div>
        <div class="nebula-glow"></div>
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <section class="xl:col-span-2 rounded-3xl glass-card p-4 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h3 class="text-base sm:text-lg font-extrabold text-slate-700">نقشه منظومه کلاس‌ها</h3>
                <div class="flex items-center flex-wrap gap-2">
                    <button id="zoomIn" type="button" class="px-3 py-1 rounded-lg bg-white text-slate-700 text-xs border border-slate-200 hover:bg-slate-50">+</button>
                    <button id="zoomOut" type="button" class="px-3 py-1 rounded-lg bg-white text-slate-700 text-xs border border-slate-200 hover:bg-slate-50">-</button>
                    <button id="zoomReset" type="button" class="px-3 py-1 rounded-lg bg-white text-slate-700 text-xs border border-slate-200 hover:bg-slate-50">۱:۱</button>
                    <button id="toggleMotion" type="button" class="px-3 py-1 rounded-lg bg-slate-700 text-white text-xs hover:bg-slate-800">توقف حرکت</button>
                </div>
            </div>

            <div id="solarWrap" class="solar-wrap" aria-label="منظومه دسته‌بندی کلاس‌ها">
                <div id="stars" class="stars"></div>
                <div class="sun" title="تصویر دانش‌آموز">
                    <img src="<?= htmlspecialchars($studentImage) ?>" alt="Student" loading="lazy">
                </div>
                <div id="solarSystem" class="solar-system"></div>
                <div id="moonTooltip" class="moon-tooltip hidden"></div>
            </div>
        </section>

        <section class="rounded-3xl glass-card p-4 sm:p-6">
            <h3 class="text-lg font-extrabold mb-3">تحلیل دسته‌بندی‌ها</h3>
            <p class="text-xs text-slate-500 mb-3">
                این نوار رنگی سهم هر دسته از تعداد کلاس‌های گرفته‌شده را نشان می‌دهد: سمت قرمز کمتر، سمت سبز بیشتر.
            </p>

            <div id="selectedPlanetPanel" class="mb-3 rounded-2xl border border-sky-200 bg-sky-50 px-3 py-3 text-xs text-sky-800">
                برای مشاهده کلاس‌های یک دسته، روی سیاره آن کلیک کنید.
            </div>

            <?php if (empty($solarItems)): ?>
                <div class="text-sm text-slate-500">هنوز کلاسی اخذ نشده است.</div>
            <?php else: ?>
                <div class="space-y-3 max-h-[560px] overflow-auto pr-1">
                    <?php $maxCount = max(array_map(fn($i) => (int)($i['count'] ?? 0), $solarItems)); ?>
                    <?php foreach ($solarItems as $item): ?>
                        <?php
                            $count = (int)($item['count'] ?? 0);
                            $ratio = $solarTotal > 0 ? (int)round(($count / $solarTotal) * 100) : 0;
                            $hue = (int)round(($ratio / 100) * 120); // 0=red, 120=green
                            $barColorStart = "hsl($hue, 82%, 45%)";
                            $barColorEnd = "hsl($hue, 82%, 38%)";
                            $names = implode('، ', array_map(fn($c) => (string)($c['course_name'] ?? ''), $item['classes'] ?? []));
                        ?>
                        <article class="rounded-2xl border border-slate-200 bg-white/70 px-3 py-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-slate-700"><?= htmlspecialchars($item['category']) ?></span>
                                <span class="text-xs rounded-full bg-slate-100 text-slate-700 px-2 py-1"><?= $count ?> کلاس</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full" style="width: <?= $ratio ?>%; background: linear-gradient(90deg, <?= $barColorStart ?>, <?= $barColorEnd ?>);"></div>
                            </div>
                            <div class="mt-2 text-xs text-slate-500 leading-6"><?= htmlspecialchars($names) ?></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<style>
.nebula-card {
    position: relative;
    border: 1px solid rgba(148, 163, 184, .28);
    background:
        radial-gradient(circle at 8% 15%, rgba(14, 116, 144, .35), transparent 36%),
        radial-gradient(circle at 78% 88%, rgba(14, 165, 164, .28), transparent 42%),
        linear-gradient(135deg, #0b1024 0%, #12233f 55%, #0f172a 100%);
}
.nebula-glow {
    position: absolute;
    inset: 0;
    pointer-events: none;
    opacity: .5;
    background-image: radial-gradient(rgba(255,255,255,.35) 1px, transparent 1px);
    background-size: 26px 26px;
}
.metric-card {
    display: flex;
    flex-direction: column;
    gap: 6px;
    border-radius: 14px;
    border: 1px solid rgba(226,232,240,.22);
    background: rgba(255,255,255,.08);
    padding: 10px 12px;
    color: #e2e8f0;
}
.metric-card strong {
    color: #f8fafc;
    font-size: 15px;
    font-weight: 800;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.solar-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    min-height: 420px;
    border-radius: 24px;
    overflow: hidden;
    perspective: 900px;
    background:
        radial-gradient(circle at 20% 20%, rgba(56, 189, 248, 0.14), transparent 36%),
        radial-gradient(circle at 76% 78%, rgba(45, 212, 191, 0.12), transparent 42%),
        radial-gradient(circle at center, #0b1730 0%, #050913 68%, #02040a 100%);
}
.stars {
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.star {
    position: absolute;
    width: 2px;
    height: 2px;
    border-radius: 999px;
    background: rgba(255,255,255,.8);
    animation: twinkle linear infinite;
}
@keyframes twinkle {
    0%, 100% { opacity: .25; transform: scale(1); }
    50% { opacity: .95; transform: scale(1.5); }
}
.solar-system {
    position: absolute;
    inset: 0;
    transform-origin: center center;
    transition: transform .35s ease;
    will-change: transform;
}
.sun {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 80px;
    height: 80px;
    transform: translate(-50%, -50%);
    border-radius: 999px;
    border: 2px solid rgba(255,255,255,.85);
    box-shadow: 0 0 24px rgba(56, 189, 248, .45), 0 0 64px rgba(14, 116, 144, .35);
    z-index: 12;
    pointer-events: none;
    overflow: hidden;
}
.sun img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.orbit {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border: 1px dashed rgba(255,255,255,.12);
    border-radius: 999px;
    animation: orbit-spin linear infinite;
}
.orbit.is-focused { border-color: rgba(125, 211, 252, .85); }
@keyframes orbit-spin {
    from { transform: translate(-50%, -50%) rotate(0deg); }
    to { transform: translate(-50%, -50%) rotate(360deg); }
}
.planet {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    border-radius: 999px;
    cursor: pointer;
    transition: transform .24s ease, box-shadow .24s ease;
    box-shadow: inset -3px -3px 8px rgba(0,0,0,.35), 0 0 12px rgba(255,255,255,.2);
}
.planet:hover { box-shadow: inset -3px -3px 8px rgba(0,0,0,.35), 0 0 24px rgba(255,255,255,.45); }
.planet.is-selected {
    transform: translateX(-50%) scale(1.28);
    z-index: 20;
    box-shadow: inset -4px -4px 9px rgba(0,0,0,.32), 0 0 38px rgba(255,255,255,.65);
}
.planet-core {
    position: absolute;
    inset: 0;
    border-radius: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 8px;
    pointer-events: none;
    animation-name: core-counter;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
}
@keyframes core-counter {
    from { transform: rotate(0deg); }
    to { transform: rotate(-360deg); }
}
.planet-text {
    font-size: 10px;
    line-height: 1.35;
    font-weight: 800;
    color: #f8fafc;
    text-shadow: 0 1px 5px rgba(2, 6, 23, .7);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.planet-progress-ring {
    position: absolute;
    inset: -2px;
    border-radius: 50%;
    pointer-events: none;
    background: conic-gradient(var(--ring-color, #22c55e) var(--ring-p, 0deg), rgba(15, 23, 42, .22) 0deg);
    -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 3.6px), #000 calc(100% - 2.6px));
    mask: radial-gradient(farthest-side, transparent calc(100% - 3.6px), #000 calc(100% - 2.6px));
}
.moon-orbit {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border: 1px dashed rgba(255,255,255,.22);
    border-radius: 999px;
    animation: orbit-spin linear infinite;
    pointer-events: none;
}
.moon {
    position: absolute;
    top: -5px;
    left: 50%;
    transform: translateX(-50%);
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #f1f5f9;
    box-shadow: 0 0 8px rgba(241,245,249,.85);
    cursor: pointer;
    transition: transform .2s ease;
    pointer-events: auto;
}
.planet.is-selected .moon {
    width: 10px;
    height: 10px;
    top: -5px;
    box-shadow: 0 0 12px rgba(191, 219, 254, .95);
}
.moon:hover { transform: translateX(-50%) scale(1.15); }
.moon-tooltip {
    position: absolute;
    z-index: 30;
    min-width: 180px;
    max-width: 300px;
    background: rgba(15, 23, 42, .95);
    color: #e2e8f0;
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 12px;
    padding: 10px;
    font-size: 12px;
    line-height: 1.7;
    box-shadow: 0 10px 30px rgba(0,0,0,.35);
}
.class-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    border: 1px solid #bfdbfe;
    background: #eff6ff;
    color: #1e3a8a;
    font-size: 11px;
    padding: 4px 9px;
    cursor: pointer;
}
@media (max-width: 640px) {
    .solar-wrap { min-height: 360px; }
    .planet-text { font-size: 9px; }
}
</style>

<script>
const solarData = <?= json_encode($solarItems, JSON_UNESCAPED_UNICODE) ?>;

const colorPalette = [
    { c1: '#22d3ee', c2: '#0ea5e9' },
    { c1: '#60a5fa', c2: '#2563eb' },
    { c1: '#34d399', c2: '#059669' },
    { c1: '#a3e635', c2: '#65a30d' },
    { c1: '#fbbf24', c2: '#d97706' },
    { c1: '#fb7185', c2: '#e11d48' },
    { c1: '#f472b6', c2: '#db2777' },
    { c1: '#c084fc', c2: '#7c3aed' },
    { c1: '#a78bfa', c2: '#5b21b6' },
    { c1: '#38bdf8', c2: '#0369a1' },
    { c1: '#2dd4bf', c2: '#0f766e' },
    { c1: '#f59e0b', c2: '#b45309' }
];

let motionPaused = false;
let speedFactor = 0.72;
let zoomLevel = 1;
let focusedPlanetEl = null;
let focusedOrbitEl = null;
let selectedItem = null;
let renderTiltRaf = null;
let tooltipPinned = false;

function shuffled(arr) {
    return [...arr].sort(() => Math.random() - 0.5);
}

function createStars() {
    const starsRoot = document.getElementById('stars');
    if (!starsRoot) return;
    const count = 110;
    for (let i = 0; i < count; i++) {
        const s = document.createElement('span');
        s.className = 'star';
        s.style.left = `${Math.random() * 100}%`;
        s.style.top = `${Math.random() * 100}%`;
        s.style.opacity = `${0.2 + Math.random() * 0.8}`;
        const size = Math.random() > 0.88 ? 3 : 2;
        s.style.width = `${size}px`;
        s.style.height = `${size}px`;
        s.style.animationDuration = `${2 + Math.random() * 4}s`;
        starsRoot.appendChild(s);
    }
}

function escapeHtml(str) {
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function tooltipContent(cls, category) {
    return `
        <div class="font-bold text-cyan-300">${escapeHtml(cls.course_name || 'کلاس')}</div>
    `;
}

function applyMotion() {
    const animated = document.querySelectorAll('.orbit, .moon-orbit, .planet-core');
    animated.forEach((el) => {
        el.style.animationPlayState = motionPaused ? 'paused' : 'running';
        const base = Number(el.dataset.baseDuration || 40);
        el.style.animationDuration = `${Math.max(2, base / speedFactor)}s`;
    });
    // Keep selected planet and its moons frozen for easier interaction.
    pauseFocusedMotion();
}

function applyZoom() {
    const root = document.getElementById('solarSystem');
    if (!root) return;
    root.style.transform = `scale(${zoomLevel})`;
}

function renderSelectedPanel(item) {
    const panel = document.getElementById('selectedPlanetPanel');
    if (!panel) return;

    if (!item) {
        panel.innerHTML = 'برای مشاهده کلاس‌های یک دسته، روی سیاره آن کلیک کنید.';
        return;
    }

    const classes = Array.isArray(item.classes) ? item.classes : [];
    const classHtml = classes.map((cls, idx) => {
        const name = escapeHtml(cls.course_name || 'کلاس');
        return `<button type="button" class="class-chip" data-class-idx="${idx}"><span>${name}</span></button>`;
    }).join(' ');

    panel.innerHTML = `
        <div class="font-bold mb-2">${escapeHtml(item.category || 'بدون دسته‌بندی')}</div>
        <div class="text-slate-700 mb-2">تعداد کلاس‌ها: <strong>${Number(item.count || 0)}</strong></div>
        <div class="flex flex-wrap gap-2">${classHtml || '<span class="text-slate-500">کلاسی ندارد</span>'}</div>
    `;
}

function clearFocusedPlanet() {
    if (!focusedPlanetEl) return;
    focusedPlanetEl.classList.remove('is-selected');
    const prevOrbit = focusedPlanetEl.closest('.orbit');
    if (prevOrbit) prevOrbit.classList.remove('is-focused');
    focusedPlanetEl = null;
    focusedOrbitEl = null;
    selectedItem = null;
    renderSelectedPanel(null);
    applyMotion();
}

function pauseFocusedMotion() {
    if (!focusedOrbitEl) return;
    focusedOrbitEl.style.animationPlayState = 'paused';
    const movingChildren = focusedOrbitEl.querySelectorAll('.moon-orbit, .planet-core');
    movingChildren.forEach((el) => {
        el.style.animationPlayState = 'paused';
    });
}

function setFocusedPlanet(planetEl, orbitEl, item) {
    if (focusedPlanetEl && focusedPlanetEl !== planetEl) {
        focusedPlanetEl.classList.remove('is-selected');
        const prevOrbit = focusedPlanetEl.closest('.orbit');
        if (prevOrbit) prevOrbit.classList.remove('is-focused');
    }

    if (focusedPlanetEl === planetEl) {
        clearFocusedPlanet();
        return;
    }

    focusedPlanetEl = planetEl;
    focusedOrbitEl = orbitEl || null;
    selectedItem = item;
    planetEl.classList.add('is-selected');
    if (orbitEl) orbitEl.classList.add('is-focused');
    renderSelectedPanel(item);
    pauseFocusedMotion();
}

(function renderSolar() {
    const root = document.getElementById('solarSystem');
    const wrap = document.getElementById('solarWrap');
    const tooltip = document.getElementById('moonTooltip');
    const toggleMotion = document.getElementById('toggleMotion');
    const zoomIn = document.getElementById('zoomIn');
    const zoomOut = document.getElementById('zoomOut');
    const zoomReset = document.getElementById('zoomReset');
    if (!root || !wrap || !tooltip) return;

    createStars();

    if (!Array.isArray(solarData) || solarData.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'absolute inset-0 flex items-center justify-center text-slate-300 text-sm';
        empty.textContent = 'هنوز داده‌ای برای منظومه وجود ندارد';
        root.appendChild(empty);
        return;
    }

    let randomColors = shuffled(colorPalette);
    const nextColor = () => {
        if (randomColors.length === 0) randomColors = shuffled(colorPalette);
        return randomColors.pop();
    };

    const maxCount = Math.max(...solarData.map(x => Number(x.count || 0)), 1);
    const totalCount = Math.max(1, solarData.reduce((s, x) => s + Number(x.count || 0), 0));
    const total = solarData.length;

    solarData.forEach((item, idx) => {
        const theme = nextColor();

        const orbit = document.createElement('div');
        orbit.className = 'orbit';
        orbit.style.zIndex = String(1000 - idx);

        const orbitStart = 250;
        const orbitStep = total > 1 ? (300 / (total - 1)) : 0;
        const orbitSize = orbitStart + (idx * orbitStep);
        orbit.style.width = `${orbitSize}px`;
        orbit.style.height = `${orbitSize}px`;
        orbit.dataset.baseDuration = String(94 + idx * 28);
        orbit.style.animationDuration = `${orbit.dataset.baseDuration}s`;

        const isReverse = idx % 2 !== 0;
        orbit.style.animationDirection = isReverse ? 'reverse' : 'normal';
        orbit.style.animationDelay = `-${(Math.random() * Number(orbit.dataset.baseDuration)).toFixed(2)}s`;

        const planet = document.createElement('div');
        planet.className = 'planet';
        planet.setAttribute('role', 'button');
        planet.setAttribute('tabindex', '0');
        planet.setAttribute('aria-label', `دسته ${item.category || 'بدون نام'} با ${Number(item.count || 0)} کلاس`);

        const count = Number(item.count || 0);
        const planetSize = 44 + Math.round((count / maxCount) * 20);
        planet.style.width = `${planetSize}px`;
        planet.style.height = `${planetSize}px`;
        planet.style.background = `radial-gradient(circle at 30% 30%, ${theme.c1}, ${theme.c2})`;

        const ratio = Math.max(0, Math.min(1, count / totalCount));
        const hue = Math.round(ratio * 120);
        const ring = document.createElement('div');
        ring.className = 'planet-progress-ring';
        ring.style.setProperty('--ring-p', `${Math.round(ratio * 360)}deg`);
        ring.style.setProperty('--ring-color', `hsl(${hue}, 82%, 48%)`);
        planet.appendChild(ring);

        const core = document.createElement('div');
        core.className = 'planet-core';
        core.dataset.baseDuration = orbit.dataset.baseDuration;
        core.style.animationDuration = `${core.dataset.baseDuration}s`;
        // Keep text visually upright: core must rotate opposite sign to orbit.
        // With core keyframes (0 -> -360), using the SAME direction as orbit cancels correctly.
        core.style.animationDirection = isReverse ? 'reverse' : 'normal';
        core.style.animationDelay = orbit.style.animationDelay;

        const text = document.createElement('div');
        text.className = 'planet-text';
        text.textContent = item.category || 'بدون دسته';
        core.appendChild(text);

        planet.appendChild(core);

        const focusPlanet = (ev) => {
            ev.stopPropagation();
            setFocusedPlanet(planet, orbit, item);
        };
        planet.addEventListener('click', focusPlanet);
        planet.addEventListener('keydown', (ev) => {
            if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                focusPlanet(ev);
            }
        });

        const classes = Array.isArray(item.classes) ? item.classes : [];
        classes.forEach((cls, mIdx) => {
            const moonOrbit = document.createElement('div');
            moonOrbit.className = 'moon-orbit';
            const moonOrbitSize = planetSize + 16 + (mIdx * 12);
            moonOrbit.style.width = `${moonOrbitSize}px`;
            moonOrbit.style.height = `${moonOrbitSize}px`;
            moonOrbit.dataset.baseDuration = String(13 + mIdx * 5);
            moonOrbit.style.animationDuration = `${moonOrbit.dataset.baseDuration}s`;
            moonOrbit.style.animationDirection = mIdx % 2 === 0 ? 'normal' : 'reverse';
            moonOrbit.style.animationDelay = `-${(Math.random() * Number(moonOrbit.dataset.baseDuration)).toFixed(2)}s`;

            const moon = document.createElement('div');
            moon.className = 'moon';
            moon.setAttribute('role', 'button');
            moon.setAttribute('tabindex', '0');
            moon.setAttribute('aria-label', `کلاس ${cls.course_name || ''}`);

            const showMoonTooltip = () => {
                tooltip.innerHTML = tooltipContent(cls, item.category || 'بدون دسته‌بندی');
                tooltip.classList.remove('hidden');
            };
            const moveMoonTooltip = (clientX, clientY) => {
                const rect = wrap.getBoundingClientRect();
                const x = Math.max(8, Math.min(rect.width - 290, clientX - rect.left + 14));
                const y = Math.max(8, Math.min(rect.height - 130, clientY - rect.top + 14));
                tooltip.style.left = `${x}px`;
                tooltip.style.top = `${y}px`;
            };

            moon.addEventListener('mouseenter', () => {
                if (tooltipPinned) return;
                showMoonTooltip();
            });
            moon.addEventListener('mousemove', (ev) => {
                if (tooltipPinned) return;
                moveMoonTooltip(ev.clientX, ev.clientY);
            });
            moon.addEventListener('mouseleave', () => {
                if (tooltipPinned) return;
                tooltip.classList.add('hidden');
            });
            moon.addEventListener('click', (ev) => {
                ev.stopPropagation();
                tooltipPinned = !tooltipPinned;
                showMoonTooltip();
                const r = moon.getBoundingClientRect();
                moveMoonTooltip(r.left + (r.width / 2), r.top + (r.height / 2));
                if (!tooltipPinned) tooltip.classList.add('hidden');
            });
            moon.addEventListener('keydown', (ev) => {
                if (ev.key === 'Enter' || ev.key === ' ') {
                    ev.preventDefault();
                    moon.click();
                }
            });

            moonOrbit.appendChild(moon);
            planet.appendChild(moonOrbit);
        });

        orbit.appendChild(planet);
        root.appendChild(orbit);
    });

    document.addEventListener('click', (ev) => {
        if (!wrap.contains(ev.target)) {
            tooltipPinned = false;
            tooltip.classList.add('hidden');
        }
    });

    let tiltX = 0;
    let tiltY = 0;
    const applyTilt = () => {
        root.style.transform = `scale(${zoomLevel}) rotateX(${tiltY}deg) rotateY(${tiltX}deg)`;
        renderTiltRaf = null;
    };

    wrap.addEventListener('mousemove', (ev) => {
        const rect = wrap.getBoundingClientRect();
        tiltX = (((ev.clientX - rect.left) / rect.width) - 0.5) * 7;
        tiltY = -((((ev.clientY - rect.top) / rect.height) - 0.5) * 7);
        if (!renderTiltRaf) renderTiltRaf = requestAnimationFrame(applyTilt);
    });
    wrap.addEventListener('mouseleave', () => {
        tiltX = 0;
        tiltY = 0;
        if (!renderTiltRaf) renderTiltRaf = requestAnimationFrame(applyTilt);
    });

    applyMotion();
    applyZoom();

    if (toggleMotion) {
        toggleMotion.addEventListener('click', () => {
            motionPaused = !motionPaused;
            toggleMotion.textContent = motionPaused ? 'ادامه حرکت' : 'توقف حرکت';
            applyMotion();
        });
    }

    if (zoomIn) {
        zoomIn.addEventListener('click', () => {
            zoomLevel = Math.min(1.8, Number((zoomLevel + 0.1).toFixed(2)));
            applyZoom();
        });
    }
    if (zoomOut) {
        zoomOut.addEventListener('click', () => {
            zoomLevel = Math.max(0.7, Number((zoomLevel - 0.1).toFixed(2)));
            applyZoom();
        });
    }
    if (zoomReset) {
        zoomReset.addEventListener('click', () => {
            zoomLevel = 1;
            applyZoom();
        });
    }

    document.addEventListener('keydown', (ev) => {
        if (ev.key === 'Escape') {
            tooltipPinned = false;
            tooltip.classList.add('hidden');
            clearFocusedPlanet();
        }
    });

    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        motionPaused = true;
        toggleMotion.textContent = 'ادامه حرکت';
        applyMotion();
    }

    const selectedPanel = document.getElementById('selectedPlanetPanel');
    if (selectedPanel) {
        selectedPanel.addEventListener('click', (ev) => {
            const btn = ev.target.closest('[data-class-idx]');
            if (!btn || !selectedItem) return;
            const idx = Number(btn.getAttribute('data-class-idx'));
            const cls = (selectedItem.classes || [])[idx];
            if (!cls) return;
            tooltipPinned = true;
            tooltip.innerHTML = tooltipContent(cls, selectedItem.category || 'بدون دسته‌بندی');
            tooltip.classList.remove('hidden');
            tooltip.style.left = '10px';
            tooltip.style.top = '10px';
        });
    }
})();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
