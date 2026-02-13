<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class Updater {
    private const REPO_OWNER = 'ghasemimh';
    private const REPO_NAME = 'classyar';
    private const REPO_BRANCH = 'main';
    private const REPO_BRANCH_FALLBACK = 'master';

    private const EXCLUDED_PATHS = [
        '.git',
        'data',
        'app/config.php',
    ];

    public static function getLocalVersion(): array {
        $versionPath = self::projectRoot() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'version.json';
        if (!file_exists($versionPath)) {
            return ['version' => '0.0.0', 'build' => 'unknown', 'channel' => 'stable', 'note' => ''];
        }

        $raw = file_get_contents($versionPath);
        $json = self::decodeJsonObject((string)$raw);
        if (!is_array($json)) {
            return ['version' => '0.0.0', 'build' => 'invalid', 'channel' => 'stable', 'note' => ''];
        }

        return [
            'version' => (string)($json['version'] ?? '0.0.0'),
            'build' => (string)($json['build'] ?? 'unknown'),
            'channel' => (string)($json['channel'] ?? 'stable'),
            'note' => (string)($json['note'] ?? ''),
        ];
    }

    public static function checkForUpdate(): array {
        $local = self::getLocalVersion();
        $remote = self::getRemoteVersion();

        if (!$remote['ok']) {
            return [
                'ok' => false,
                'message' => $remote['message'],
                'local' => $local,
                'remote' => null,
                'has_update' => false,
            ];
        }

        $remoteVersion = $remote['version'];
        $hasUpdate = self::isRemoteNewer($local, $remoteVersion);

        return [
            'ok' => true,
            'message' => $hasUpdate ? 'نسخه جدید موجود است.' : 'برنامه به‌روز است.',
            'local' => $local,
            'remote' => $remoteVersion,
            'has_update' => $hasUpdate,
        ];
    }

    public static function installUpdate(): array {
        $check = self::checkForUpdate();
        if (!$check['ok']) {
            return $check;
        }

        if (!$check['has_update']) {
            return [
                'ok' => true,
                'message' => 'آپدیتی برای نصب وجود ندارد.',
                'local' => $check['local'],
                'remote' => $check['remote'],
                'has_update' => false,
                'updated_files' => 0,
            ];
        }

        // اولویت با git است. اگر git قابل استفاده نباشد، از zip استفاده می‌کنیم.
        if (self::canUseGit()) {
            $git = self::installWithGit();
            if ($git['ok']) {
                $after = self::checkForUpdate();
                return [
                    'ok' => true,
                    'message' => 'آپدیت با git نصب شد.',
                    'local' => $after['local'] ?? self::getLocalVersion(),
                    'remote' => $after['remote'] ?? $check['remote'],
                    'has_update' => $after['has_update'] ?? false,
                    'updated_files' => $git['updated_files'] ?? 0,
                ];
            }
        }

        return self::installWithZip($check);
    }

    private static function installWithGit(): array {
        $root = self::projectRoot();
        $gitDir = $root . DIRECTORY_SEPARATOR . '.git';
        if (!is_dir($gitDir)) {
            return ['ok' => false, 'message' => 'پوشه git یافت نشد.'];
        }

        $cfgBackup = self::backupConfig();

        $commands = [
            'git -C ' . escapeshellarg($root) . ' fetch origin ' . self::REPO_BRANCH,
            'git -C ' . escapeshellarg($root) . ' pull --ff-only origin ' . self::REPO_BRANCH,
        ];

        foreach ($commands as $cmd) {
            $out = self::runShell($cmd);
            if ($out['code'] !== 0) {
                self::restoreConfig($cfgBackup);
                return ['ok' => false, 'message' => 'آپدیت با git خطا داد: ' . ($out['output'] ?: $cmd)];
            }
        }

        self::restoreConfig($cfgBackup);
        return ['ok' => true, 'updated_files' => 1];
    }

    private static function installWithZip(array $check): array {
        $zip = self::downloadRepositoryZip();
        if (!$zip['ok']) {
            return [
                'ok' => false,
                'message' => $zip['message'],
                'local' => $check['local'],
                'remote' => $check['remote'],
                'has_update' => true,
            ];
        }

        $extract = self::extractZip($zip['zip_path']);
        if (!$extract['ok']) {
            self::safeDelete($zip['zip_path']);
            return [
                'ok' => false,
                'message' => $extract['message'],
                'local' => $check['local'],
                'remote' => $check['remote'],
                'has_update' => true,
            ];
        }

        $sync = self::syncExtracted($extract['source_dir'], self::projectRoot());

        self::safeDelete($zip['zip_path']);
        self::deleteDir($extract['extract_root']);

        if (!$sync['ok']) {
            return [
                'ok' => false,
                'message' => $sync['message'],
                'local' => $check['local'],
                'remote' => $check['remote'],
                'has_update' => true,
            ];
        }

        $after = self::checkForUpdate();

        return [
            'ok' => true,
            'message' => 'آپدیت با فایل zip نصب شد.',
            'local' => $after['local'] ?? self::getLocalVersion(),
            'remote' => $after['remote'] ?? $check['remote'],
            'has_update' => $after['has_update'] ?? false,
            'updated_files' => (int)$sync['updated_files'],
        ];
    }

    private static function getRemoteVersion(): array {
        $branches = [self::REPO_BRANCH, self::REPO_BRANCH_FALLBACK];

        foreach ($branches as $branch) {
            $url = sprintf(
                'https://raw.githubusercontent.com/%s/%s/%s/app/version.json',
                self::REPO_OWNER,
                self::REPO_NAME,
                $branch
            );

            $raw = self::httpGet($url, 15);
            if ($raw === false) {
                continue;
            }

            $json = self::decodeJsonObject($raw);
            if (!is_array($json)) {
                continue;
            }

            return [
                'ok' => true,
                'version' => [
                    'version' => (string)($json['version'] ?? '0.0.0'),
                    'build' => (string)($json['build'] ?? 'unknown'),
                    'channel' => (string)($json['channel'] ?? 'stable'),
                    'note' => (string)($json['note'] ?? ''),
                ],
            ];
        }

        return ['ok' => false, 'message' => 'نسخه مخزن قابل خواندن نیست (branch: main/master).'];
    }

    private static function isRemoteNewer(array $local, array $remote): bool {
        $vLocal = (string)($local['version'] ?? '0.0.0');
        $vRemote = (string)($remote['version'] ?? '0.0.0');

        if (version_compare($vRemote, $vLocal, '>')) {
            return true;
        }
        if (version_compare($vRemote, $vLocal, '==')) {
            return ((string)($remote['build'] ?? '')) !== ((string)($local['build'] ?? ''));
        }

        return false;
    }

    private static function canUseGit(): bool {
        if (!function_exists('shell_exec')) {
            return false;
        }
        $root = self::projectRoot();
        if (!is_dir($root . DIRECTORY_SEPARATOR . '.git')) {
            return false;
        }
        $probe = self::runShell('git --version');
        return $probe['code'] === 0;
    }

    private static function runShell(string $command): array {
        $output = [];
        $code = 1;
        @exec($command . ' 2>&1', $output, $code);
        return [
            'code' => (int)$code,
            'output' => trim(implode("\n", $output)),
        ];
    }

    private static function backupConfig(): ?string {
        $cfg = self::projectRoot() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config.php';
        if (!is_file($cfg)) {
            return null;
        }

        $tmp = self::tmpRoot();
        if (!is_dir($tmp)) {
            @mkdir($tmp, 0775, true);
        }
        $bak = $tmp . DIRECTORY_SEPARATOR . 'config.php.bak';
        if (@copy($cfg, $bak)) {
            return $bak;
        }
        return null;
    }

    private static function restoreConfig(?string $backupPath): void {
        if (!$backupPath || !is_file($backupPath)) {
            return;
        }
        $cfg = self::projectRoot() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config.php';
        @copy($backupPath, $cfg);
        @unlink($backupPath);
    }

    private static function downloadRepositoryZip(): array {
        $tmpRoot = self::tmpRoot();
        if (!is_dir($tmpRoot) && !@mkdir($tmpRoot, 0775, true)) {
            return ['ok' => false, 'message' => 'ساخت مسیر موقت آپدیت ممکن نیست.'];
        }

        $zipPath = $tmpRoot . DIRECTORY_SEPARATOR . 'update_' . time() . '.zip';
        $url = sprintf(
            'https://codeload.github.com/%s/%s/zip/refs/heads/%s',
            self::REPO_OWNER,
            self::REPO_NAME,
            self::REPO_BRANCH
        );

        $raw = self::httpGet($url, 40);
        if ($raw === false) {
            return ['ok' => false, 'message' => 'دانلود فایل zip از گیت‌هاب ناموفق بود.'];
        }

        if (file_put_contents($zipPath, $raw) === false) {
            return ['ok' => false, 'message' => 'ذخیره فایل zip ناموفق بود.'];
        }

        return ['ok' => true, 'zip_path' => $zipPath];
    }

    private static function extractZip(string $zipPath): array {
        if (!class_exists('ZipArchive')) {
            return ['ok' => false, 'message' => 'اکستنشن ZipArchive روی PHP فعال نیست.'];
        }

        $extractRoot = self::tmpRoot() . DIRECTORY_SEPARATOR . 'extract_' . time();
        if (!@mkdir($extractRoot, 0775, true)) {
            return ['ok' => false, 'message' => 'ساخت پوشه استخراج ممکن نیست.'];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            self::deleteDir($extractRoot);
            return ['ok' => false, 'message' => 'باز کردن فایل zip ممکن نیست.'];
        }

        if (!$zip->extractTo($extractRoot)) {
            $zip->close();
            self::deleteDir($extractRoot);
            return ['ok' => false, 'message' => 'استخراج فایل zip ناموفق بود.'];
        }
        $zip->close();

        $candidates = glob($extractRoot . DIRECTORY_SEPARATOR . self::REPO_NAME . '-*');
        if (!$candidates || !is_dir($candidates[0])) {
            self::deleteDir($extractRoot);
            return ['ok' => false, 'message' => 'ساختار بسته دانلود شده معتبر نیست.'];
        }

        return ['ok' => true, 'extract_root' => $extractRoot, 'source_dir' => $candidates[0]];
    }

    private static function syncExtracted(string $sourceDir, string $targetDir): array {
        $updatedFiles = 0;
        $ok = self::copyRecursive($sourceDir, $targetDir, $sourceDir, $updatedFiles);
        if (!$ok) {
            return ['ok' => false, 'message' => 'کپی فایل‌ها هنگام آپدیت با خطا مواجه شد.'];
        }
        return ['ok' => true, 'updated_files' => $updatedFiles];
    }

    private static function copyRecursive(string $src, string $dst, string $baseSrc, int &$updatedFiles): bool {
        if (!is_dir($src)) {
            return false;
        }

        $items = scandir($src);
        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $srcPath = $src . DIRECTORY_SEPARATOR . $item;
            $relative = self::relativePath($srcPath, $baseSrc);
            if (self::isExcluded($relative)) {
                continue;
            }

            $dstPath = $dst . DIRECTORY_SEPARATOR . $relative;

            if (is_dir($srcPath)) {
                if (!is_dir($dstPath) && !@mkdir($dstPath, 0775, true)) {
                    return false;
                }
                if (!self::copyRecursive($srcPath, $dst, $baseSrc, $updatedFiles)) {
                    return false;
                }
                continue;
            }

            $dstDir = dirname($dstPath);
            if (!is_dir($dstDir) && !@mkdir($dstDir, 0775, true)) {
                return false;
            }

            if (!@copy($srcPath, $dstPath)) {
                return false;
            }
            $updatedFiles++;
        }

        return true;
    }

    private static function isExcluded(string $relativePath): bool {
        $normalized = str_replace('\\', '/', ltrim($relativePath, '/'));

        foreach (self::EXCLUDED_PATHS as $excluded) {
            $excluded = trim(str_replace('\\', '/', $excluded), '/');
            if ($normalized === $excluded || str_starts_with($normalized, $excluded . '/')) {
                return true;
            }
        }

        return false;
    }

    private static function relativePath(string $absolutePath, string $basePath): string {
        $a = str_replace('\\', '/', $absolutePath);
        $b = rtrim(str_replace('\\', '/', $basePath), '/');
        if (str_starts_with($a, $b . '/')) {
            return substr($a, strlen($b) + 1);
        }
        return ltrim($a, '/');
    }

    private static function httpGet(string $url, int $timeout = 10) {
        $context = stream_context_create([
            'http' => [
                'timeout' => $timeout,
                'ignore_errors' => true,
                'user_agent' => 'classyar-updater/1.0',
            ],
        ]);

        $data = @file_get_contents($url, false, $context);
        if ($data === false) {
            return false;
        }

        $statusCode = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', (string)$http_response_header[0], $m)) {
            $statusCode = (int)$m[1];
        }
        if ($statusCode >= 400) {
            return false;
        }

        return $data;
    }

    private static function decodeJsonObject(string $raw): ?array {
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }
        $json = json_decode(trim($raw), true);
        return is_array($json) ? $json : null;
    }

    private static function tmpRoot(): string {
        return self::projectRoot() . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'updater';
    }

    private static function projectRoot(): string {
        return realpath(__DIR__ . '/..' . '/..') ?: dirname(__DIR__, 2);
    }

    private static function safeDelete(string $file): void {
        if (is_file($file)) {
            @unlink($file);
        }
    }

    private static function deleteDir(string $dir): void {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                self::deleteDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
