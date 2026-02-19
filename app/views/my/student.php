<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
require_once __DIR__ . '/../layouts/header.php';

$solarItems = $solar['items'] ?? [];
$solarTotal = (int)($solar['total'] ?? 0);
$solarTerm = $solar['term'] ?? null;
?>

<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-3xl font-extrabold">منظومه دروس من</h2>
            <p class="text-sm text-slate-600 mt-1">
                <?= !empty($solarTerm['name']) ? ('ترم: ' . htmlspecialchars($solarTerm['name'])) : 'ترمی برای نمایش پیدا نشد' ?>
            </p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white/80 px-4 py-2 text-sm text-slate-700">
            مجموع کلاس‌های اخذشده: <span class="font-extrabold"><?= $solarTotal ?></span>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        <div class="xl:col-span-2 rounded-3xl glass-card p-4 sm:p-6">
            <div id="solarWrap" class="solar-wrap">
                <div class="sun" title="مرکز"></div>
                <div id="solarSystem" class="solar-system"></div>
            </div>
        </div>

        <div class="rounded-3xl glass-card p-4 sm:p-6">
            <h3 class="text-lg font-extrabold mb-3">تحلیل دسته‌بندی‌ها</h3>
            <?php if (empty($solarItems)): ?>
                <div class="text-sm text-slate-500">هنوز کلاس اخذ نشده است.</div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($solarItems as $item): ?>
                        <div class="rounded-2xl border border-slate-200 bg-white/70 px-3 py-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-slate-700"><?= htmlspecialchars($item['category']) ?></span>
                                <span class="text-xs rounded-full bg-teal-100 text-teal-700 px-2 py-1">
                                    <?= (int)$item['count'] ?> کلاس
                                </span>
                            </div>
                            <div class="mt-2 text-xs text-slate-500">
                                <?= htmlspecialchars(implode('، ', array_map(fn($c) => (string)($c['course_name'] ?? ''), $item['classes'] ?? []))) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.solar-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    min-height: 420px;
    border-radius: 24px;
    overflow: hidden;
    background:
        radial-gradient(circle at 30% 25%, rgba(56, 189, 248, 0.18), transparent 40%),
        radial-gradient(circle at 70% 75%, rgba(45, 212, 191, 0.14), transparent 42%),
        radial-gradient(circle at center, #0d1b2a 0%, #040814 70%);
}
.solar-system {
    position: absolute;
    inset: 0;
}
.sun {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 26px;
    height: 26px;
    transform: translate(-50%, -50%);
    border-radius: 999px;
    background: radial-gradient(circle, #fff9c4 0%, #f59e0b 65%, #b45309 100%);
    box-shadow: 0 0 24px rgba(245, 158, 11, 0.9), 0 0 60px rgba(245, 158, 11, 0.35);
    z-index: 5;
}
.orbit {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border: 1px dashed rgba(255, 255, 255, 0.14);
    border-radius: 999px;
    animation: spin linear infinite;
}
.planet {
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    border-radius: 999px;
    box-shadow: inset -3px -3px 8px rgba(0,0,0,.45), 0 0 8px rgba(255,255,255,.3);
}
.planet-label {
    position: absolute;
    top: 14px;
    right: -14px;
    transform: translate(100%, 0);
    background: rgba(15, 23, 42, 0.85);
    color: #e2e8f0;
    font-size: 11px;
    white-space: nowrap;
    padding: 3px 8px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.35);
}
.moon-orbit {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border: 1px dashed rgba(255, 255, 255, 0.2);
    border-radius: 999px;
    animation: spin linear infinite;
}
.moon {
    position: absolute;
    top: -3px;
    left: 50%;
    transform: translateX(-50%);
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: #e2e8f0;
    box-shadow: 0 0 6px rgba(226, 232, 240, 0.8);
}
@keyframes spin {
    from { transform: translate(-50%, -50%) rotate(0deg); }
    to { transform: translate(-50%, -50%) rotate(360deg); }
}
</style>

<script>
const solarData = <?= json_encode($solarItems, JSON_UNESCAPED_UNICODE) ?>;

(function renderSolar() {
    const root = document.getElementById('solarSystem');
    if (!root) return;

    if (!Array.isArray(solarData) || solarData.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'absolute inset-0 flex items-center justify-center text-slate-300 text-sm';
        empty.textContent = 'هنوز داده‌ای برای منظومه وجود ندارد';
        root.appendChild(empty);
        return;
    }

    const colors = [
        ['#7dd3fc', '#0ea5e9'],
        ['#86efac', '#16a34a'],
        ['#fcd34d', '#f59e0b'],
        ['#f9a8d4', '#ec4899'],
        ['#c4b5fd', '#8b5cf6'],
        ['#fdba74', '#ea580c'],
        ['#67e8f9', '#0891b2'],
    ];

    const maxCount = Math.max(...solarData.map(x => Number(x.count || 0)), 1);
    const total = solarData.length;

    solarData.forEach((item, idx) => {
        const orbit = document.createElement('div');
        orbit.className = 'orbit';

        const orbitSize = 130 + (idx * (360 / Math.max(total, 1) / 2.2));
        orbit.style.width = `${orbitSize}px`;
        orbit.style.height = `${orbitSize}px`;
        orbit.style.animationDuration = `${70 + idx * 28}s`;
        orbit.style.animationDirection = idx % 2 === 0 ? 'normal' : 'reverse';

        const planet = document.createElement('div');
        planet.className = 'planet';

        const count = Number(item.count || 0);
        const planetSize = 10 + Math.round((count / maxCount) * 16);
        planet.style.width = `${planetSize}px`;
        planet.style.height = `${planetSize}px`;

        const c = colors[idx % colors.length];
        planet.style.background = `radial-gradient(circle at 30% 30%, ${c[0]}, ${c[1]})`;

        const label = document.createElement('div');
        label.className = 'planet-label';
        label.textContent = `${item.category} (${count})`;
        planet.appendChild(label);

        const classes = Array.isArray(item.classes) ? item.classes : [];
        classes.forEach((cls, mIdx) => {
            const moonOrbit = document.createElement('div');
            moonOrbit.className = 'moon-orbit';
            const moonOrbitSize = 24 + (mIdx * 8);
            moonOrbit.style.width = `${moonOrbitSize}px`;
            moonOrbit.style.height = `${moonOrbitSize}px`;
            moonOrbit.style.animationDuration = `${10 + mIdx * 4}s`;
            moonOrbit.style.animationDirection = mIdx % 2 === 0 ? 'normal' : 'reverse';

            const moon = document.createElement('div');
            moon.className = 'moon';
            moon.title = cls.course_name || 'کلاس';
            moonOrbit.appendChild(moon);
            planet.appendChild(moonOrbit);
        });

        orbit.appendChild(planet);
        root.appendChild(orbit);
    });
})();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
