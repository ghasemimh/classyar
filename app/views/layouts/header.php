<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

global $CFG, $MDL, $MSG;
require_once __DIR__ . '/../../models/term.php';
require_once __DIR__ . '/../../services/flash.php';
require_once __DIR__ . '/../../services/csrf.php';

$msg = $msg ?? null;
$subtitle = $subtitle ?? $CFG->sitedescription;

$userRole = $_SESSION['USER']->role ?? 'guest';
$currentUserName = $_SESSION['USER']->fullname ?? 'کاربر مهمان';
$currentUserImage = trim((string)($_SESSION['USER']->profileimage ?? ''));
if ($currentUserImage === '') {
    $currentUserImage = (string)($CFG->assets . '/images/site-icon.png');
}
$logoLightUrl = $CFG->assets . '/images/logo-light.png';
$logoDarkUrl = $CFG->assets . '/images/logo-dark.png';
$siteIconBaseUrl = !empty($CFG->siteiconurl) ? (string)$CFG->siteiconurl : ($CFG->assets . '/images/site-icon.png');
$siteIconVersion = (string)@filemtime(__DIR__ . '/../assets/images/site-icon.png');
if ($siteIconVersion === '' || $siteIconVersion === '0') {
    $siteIconVersion = (string)time();
}
$siteIconUrl = $siteIconBaseUrl . (str_contains($siteIconBaseUrl, '?') ? '&' : '?') . 'v=' . $siteIconVersion;
$siteIconIcoVersion = (string)@filemtime(__DIR__ . '/../assets/images/favicon.ico');
if ($siteIconIcoVersion === '' || $siteIconIcoVersion === '0') {
    $siteIconIcoVersion = $siteIconVersion;
}
$siteIconIcoUrl = $CFG->assets . '/images/favicon.ico?v=' . $siteIconIcoVersion;
$termOptions = [];
$effectiveTerm = null;
$isTermOverridden = false;
$effectiveTermName = null;
$csrfToken = Csrf::token();
$csrfField = Csrf::fieldName();
try {
    $termContext = Term::getContextInfo();
    $effectiveTerm = $termContext['effective_term'] ?? null;
    $isTermOverridden = !empty($termContext['is_overridden']);
    $effectiveTermName = is_array($effectiveTerm) ? (string)($effectiveTerm['name'] ?? '') : null;
    $termOptions = Term::getTerm(mode: 'all') ?: [];
} catch (Throwable $e) {
    $termOptions = [];
    $effectiveTerm = null;
    $isTermOverridden = false;
    $effectiveTermName = null;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($CFG->sitetitle . ' | ' . $subtitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($CFG->sitetitle . ' | ' . $subtitle, ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= htmlspecialchars($CFG->wwwroot, ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:image" content="<?= htmlspecialchars($CFG->assets, ENT_QUOTES, 'UTF-8') ?>/images/og-image.png" />

    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($siteIconIcoUrl, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($siteIconUrl, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= htmlspecialchars($siteIconIcoUrl, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= htmlspecialchars($siteIconUrl, ENT_QUOTES, 'UTF-8') ?>">
    <script>
    (function() {
        function readCookie(name) {
            const key = `${name}=`;
            const parts = document.cookie ? document.cookie.split(';') : [];
            for (let i = 0; i < parts.length; i += 1) {
                const c = parts[i].trim();
                if (c.startsWith(key)) {
                    return decodeURIComponent(c.substring(key.length));
                }
            }
            return '';
        }

        try {
            const cookiePref = readCookie('classyar_theme_mode');
            const storagePref = localStorage.getItem('classyar_theme_mode');
            const pref = (cookiePref || storagePref || 'auto');
            const validPref = ['auto', 'dark', 'light'].includes(pref) ? pref : 'auto';
            const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const resolved = validPref === 'auto' ? (systemDark ? 'dark' : 'light') : validPref;
            document.documentElement.setAttribute('data-theme', resolved);
            document.documentElement.setAttribute('data-theme-mode', validPref);
            localStorage.setItem('classyar_theme_mode', validPref);
        } catch (err) {
            document.documentElement.setAttribute('data-theme', 'light');
            document.documentElement.setAttribute('data-theme-mode', 'auto');
        }
    })();
    </script>
    <script src="<?= $CFG->assets ?>/js/jquery-3.7.1.js"></script>
    <link rel="stylesheet" href="<?= $CFG->assets ?>/css/style.css">
    <link rel="stylesheet" href="<?= $CFG->assets ?>/css/jalalidatepicker.min.css">
    <script src="<?= $CFG->assets ?>/js/tailwindcss.js"></script>
    <script src="<?= $CFG->assets ?>/js/jalalidatepicker.min.js"></script>
<style>
:root {
    --bg: #f8f5f0;
    --bg-soft: #eef6f4;
    --ink: #1f2937;
    --accent: #0f766e;
    --accent-2: #f59e0b;
    --header-bg: rgba(255, 255, 255, 0.70);
    --header-border: rgba(255, 255, 255, 0.60);
    --footer-bg: rgba(255, 255, 255, 0.70);
    --footer-border: rgba(255, 255, 255, 0.60);
    --surface: rgba(255, 255, 255, 0.85);
    --surface-border: rgba(255, 255, 255, 0.60);
    --theme-btn-bg: rgba(255, 255, 255, 0.70);
    --theme-btn-border: rgba(226, 232, 240, 0.90);
    --theme-btn-text: #334155;
    --page-grad-from: #f8f5f0;
    --page-grad-via: #f3f7f5;
    --page-grad-to: #eef6f4;
    --dot-color: rgba(15, 118, 110, 0.15);
    --loader-bg: #f8f5f0;
    --loader-accent: #0f766e;
    --header-offset: 88px;
}
html[data-theme='dark'] {
    --bg: #0b1320;
    --bg-soft: #101a2a;
    --ink: #e5e7eb;
    --header-bg: rgba(12, 20, 33, 0.78);
    --header-border: rgba(100, 116, 139, 0.28);
    --footer-bg: rgba(12, 20, 33, 0.78);
    --footer-border: rgba(100, 116, 139, 0.28);
    --surface: rgba(15, 23, 42, 0.72);
    --surface-border: rgba(100, 116, 139, 0.24);
    --theme-btn-bg: rgba(15, 23, 42, 0.72);
    --theme-btn-border: rgba(100, 116, 139, 0.45);
    --theme-btn-text: #e2e8f0;
    --page-grad-from: #0b1320;
    --page-grad-via: #0f1b2f;
    --page-grad-to: #111827;
    --dot-color: rgba(148, 163, 184, 0.14);
    --loader-bg: #0b1320;
    --loader-accent: #38bdf8;
}
html, body {
    font-family: "Vazirmatn", system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", "Apple Color Emoji", "Segoe UI Emoji";
    color: var(--ink);
    margin: 0;
    padding: 0;
    min-height: 100%;
}
.glass-card {
    background: var(--surface);
    backdrop-filter: blur(20px);
    border: 1px solid var(--surface-border);
    box-shadow: 0 10px 30px rgba(15, 118, 110, 0.08);
}
.loader-wrapper {
    position: fixed;
    inset: 0;
    z-index: 10000;
    background-color: var(--loader-bg);
    display: flex;
    justify-content: center;
    align-items: center;
}
.loader {
    display: inline-block;
    width: 30px;
    height: 30px;
    position: relative;
    border: 4px solid var(--loader-accent);
    animation: loader 2s infinite ease;
}
.loader-inner {
    vertical-align: top;
    display: inline-block;
    width: 100%;
    background-color: var(--loader-accent);
    animation: loader-inner 2s infinite ease-in;
}
@keyframes loader {
    0% { transform: rotate(0deg); }
    25% { transform: rotate(180deg); }
    50% { transform: rotate(180deg); }
    75% { transform: rotate(360deg); }
    100% { transform: rotate(360deg); }
}
@keyframes loader-inner {
    0% { height: 0%; }
    25% { height: 0%; }
    50% { height: 100%; }
    75% { height: 100%; }
    100% { height: 0%; }
}
.page-theme-bg {
    background: linear-gradient(to bottom, var(--page-grad-from), var(--page-grad-via), var(--page-grad-to));
}
.page-theme-dots {
    background-image: radial-gradient(var(--dot-color) 1px, transparent 1px);
    background-size: 24px 24px;
}
.theme-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 12px;
    border: 1px solid var(--theme-btn-border);
    background: var(--theme-btn-bg);
    color: var(--theme-btn-text);
    padding: 8px 10px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
}
.theme-toggle:hover { filter: brightness(0.97); }
.theme-toggle .theme-icon { font-size: 14px; }
.site-logo-light { display: inline-flex; }
.site-logo-dark { display: none; }
html[data-theme='dark'] .site-logo-light { display: none; }
html[data-theme='dark'] .site-logo-dark { display: inline-flex; }
html[data-theme='dark'] body .text-slate-600,
html[data-theme='dark'] body .text-slate-700,
html[data-theme='dark'] body .text-slate-500 { color: #cbd5e1 !important; }
html[data-theme='dark'] body .bg-white,
html[data-theme='dark'] body .bg-white\/70,
html[data-theme='dark'] body .bg-white\/80,
html[data-theme='dark'] body .bg-white\/85 {
    background-color: rgba(15, 23, 42, 0.72) !important;
}
html[data-theme='dark'] body .border-slate-200,
html[data-theme='dark'] body .border-white\/60,
html[data-theme='dark'] body .border-white\/70 {
    border-color: rgba(100, 116, 139, 0.35) !important;
}
html[data-theme='dark'] body input,
html[data-theme='dark'] body select,
html[data-theme='dark'] body textarea {
    background: rgba(15, 23, 42, 0.75);
    color: #e2e8f0;
    border-color: rgba(100, 116, 139, 0.45);
}
</style>
<script>
$(document).ready(function() {
    $(".loader-wrapper").fadeOut("slow");
});

window.CSRF_TOKEN = <?= json_encode($csrfToken) ?>;
window.CSRF_FIELD = <?= json_encode($csrfField) ?>;

(function() {
    function ensureCsrfField(form) {
        const method = String($(form).attr('method') || 'get').toLowerCase();
        if (method !== 'post') return;
        if ($(form).find(`input[name="${window.CSRF_FIELD}"]`).length > 0) return;
        $('<input>', {
            type: 'hidden',
            name: window.CSRF_FIELD,
            value: window.CSRF_TOKEN
        }).appendTo(form);
    }

    $(function() {
        $('form').each(function() { ensureCsrfField(this); });
    });

    $(document).on('submit', 'form', function() {
        ensureCsrfField(this);
    });

    $.ajaxSetup({
        headers: { 'X-CSRF-Token': window.CSRF_TOKEN }
    });
})();

(function() {
    const STORAGE_KEY = 'classyar_theme_mode';
    const COOKIE_KEY = 'classyar_theme_mode';
    const MODES = ['auto', 'dark', 'light'];
    const systemQuery = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    function resolve(mode) {
        if (mode === 'auto') {
            return systemQuery && systemQuery.matches ? 'dark' : 'light';
        }
        return mode;
    }

    function apply(mode) {
        const resolved = resolve(mode);
        document.documentElement.setAttribute('data-theme-mode', mode);
        document.documentElement.setAttribute('data-theme', resolved);
        try { localStorage.setItem(STORAGE_KEY, mode); } catch (err) {}
        try {
            document.cookie = `${COOKIE_KEY}=${encodeURIComponent(mode)}; path=/; max-age=${60 * 60 * 24 * 365}; samesite=lax`;
        } catch (err) {}
        updateButtons(mode, resolved);
    }

    function nextMode(current) {
        const idx = MODES.indexOf(current);
        return MODES[(idx + 1) % MODES.length];
    }

    function modeMeta(mode, resolved) {
        if (mode === 'auto') {
            return {
                icon: '◐',
                text: `خودکار (${resolved === 'dark' ? 'تیره' : 'روشن'})`
            };
        }
        return {
            icon: resolved === 'dark' ? '☾' : '☀',
            text: resolved === 'dark' ? 'تیره' : 'روشن'
        };
    }

    function updateButtons(mode, resolved) {
        const meta = modeMeta(mode, resolved);
        document.querySelectorAll('.theme-toggle').forEach((btn) => {
            btn.setAttribute('data-mode', mode);
            btn.setAttribute('aria-label', `تغییر حالت نمایش (فعلی: ${meta.text})`);
            btn.querySelector('.theme-icon').textContent = meta.icon;
            btn.querySelector('.theme-label').textContent = meta.text;
        });
    }

    function currentMode() {
        return document.documentElement.getAttribute('data-theme-mode') || 'auto';
    }

    document.addEventListener('click', (ev) => {
        const btn = ev.target.closest('.theme-toggle');
        if (!btn) return;
        const next = nextMode(currentMode());
        apply(next);
    });

    if (systemQuery && typeof systemQuery.addEventListener === 'function') {
        systemQuery.addEventListener('change', () => {
            if (currentMode() === 'auto') {
                apply('auto');
            }
        });
    }

    function syncButtonsFromDom() {
        const mode = currentMode();
        const resolved = document.documentElement.getAttribute('data-theme') || resolve(mode);
        updateButtons(mode, resolved);
    }

    syncButtonsFromDom();
    document.addEventListener('DOMContentLoaded', syncButtonsFromDom);
})();

(function() {
    window.classyarConfirm = function(message, options) {
        const opts = options || {};
        const title = String(opts.title || 'تایید عملیات');
        const okText = String(opts.okText || 'تایید');
        const cancelText = String(opts.cancelText || 'انصراف');
        const body = String(message || 'آیا مطمئن هستید؟');

        return new Promise((resolve) => {
            const $modal = $('#globalConfirmModal');
            const $title = $('#globalConfirmTitle');
            const $body = $('#globalConfirmBody');
            const $ok = $('#globalConfirmOk');
            const $cancel = $('#globalConfirmCancel');

            function cleanup(result) {
                $(document).off('keydown.classyarConfirm');
                $ok.off('click.classyarConfirm');
                $cancel.off('click.classyarConfirm');
                $modal.off('click.classyarConfirm');
                $modal.fadeOut(150, function() {
                    $modal.addClass('hidden');
                    resolve(result);
                });
            }

            $title.text(title);
            $body.text(body);
            $ok.text(okText);
            $cancel.text(cancelText);

            $modal.removeClass('hidden').hide().fadeIn(150);

            $ok.on('click.classyarConfirm', function() { cleanup(true); });
            $cancel.on('click.classyarConfirm', function() { cleanup(false); });
            $modal.on('click.classyarConfirm', function(ev) {
                if (ev.target === this) cleanup(false);
            });
            $(document).on('keydown.classyarConfirm', function(ev) {
                if (ev.key === 'Escape') cleanup(false);
            });
        });
    };

    $(document).on('submit', 'form[data-confirm]', function(ev) {
        if (this.dataset.confirmed === '1') {
            this.dataset.confirmed = '';
            return;
        }
        ev.preventDefault();
        const form = this;
        const msg = String($(form).attr('data-confirm') || 'آیا مطمئن هستید؟');
        window.classyarConfirm(msg).then(function(ok) {
            if (!ok) return;
            form.dataset.confirmed = '1';
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });
    });
})();

(function() {
    function showLoader() {
        $(".loader-wrapper").stop(true, true).fadeIn(100);
    }
    function hideLoader() {
        $(".loader-wrapper").stop(true, true).fadeOut(300);
    }
    function shouldSkipByPath(pathname) {
        if (!pathname) return false;
        return pathname.includes('/export') || pathname.endsWith('/csv');
    }

    window.ClassyarLoader = {
        show: showLoader,
        hide: hideLoader
    };

    $(window).on('load', hideLoader);

    $(document).on('click', 'a[href]:not([data-no-loader])', function(ev) {
        if (ev.defaultPrevented) return;
        if (ev.button !== 0) return;
        if (ev.ctrlKey || ev.metaKey || ev.shiftKey || ev.altKey) return;

        const $a = $(this);
        const target = String($a.attr('target') || '').toLowerCase();
        if (target === '_blank') return;
        if ($a.is('[download]')) return;

        const href = String($a.attr('href') || '').trim();
        if (!href || href === '#' || href.startsWith('#')) return;
        if (href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;

        let url;
        try {
            url = new URL(href, location.href);
        } catch (err) {
            return;
        }
        if (url.origin !== location.origin) return;
        if (shouldSkipByPath(url.pathname)) return;
        if (url.pathname === location.pathname && url.search === location.search && url.hash !== '') return;

        showLoader();
    });

    $(document).on('submit', 'form:not([data-no-loader])', function(ev) {
        if (ev.isDefaultPrevented()) return;
        const target = String($(this).attr('target') || '').toLowerCase();
        if (target === '_blank') return;
        if ($(this).is('[data-no-loader], [data-export], [download]')) return;
        showLoader();
    });

})();
</script>
</head>
<body class="text-slate-800">
<div class="fixed inset-0 -z-10">
    <div class="absolute inset-0 page-theme-bg"></div>
    <div class="absolute inset-0 opacity-40 page-theme-dots"></div>
</div>

<div class="loader-wrapper">
    <span class="loader"><span class="loader-inner"></span></span>
</div>

<div id="globalConfirmModal" class="fixed inset-0 hidden z-[10020] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="relative z-10 w-full max-w-md mx-4 rounded-3xl glass-card p-6">
        <h3 id="globalConfirmTitle" class="text-lg font-bold mb-2">تایید عملیات</h3>
        <p id="globalConfirmBody" class="text-sm text-slate-600 mb-5">آیا مطمئن هستید؟</p>
        <div class="flex items-center justify-end gap-2">
            <button id="globalConfirmCancel" type="button" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-800 text-sm font-semibold hover:bg-slate-300 transition">انصراف</button>
            <button id="globalConfirmOk" type="button" class="px-4 py-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 text-white text-sm font-bold hover:opacity-90 transition">تایید</button>
        </div>
    </div>
</div>

<header id="siteHeader" class="fixed top-0 inset-x-0 z-50 backdrop-blur-xl border-b shadow-sm" style="background: var(--header-bg); border-color: var(--header-border);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="<?= $CFG->wwwroot ?>" class="font-extrabold text-xl sm:text-2xl flex items-center gap-3">
            <img src="<?= htmlspecialchars($logoLightUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($CFG->sitename, ENT_QUOTES, 'UTF-8') ?>" class="site-logo-light w-12 h-12 sm:w-14 sm:h-14 rounded-2xl object-cover ring-2 ring-white/70 shadow-md">
            <img src="<?= htmlspecialchars($logoDarkUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($CFG->sitename, ENT_QUOTES, 'UTF-8') ?>" class="site-logo-dark w-12 h-12 sm:w-14 sm:h-14 rounded-2xl object-cover ring-2 ring-white/70 shadow-md">
            <span class="hidden sm:inline-block"><?= htmlspecialchars($CFG->sitename, ENT_QUOTES, 'UTF-8') ?></span>
            <?php if (!empty($effectiveTermName)): ?>
                <span class="hidden lg:inline-flex items-center gap-2 text-xs font-bold px-3 py-1 rounded-full bg-teal-100 text-teal-700 border border-teal-200">
                    <?= $isTermOverridden ? 'ترم کاری' : 'ترم فعال' ?>: <?= htmlspecialchars($effectiveTermName) ?>
                </span>
            <?php endif; ?>
        </a>

        <nav class="hidden md:flex items-center gap-4 text-base font-semibold">
            <?php if ($userRole === 'admin' || $userRole === 'guide'): ?>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/dashboard">داشبورد</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/users">کاربران</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/teacher">معلمان</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/program">چیدمان</a>
                <details class="relative">
                    <summary style="list-style:none;" class="cursor-pointer text-slate-600 hover:text-teal-700 transition inline-flex items-center gap-1">
                        بیشتر
                    </summary>
                    <div class="absolute right-0 top-full mt-2 w-52 rounded-2xl border border-slate-200 bg-white/95 backdrop-blur-xl p-2 shadow-lg z-50 grid gap-1">
                        <a class="rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/category">دسته‌بندی‌ها</a>
                        <a class="rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/room">مکان‌ها</a>
                        <a class="rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/course">دوره‌ها</a>
                        <a class="rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/panel">پنل برگزاری</a>
                        <a class="rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/prints">چاپ لیست‌ها</a>
                        <a class="rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/term">ترم‌ها</a>
                        <a class="rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/sync">همگام‌سازی</a>
                        <a class="rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/enroll/admin">ثبت‌نام</a>
                        <a class="rounded-xl px-3 py-2 text-sm text-slate-700 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/settings">تنظیمات</a>
                    </div>
                </details>
            <?php elseif ($userRole === 'teacher'): ?>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>">پنل معلم</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/prints">چاپ لیست‌ها</a>
            <?php elseif ($userRole === 'student'): ?>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>">منظومه</a>
                <a class="text-slate-600 hover:text-teal-700 transition" href="<?= $CFG->wwwroot ?>/enroll">ثبت‌نام</a>
            <?php else: ?>
                <a class="hover:bg-teal-700 text-white bg-teal-600 rounded-xl px-4 py-2 transition" href="<?= $MDL->wwwroot ?>/login">ورود</a>
            <?php endif; ?>
        </nav>

        <div class="flex items-center gap-3">
            <?php if ($userRole !== 'guest' && !empty($termOptions)): ?>
                <form method="post" action="<?= $CFG->wwwroot ?>/term/context/0" class="hidden xl:flex items-center gap-2">
                    <input type="hidden" name="<?= htmlspecialchars($csrfField, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <select name="term_id"
                            class="rounded-xl border border-slate-200 bg-white/85 px-2 py-1 text-xs font-semibold text-slate-700 min-w-[140px]"
                            onchange="this.form.action='<?= $CFG->wwwroot ?>/term/context/' + (this.value || 0);">
                        <?php foreach ($termOptions as $t): ?>
                            <?php $tid = (int)($t['id'] ?? 0); ?>
                            <option value="<?= $tid ?>" <?= ($tid === (int)($effectiveTerm['id'] ?? 0) ? 'selected' : '') ?>>
                                <?= htmlspecialchars((string)($t['name'] ?? ('ترم #' . $tid)), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="px-3 py-1 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">سوییچ</button>
                </form>
                <?php if ($isTermOverridden): ?>
                    <form method="post" action="<?= $CFG->wwwroot ?>/term/context/reset" class="hidden xl:block">
                        <input type="hidden" name="<?= htmlspecialchars($csrfField, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition">
                            بازگشت
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($userRole !== 'guest'): ?>
                <p class="text-base font-semibold text-slate-700 hidden sm:block"><?= htmlspecialchars($currentUserName) ?></p>
                <img src="<?= htmlspecialchars($currentUserImage) ?>" class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl object-cover hidden sm:block ring-2 ring-white/70 shadow-md" alt="User Profile">
            <?php endif; ?>
            <button type="button" class="theme-toggle" data-mode="auto">
                <span class="theme-icon" aria-hidden="true">◐</span>
                <span class="theme-label">خودکار</span>
            </button>
            <button id="mobileMenuToggle" type="button" class="md:hidden inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/70 px-3 py-2 text-sm">منو</button>
        </div>
    </div>
    <nav id="mobileMenu" class="md:hidden hidden border-t border-white/70 bg-white/85 backdrop-blur-xl px-4 py-3">
        <div class="grid grid-cols-2 gap-2 text-sm font-semibold">
            <?php if ($userRole === 'admin' || $userRole === 'guide'): ?>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/dashboard">داشبورد</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/users">کاربران</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/category">دسته‌بندی‌ها</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/room">مکان‌ها</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/course">دوره‌ها</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/teacher">معلمان</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/panel">پنل برگزاری</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/prints">چاپ لیست‌ها</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/term">ترم‌ها</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/program">چیدمان</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/sync">همگام‌سازی</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/enroll/admin">ثبت‌نام</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50 col-span-2" href="<?= $CFG->wwwroot ?>/settings">تنظیمات</a>
            <?php elseif ($userRole === 'teacher'): ?>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>">پنل معلم</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/prints">چاپ لیست‌ها</a>
            <?php elseif ($userRole === 'student'): ?>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>">منظومه</a>
                <a class="rounded-xl px-3 py-2 text-slate-700 bg-white/70 hover:bg-teal-50" href="<?= $CFG->wwwroot ?>/enroll">ثبت‌نام</a>
            <?php else: ?>
                <a class="rounded-xl px-3 py-2 text-white bg-teal-600 hover:bg-teal-700" href="<?= $MDL->wwwroot ?>/login">ورود</a>
            <?php endif; ?>
            <button type="button" class="theme-toggle col-span-2 justify-center" data-mode="auto">
                <span class="theme-icon" aria-hidden="true">◐</span>
                <span class="theme-label">خودکار</span>
            </button>
            <?php if ($userRole !== 'guest' && !empty($termOptions)): ?>
                <form method="post" action="<?= $CFG->wwwroot ?>/term/context/0" class="col-span-2 grid grid-cols-4 gap-2">
                    <input type="hidden" name="<?= htmlspecialchars($csrfField, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <select name="term_id"
                            class="col-span-3 rounded-xl border border-slate-200 bg-white/85 px-3 py-2 text-xs font-semibold text-slate-700"
                            onchange="this.form.action='<?= $CFG->wwwroot ?>/term/context/' + (this.value || 0);">
                        <?php foreach ($termOptions as $t): ?>
                            <?php $tid = (int)($t['id'] ?? 0); ?>
                            <option value="<?= $tid ?>" <?= ($tid === (int)($effectiveTerm['id'] ?? 0) ? 'selected' : '') ?>>
                                <?= htmlspecialchars((string)($t['name'] ?? ('ترم #' . $tid)), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="rounded-xl bg-indigo-600 text-white text-xs font-bold px-2 py-2">سوییچ</button>
                </form>
                <?php if ($isTermOverridden): ?>
                    <form method="post" action="<?= $CFG->wwwroot ?>/term/context/reset" class="col-span-2">
                        <input type="hidden" name="<?= htmlspecialchars($csrfField, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="w-full rounded-xl bg-emerald-600 text-white text-xs font-bold px-3 py-2">بازگشت به خودکار</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </nav>
</header>
<div id="headerSpacer" aria-hidden="true" style="height: var(--header-offset, 88px);"></div>

<?php
if (!class_exists('Flash', false)) {
    require_once __DIR__ . '/../../services/flash.php';
}
$flash = class_exists('Flash', false) ? (Flash::get() ?? NULL) : NULL;
if (!$flash && !empty($_GET['msg'])) {
    $flash = [
        'message' => $_GET['msg'],
        'type' => ($_GET['type'] ?? 'info')
    ];
}
if (!empty($flash['message'])) {
    $flashType = $flash['type'] ?? 'info';
    $flashClass = 'bg-slate-100 text-slate-800 border-slate-200';
    if ($flashType === 'success') $flashClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
    if ($flashType === 'error') $flashClass = 'bg-rose-100 text-rose-800 border-rose-200';
    echo '<div id="global-flash" class="fixed top-20 right-6 z-[9999] px-4 py-3 rounded-2xl border ' . $flashClass . ' shadow-lg text-sm">';
    echo htmlspecialchars((string)$flash['message']);
    echo '</div>';
    echo '<script>setTimeout(function(){ $("#global-flash").fadeOut(300); }, 3500);</script>';
}
?>
<main class="app-main">
<script>
$(function() {
    function syncMainOffset() {
        const header = document.getElementById('siteHeader');
        const spacer = document.getElementById('headerSpacer');
        if (!header) return;
        const h = Math.ceil(header.getBoundingClientRect().height);
        const offset = h + 16;
        document.documentElement.style.setProperty('--header-offset', `${offset}px`);
        if (spacer) spacer.style.height = `${offset}px`;
        if (document.body) document.body.style.scrollPaddingTop = `${offset}px`;
    }

    syncMainOffset();
    $(window).on('resize', syncMainOffset);

    $('#mobileMenuToggle').on('click', function() {
        $('#mobileMenu').toggleClass('hidden');
        syncMainOffset();
    });
});
</script>


