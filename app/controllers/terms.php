<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/term.php';
require_once __DIR__ . '/../services/moodleAPI.php';
require_once __DIR__ . '/../services/jalali/CalendarUtils.php';
use Morilog\Jalali\CalendarUtils;

class Terms {
    private static function hasTermMdlColumn(): bool {
        global $CFG;
        $col = DB::getRow("SHOW COLUMNS FROM {$CFG->termstable} LIKE 'mdl_id'");
        return !empty($col);
    }

    private static function createMoodleTermCategory(string $termName): int {
        global $MDL;
        $parentId = (int)($MDL->defaultParentCategoryId ?? 1);
        return Moodle::createCategory([
            'name' => $termName,
            'parent' => $parentId
        ]);
    }

    private static function ensureMoodleCategoryForTerm(array $term): int {
        global $CFG;
        $termId = (int)($term['id'] ?? 0);
        $mdlId = (int)($term['mdl_id'] ?? 0);
        $name = trim((string)($term['name'] ?? ''));

        if ($mdlId > 0) {
            Moodle::updateCategory([
                'id' => $mdlId,
                'name' => $name
            ]);
            return $mdlId;
        }

        $newMdlId = self::createMoodleTermCategory($name);
        DB::query("UPDATE {$CFG->termstable} SET mdl_id = :mdl_id WHERE id = :id", [
            ':mdl_id' => $newMdlId,
            ':id' => $termId
        ]);
        return $newMdlId;
    }

    private static function normalizeDigits($str) {
        if ($str === null) return '';
        $map = [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ];
        return strtr($str, $map);
    }

    private static function jalaliToTimestamp($str) {
        if (!$str) return null;
        $raw = self::normalizeDigits(trim($str));
        $raw = preg_replace('/[^\d\/\-\:\s]/u', ' ', $raw);
        $raw = preg_replace('/\s+/', ' ', $raw);
        $raw = trim($raw);

        if (is_numeric($raw)) {
            return intval($raw);
        }

        if (!preg_match('/(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/', $raw, $m)) {
            return null;
        }
        $jy = intval($m[1]);
        $jm = intval($m[2]);
        $jd = intval($m[3]);
        if (!$jy || !$jm || !$jd) return null;

        $g = CalendarUtils::toGregorian($jy, $jm, $jd);
        $hh = 0; $mm = 0; $ss = 0;
        if (preg_match('/(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?/', $raw, $t)) {
            $hh = intval($t[1] ?? 0);
            $mm = intval($t[2] ?? 0);
            $ss = intval($t[3] ?? 0);
        }
        return mktime($hh, $mm, $ss, $g[1], $g[2], $g[0]);
    }

    public static function index($request) {
        global $CFG, $MSG;

        if (!Auth::hasPermission(role: 'guide')) {
            $msg = $MSG->notallowed;
            return include_once __DIR__ . '/../views/errors/403.php';
        }

        $terms = Term::getTerm(mode: 'all');
        $msg = $request['get']['msg'] ?? NULL;
        return include_once __DIR__ . '/../views/terms/index.php';
    }

    public static function store($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/term?msg=" . urlencode($MSG->notallowed));
        }
        if (!self::hasTermMdlColumn()) {
            return self::respond(['success' => false, 'msg' => 'فیلد terms.mdl_id وجود ندارد.'], $CFG->wwwroot . "/term?msg=" . urlencode('فیلد terms.mdl_id وجود ندارد.'));
        }

        $post = $request['post'] ?? [];
        $name = trim($post['name'] ?? '');
        $start = trim($post['start'] ?? '');
        $end = trim($post['end'] ?? '');
        $first_open_time = trim($post['first_open_time'] ?? '');
        $close_time = trim($post['close_time'] ?? '');
        $start_display = trim($post['start_display'] ?? '');
        $end_display = trim($post['end_display'] ?? '');
        $first_open_display = trim($post['first_open_time_display'] ?? '');
        $close_display = trim($post['close_time_display'] ?? '');
        $editable = isset($post['editable']) ? 1 : 0;

