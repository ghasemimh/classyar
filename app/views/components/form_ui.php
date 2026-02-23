<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

if (!function_exists('uiFormAlert')) {
    function uiFormAlert(?string $message, string $type = 'info'): void {
        if (empty($message)) {
            return;
        }
        $classes = [
            'success' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            'error' => 'bg-rose-100 text-rose-800 border border-rose-200',
            'info' => 'bg-sky-100 text-sky-800 border border-sky-200',
        ];
        $style = $classes[$type] ?? $classes['info'];
        echo '<div class="mb-6 p-4 rounded-2xl ' . $style . '">';
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        echo '</div>';
    }
}

if (!function_exists('uiFieldLabel')) {
    function uiFieldLabel(string $for, string $label): void {
        echo '<label for="' . htmlspecialchars($for, ENT_QUOTES, 'UTF-8') . '" class="block text-sm font-semibold text-slate-700 mb-2">';
        echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        echo '</label>';
    }
}

if (!function_exists('uiInputClass')) {
    function uiInputClass(): string {
        return 'w-full rounded-2xl border border-slate-300 bg-white/85 px-4 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-200 focus:border-sky-400';
    }
}

if (!function_exists('uiPrimaryButtonClass')) {
    function uiPrimaryButtonClass(): string {
        return 'px-6 py-2.5 rounded-2xl bg-gradient-to-r from-sky-600 to-indigo-600 text-white font-semibold hover:opacity-90 transition';
    }
}

if (!function_exists('uiSecondaryButtonClass')) {
    function uiSecondaryButtonClass(): string {
        return 'px-6 py-2.5 rounded-2xl bg-slate-600 text-white font-semibold hover:bg-slate-700 transition';
    }
}