        if (!$name) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . "/term?msg=" . urlencode($MSG->baddata));
        }

        $startTs = is_numeric($start) ? intval($start) : self::jalaliToTimestamp($start_display);
        $endTs = is_numeric($end) ? intval($end) : self::jalaliToTimestamp($end_display);
        $firstOpenTs = is_numeric($first_open_time) ? intval($first_open_time) : self::jalaliToTimestamp($first_open_display);
        $closeTs = is_numeric($close_time) ? intval($close_time) : self::jalaliToTimestamp($close_display);

        if (!$startTs || !$endTs) {
            return self::respond(['success' => false, 'msg' => 'تاریخ شروع و پایان معتبر نیست.'], $CFG->wwwroot . "/term?msg=" . urlencode('تاریخ شروع و پایان معتبر نیست.'));
        }

        if ($startTs >= $endTs) {
            return self::respond(['success' => false, 'msg' => 'زمان شروع باید قبل از پایان باشد.'], $CFG->wwwroot . "/term?msg=" . urlencode('زمان شروع باید قبل از پایان باشد.'));
        }

        if ($firstOpenTs && ($firstOpenTs < $startTs || $firstOpenTs > $endTs)) {
            return self::respond(['success' => false, 'msg' => 'اولین زمان باز باید داخل بازه ترم باشد.'], $CFG->wwwroot . "/term?msg=" . urlencode('اولین زمان باز باید داخل بازه ترم باشد.'));
        }
        if ($closeTs && ($closeTs < $startTs || $closeTs > $endTs)) {
            return self::respond(['success' => false, 'msg' => 'زمان بسته‌شدن باید داخل بازه ترم باشد.'], $CFG->wwwroot . "/term?msg=" . urlencode('زمان بسته‌شدن باید داخل بازه ترم باشد.'));
        }
        if ($firstOpenTs && $closeTs && $firstOpenTs > $closeTs) {
            return self::respond(['success' => false, 'msg' => 'اولین زمان باز نباید بعد از زمان بسته‌شدن باشد.'], $CFG->wwwroot . "/term?msg=" . urlencode('اولین زمان باز نباید بعد از زمان بسته‌شدن باشد.'));
        }

        if (Term::hasOverlap($startTs, $endTs)) {
            return self::respond(['success' => false, 'msg' => 'بازه زمانی این ترم با ترم دیگری همپوشانی دارد.'], $CFG->wwwroot . "/term?msg=" . urlencode('بازه زمانی این ترم با ترم دیگری همپوشانی دارد.'));
        }

        $id = Term::create($name, $startTs, $endTs, $firstOpenTs, $closeTs, $editable);
        if ($id) {
            try {
                $mdlCategoryId = self::createMoodleTermCategory($name);
                DB::query("UPDATE {$CFG->termstable} SET mdl_id = :mdl_id WHERE id = :id", [
                    ':mdl_id' => $mdlCategoryId,
                    ':id' => (int)$id
                ]);
            } catch (Throwable $e) {
                Term::softDelete($id);
                return self::respond(['success' => false, 'msg' => 'ایجاد ترم در مودل ناموفق بود: ' . $e->getMessage()], $CFG->wwwroot . "/term?msg=" . urlencode('ایجاد ترم در مودل ناموفق بود.'));
            }

            return self::respond(['success' => true, 'msg' => 'ترم با موفقیت ایجاد شد و دسته‌بندی مودل نیز ساخته شد.', 'id' => $id], $CFG->wwwroot . "/term?msg=" . urlencode('ترم با موفقیت ایجاد شد و دسته‌بندی مودل نیز ساخته شد.'));
        }

        return self::respond(['success' => false, 'msg' => $MSG->unknownerror], $CFG->wwwroot . "/term?msg=" . urlencode($MSG->unknownerror));
    }

    public static function update($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], $CFG->wwwroot . "/term?msg=" . urlencode($MSG->notallowed));
        }
        if (!self::hasTermMdlColumn()) {
            return self::respond(['success' => false, 'msg' => 'فیلد terms.mdl_id وجود ندارد.'], $CFG->wwwroot . "/term?msg=" . urlencode('فیلد terms.mdl_id وجود ندارد.'));
        }

        $id = $request['route'][0] ?? NULL;
        $post = $request['post'] ?? [];
        $name = trim($post['name'] ?? '');
        $start = trim($post['start'] ?? '');
        $end = trim($post['end'] ?? '');
        $first_open_time = trim($post['first_open_time'] ?? '');
        $close_time = trim($post['close_time'] ?? '');
        $start_display = trim($post['start_display'] ?? '');
        $end_display = trim($post['end_display'] ?? '');
        $first_open_display = trim($post['first_open_time_display'] ?? '');
        $close_display = trim($post['close_time_display'] ?? '');
        $editable = isset($post['editable']) ? 1 : 0;

        if (!$id || !$name) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . "/term?msg=" . urlencode($MSG->baddata));
        }

        $term = Term::getTerm(id: $id);
        if (!$term) {
            return self::respond(['success' => false, 'msg' => $MSG->baddata], $CFG->wwwroot . "/term?msg=" . urlencode($MSG->baddata));
        }

        $startTs = is_numeric($start) ? intval($start) : self::jalaliToTimestamp($start_display);
        $endTs = is_numeric($end) ? intval($end) : self::jalaliToTimestamp($end_display);
        $firstOpenTs = is_numeric($first_open_time) ? intval($first_open_time) : self::jalaliToTimestamp($first_open_display);
        $closeTs = is_numeric($close_time) ? intval($close_time) : self::jalaliToTimestamp($close_display);

        if (!$startTs || !$endTs) {
            return self::respond(['success' => false, 'msg' => 'تاریخ شروع و پایان معتبر نیست.'], $CFG->wwwroot . "/term?msg=" . urlencode('تاریخ شروع و پایان معتبر نیست.'));
        }

        if ($startTs >= $endTs) {
            return self::respond(['success' => false, 'msg' => 'زمان شروع باید قبل از پایان باشد.'], $CFG->wwwroot . "/term?msg=" . urlencode('زمان شروع باید قبل از پایان باشد.'));
        }

        if ($firstOpenTs && ($firstOpenTs < $startTs || $firstOpenTs > $endTs)) {
            return self::respond(['success' => false, 'msg' => 'اولین زمان باز باید داخل بازه ترم باشد.'], $CFG->wwwroot . "/term?msg=" . urlencode('اولین زمان باز باید داخل بازه ترم باشد.'));
        }
        if ($closeTs && ($closeTs < $startTs || $closeTs > $endTs)) {
            return self::respond(['success' => false, 'msg' => 'زمان بسته‌شدن باید داخل بازه ترم باشد.'], $CFG->wwwroot . "/term?msg=" . urlencode('زمان بسته‌شدن باید داخل بازه ترم باشد.'));
        }
        if ($firstOpenTs && $closeTs && $firstOpenTs > $closeTs) {
            return self::respond(['success' => false, 'msg' => 'اولین زمان باز نباید بعد از زمان بسته‌شدن باشد.'], $CFG->wwwroot . "/term?msg=" . urlencode('اولین زمان باز نباید بعد از زمان بسته‌شدن باشد.'));
        }

        if (Term::hasOverlap($startTs, $endTs, $id)) {
            return self::respond(['success' => false, 'msg' => 'بازه زمانی این ترم با ترم دیگری همپوشانی دارد.'], $CFG->wwwroot . "/term?msg=" . urlencode('بازه زمانی این ترم با ترم دیگری همپوشانی دارد.'));
        }

        $ok = Term::update($id, $name, $startTs, $endTs, $firstOpenTs, $closeTs, $editable);
        if ($ok) {
            $updatedTerm = Term::getTerm(id: $id);
            try {
                self::ensureMoodleCategoryForTerm($updatedTerm ?: ['id' => $id, 'name' => $name, 'mdl_id' => 0]);
            } catch (Throwable $e) {
                return self::respond(['success' => false, 'msg' => 'ترم ذخیره شد ولی بروزرسانی دسته مودل ناموفق بود: ' . $e->getMessage()], $CFG->wwwroot . "/term?msg=" . urlencode('ترم ذخیره شد ولی بروزرسانی دسته مودل ناموفق بود.'));
            }
            return self::respond(['success' => true, 'msg' => 'ترم و دسته‌بندی مودل با موفقیت بروزرسانی شد.'], $CFG->wwwroot . "/term?msg=" . urlencode('ترم و دسته‌بندی مودل با موفقیت بروزرسانی شد.'));
        }

        return self::respond(['success' => false, 'msg' => $MSG->unknownerror], $CFG->wwwroot . "/term?msg=" . urlencode($MSG->unknownerror));
    }

    public static function delete($request) {
        global $CFG, $MSG;
        if (!Auth::hasPermission(role: 'admin')) {
            return self::respond(['success' => false, 'msg' => $MSG->notallowed], '');
        }

        $id = $request['route'][0] ?? NULL;
        if (!$id) return self::respond(['success' => false, 'msg' => $MSG->idnotgiven], '');

        $term = Term::getTerm(id: $id);
        if (!$term) return self::respond(['success' => false, 'msg' => $MSG->baddata], '');

        $now = time();
        $start = (int)($term['start'] ?? 0);
        $end = (int)($term['end'] ?? 0);
        if ($start > 0 && $end > 0 && $now >= $start && $now <= $end) {
            return self::respond([
                'success' => false,
                'blocked' => true,
                'msg' => 'Active term cannot be deleted.'
            ], '');
        }

        $blockers = Term::getDeleteBlockers($id);
        if (!empty($blockers['classes'])) {
            $count = (int)$blockers['classes'];
            return self::respond([
                'success' => false,
                'blocked' => true,
                'msg' => "This term is used in {$count} classes and cannot be deleted."
            ], '');
        }

        $ok = Term::softDelete($id);
        if ($ok) return self::respond(['success' => true, 'msg' => 'Term deleted successfully.', 'id' => $id], '');
        return self::respond(['success' => false, 'msg' => $MSG->unknownerror], '');
    }

    private static function respond($data, $redirectUrl) {
        if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            if (ob_get_length()) { ob_clean(); }
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }
        if (!empty($data['msg'])) {
            $type = (!empty($data['success']) && $data['success']) ? 'success' : 'error';
            Flash::set($data['msg'], $type);
        }
        if ($redirectUrl !== '') {
            header("Location: $redirectUrl");
            exit();
        }
        return $data;
    }
}
