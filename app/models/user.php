<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class User {
    private static $tableColumns = [];

    private static function tableColumns(string $table): array {
        if (isset(self::$tableColumns[$table])) {
            return self::$tableColumns[$table];
        }
        $cols = DB::getAll("SHOW COLUMNS FROM {$table}");
        self::$tableColumns[$table] = array_map(fn($c) => (string)$c['Field'], $cols ?: []);
        return self::$tableColumns[$table];
    }

    private static function hasColumn(string $table, string $column): bool {
        return in_array($column, self::tableColumns($table), true);
    }

    private static function studentLinkColumn(): ?string {
        global $CFG;
        $cols = self::tableColumns($CFG->studentstable);
        if (in_array('user_id', $cols, true)) return 'user_id';
        if (in_array('mdl_id', $cols, true)) return 'mdl_id';
        return null;
    }

    private static function activeCondition(string $table): string {
        return self::hasColumn($table, 'deleted') ? ' AND deleted = 0 ' : '';
    }

    public static function getUserByMoodleId($mdlId) {
        global $CFG;
        return DB::getRow(
            "SELECT id, mdl_id, suspend, role FROM {$CFG->userstable} WHERE mdl_id = :mdl_id LIMIT 1",
            [':mdl_id' => (int)$mdlId]
        ) ?: null;
    }

    public static function createUser($mdlId, $role = 'student', $suspend = 0) {
        global $CFG;
        $user = self::getUserByMoodleId($mdlId);
        if ($user) {
            return false;
        }

        return DB::insert($CFG->userstable, [
            'mdl_id' => (int)$mdlId,
            'role' => (string)$role,
            'suspend' => (int)$suspend
        ]) ?: false;
    }

    public static function getUser($id = null, $role = null, $mode = 'auto', $suspend = 0) {
        global $CFG;

        if ($id !== null) {
            $id = (int)$id;
            if ($id <= 0) return null;
            return DB::getRow("SELECT * FROM {$CFG->userstable} WHERE id = :id LIMIT 1", [':id' => $id]) ?: null;
        }

        if ($mode === 'all') {
            return DB::getAll("SELECT * FROM {$CFG->userstable} ORDER BY id DESC");
        }

        return null;
    }

    public static function countForAdmin(array $filters): int {
        global $CFG;
        [$where, $params] = self::buildAdminWhere($filters);
        $row = DB::getRow("SELECT COUNT(*) AS c FROM {$CFG->userstable} u {$where}", $params);
        return (int)($row['c'] ?? 0);
    }

    public static function listForAdmin(array $filters, int $limit, int $offset): array {
        global $CFG;
        [$where, $params] = self::buildAdminWhere($filters);
        $limit = max(1, $limit);
        $offset = max(0, $offset);

        $rows = DB::getAll(
            "SELECT u.*
             FROM {$CFG->userstable} u
             {$where}
             ORDER BY u.id DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        if (empty($rows)) {
            return [];
        }

        $teacherMap = [];
        $teacherRows = DB::getAll("SELECT id, user_id" . (self::hasColumn($CFG->teacherstable, 'deleted') ? ', deleted' : '') . " FROM {$CFG->teacherstable}");
        foreach ($teacherRows as $t) {
            $uid = (int)($t['user_id'] ?? 0);
            if ($uid <= 0) continue;
            $isActive = !self::hasColumn($CFG->teacherstable, 'deleted') || (int)($t['deleted'] ?? 0) === 0;
            $teacherMap[$uid] = ['id' => (int)$t['id'], 'active' => $isActive];
        }

        $studentMap = [];
        $studentLink = self::studentLinkColumn();
        if ($studentLink !== null) {
            $studentRows = DB::getAll("SELECT id, {$studentLink}" . (self::hasColumn($CFG->studentstable, 'deleted') ? ', deleted' : '') . " FROM {$CFG->studentstable}");
            foreach ($studentRows as $s) {
                $key = (int)($s[$studentLink] ?? 0);
                if ($key <= 0) continue;
                $isActive = !self::hasColumn($CFG->studentstable, 'deleted') || (int)($s['deleted'] ?? 0) === 0;
                $studentMap[$key] = ['id' => (int)$s['id'], 'active' => $isActive];
            }
        }

        foreach ($rows as &$row) {
            $uid = (int)$row['id'];
            $mdlId = (int)($row['mdl_id'] ?? 0);

            $teacher = $teacherMap[$uid] ?? null;
            $student = null;
            if ($studentLink === 'user_id') {
                $student = $studentMap[$uid] ?? null;
            } elseif ($studentLink === 'mdl_id') {
                $student = $studentMap[$mdlId] ?? null;
            }

            $row['teacher_profile_id'] = $teacher['id'] ?? null;
            $row['teacher_profile_active'] = $teacher['active'] ?? false;
            $row['student_profile_id'] = $student['id'] ?? null;
            $row['student_profile_active'] = $student['active'] ?? false;
        }
        unset($row);

        return $rows;
    }

    public static function roleStats(): array {
        global $CFG;
        $rows = DB::getAll("SELECT role, COUNT(*) AS c FROM {$CFG->userstable} GROUP BY role");
        $out = ['admin' => 0, 'guide' => 0, 'teacher' => 0, 'student' => 0];
        foreach ($rows as $r) {
            $role = (string)($r['role'] ?? '');
            $out[$role] = (int)($r['c'] ?? 0);
        }
        return $out;
    }

    public static function suspendStats(): array {
        global $CFG;
        $rows = DB::getAll("SELECT suspend, COUNT(*) AS c FROM {$CFG->userstable} GROUP BY suspend");
        $out = ['active' => 0, 'suspended' => 0];
        foreach ($rows as $r) {
            if ((int)($r['suspend'] ?? 0) === 1) {
                $out['suspended'] = (int)$r['c'];
            } else {
                $out['active'] += (int)$r['c'];
            }
        }
        return $out;
    }

    private static function buildAdminWhere(array $filters): array {
        $where = [];
        $params = [];

        $moodleMatchedIds = [];
        if (!empty($filters['moodle_match_ids']) && is_array($filters['moodle_match_ids'])) {
            $moodleMatchedIds = array_values(array_unique(array_filter(
                array_map('intval', $filters['moodle_match_ids']),
                fn($v) => $v > 0
            )));
        }

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            if (ctype_digit($search)) {
                $where[] = '(u.id = :search_id OR u.mdl_id = :search_mdl OR u.role LIKE :search_role)';
                $params[':search_id'] = (int)$search;
                $params[':search_mdl'] = (int)$search;
                $params[':search_role'] = '%' . $search . '%';
            } else {
                $orParts = ['u.role LIKE :search_role'];
                $params[':search_role'] = '%' . $search . '%';

                if (!empty($moodleMatchedIds)) {
                    $inParts = [];
                    foreach ($moodleMatchedIds as $idx => $mid) {
                        $ph = ':mdl_match_' . $idx;
                        $inParts[] = $ph;
                        $params[$ph] = $mid;
                    }
                    $orParts[] = 'u.mdl_id IN (' . implode(',', $inParts) . ')';
                }

                $where[] = '(' . implode(' OR ', $orParts) . ')';
            }
        }

        $role = trim((string)($filters['role'] ?? ''));
        if ($role !== '' && in_array($role, ['admin', 'guide', 'teacher', 'student'], true)) {
            $where[] = 'u.role = :role';
            $params[':role'] = $role;
        }

        $status = trim((string)($filters['status'] ?? ''));
        if ($status === 'active') {
            $where[] = 'u.suspend = 0';
        } elseif ($status === 'suspended') {
            $where[] = 'u.suspend = 1';
        }

        $sql = empty($where) ? '' : ('WHERE ' . implode(' AND ', $where));
        return [$sql, $params];
    }

    private static function teacherRowByUser(int $userId): ?array {
        global $CFG;
        $sql = "SELECT * FROM {$CFG->teacherstable} WHERE user_id = :uid";
        if (self::hasColumn($CFG->teacherstable, 'deleted')) {
            $sql .= ' ORDER BY deleted ASC, id DESC';
        } else {
            $sql .= ' ORDER BY id DESC';
        }
        $sql .= ' LIMIT 1';
        return DB::getRow($sql, [':uid' => $userId]) ?: null;
    }

    private static function studentRowByUser(int $userId, int $mdlId): ?array {
        global $CFG;
        $link = self::studentLinkColumn();
        if ($link === null) return null;
        $value = ($link === 'user_id') ? $userId : $mdlId;
        $sql = "SELECT * FROM {$CFG->studentstable} WHERE {$link} = :k";
        if (self::hasColumn($CFG->studentstable, 'deleted')) {
            $sql .= ' ORDER BY deleted ASC, id DESC';
        } else {
            $sql .= ' ORDER BY id DESC';
        }
        $sql .= ' LIMIT 1';
        return DB::getRow($sql, [':k' => $value]) ?: null;
    }

    private static function userHasTeacherClasses(int $teacherId): bool {
        global $CFG;
        $row = DB::getRow(
            "SELECT id FROM {$CFG->classestable} WHERE deleted = 0 AND teacher_id = :tid LIMIT 1",
            [':tid' => $teacherId]
        );
        return !empty($row);
    }

    private static function studentHasEnrollments(int $studentId): bool {
        global $CFG;
        $row = DB::getRow(
            "SELECT e.id
             FROM {$CFG->enrollstable} e
             JOIN {$CFG->classestable} c ON c.id = e.class_id
             WHERE e.deleted = 0 AND c.deleted = 0 AND e.student_id = :sid
             LIMIT 1",
            [':sid' => $studentId]
        );
        return !empty($row);
    }

    private static function activeAdminCount(): int {
        global $CFG;
        $row = DB::getRow(
            "SELECT COUNT(*) AS c FROM {$CFG->userstable} WHERE role = 'admin' AND suspend = 0"
        );
        return (int)($row['c'] ?? 0);
    }

    private static function activateTeacherProfile(int $userId): bool {
        global $CFG;
        $row = self::teacherRowByUser($userId);
        if ($row) {
            if (self::hasColumn($CFG->teacherstable, 'deleted') && (int)($row['deleted'] ?? 0) !== 0) {
                DB::query("UPDATE {$CFG->teacherstable} SET deleted = 0 WHERE id = :id", [':id' => (int)$row['id']]);
            }
            return true;
        }

        $data = ['user_id' => $userId];
        if (self::hasColumn($CFG->teacherstable, 'times')) {
            $data['times'] = '';
        }
        if (self::hasColumn($CFG->teacherstable, 'deleted')) {
            $data['deleted'] = 0;
        }
        return (bool)DB::insert($CFG->teacherstable, $data);
    }

    private static function deactivateTeacherProfile(int $userId): bool {
        global $CFG;
        if (!self::hasColumn($CFG->teacherstable, 'deleted')) {
            return true;
        }
        DB::query("UPDATE {$CFG->teacherstable} SET deleted = 1 WHERE user_id = :uid", [':uid' => $userId]);
        return true;
    }

    private static function activateStudentProfile(int $userId, int $mdlId): bool {
        global $CFG;
        $row = self::studentRowByUser($userId, $mdlId);
        if ($row) {
            if (self::hasColumn($CFG->studentstable, 'deleted') && (int)($row['deleted'] ?? 0) !== 0) {
                DB::query("UPDATE {$CFG->studentstable} SET deleted = 0 WHERE id = :id", [':id' => (int)$row['id']]);
            }
            if (self::hasColumn($CFG->studentstable, 'suspend') && (int)($row['suspend'] ?? 0) !== 0) {
                DB::query("UPDATE {$CFG->studentstable} SET suspend = 0 WHERE id = :id", [':id' => (int)$row['id']]);
            }
            return true;
        }

        require_once __DIR__ . '/student.php';
        return (bool)Student::createStudent($mdlId);
    }

    private static function deactivateStudentProfile(int $userId, int $mdlId): bool {
        global $CFG;
        $row = self::studentRowByUser($userId, $mdlId);
        if (!$row) {
            return true;
        }
        $id = (int)$row['id'];
        if (self::hasColumn($CFG->studentstable, 'deleted')) {
            DB::query("UPDATE {$CFG->studentstable} SET deleted = 1 WHERE id = :id", [':id' => $id]);
        }
        return true;
    }

    public static function changeRoleTransactional(int $userId, string $newRole, int $actorUserId = 0): array {
        global $CFG;
        $newRole = trim($newRole);
        if (!in_array($newRole, ['admin', 'guide', 'teacher', 'student'], true)) {
            return ['success' => false, 'msg' => 'نقش نامعتبر است.'];
        }

        $user = self::getUser($userId);
        if (!$user) {
            return ['success' => false, 'msg' => 'کاربر یافت نشد.'];
        }

        $oldRole = (string)($user['role'] ?? 'student');
        $mdlId = (int)($user['mdl_id'] ?? 0);
        if ($oldRole === $newRole) {
            return ['success' => true, 'msg' => 'نقش کاربر تغییری نکرد.', 'no_change' => true];
        }

        if ($actorUserId > 0 && $actorUserId === $userId) {
            return ['success' => false, 'msg' => 'تغییر نقش کاربر جاری مجاز نیست.'];
        }

        if ($oldRole === 'admin' && $newRole !== 'admin' && self::activeAdminCount() <= 1) {
            return ['success' => false, 'msg' => 'آخرین ادمین فعال را نمی‌توانید تغییر نقش دهید.'];
        }

        DB::query('START TRANSACTION');
        try {
            if ($oldRole === 'teacher' && $newRole !== 'teacher') {
                $t = self::teacherRowByUser($userId);
                if ($t && self::userHasTeacherClasses((int)$t['id'])) {
                    DB::query('ROLLBACK');
                    return ['success' => false, 'msg' => 'این معلم کلاس فعال دارد و تغییر نقش ممکن نیست.'];
                }
                self::deactivateTeacherProfile($userId);
            }

            if ($oldRole === 'student' && $newRole !== 'student') {
                $s = self::studentRowByUser($userId, $mdlId);
                if ($s && self::studentHasEnrollments((int)$s['id'])) {
                    DB::query('ROLLBACK');
                    return ['success' => false, 'msg' => 'این دانش‌آموز ثبت‌نام فعال دارد و تغییر نقش ممکن نیست.'];
                }
                self::deactivateStudentProfile($userId, $mdlId);
            }

            if ($newRole === 'teacher') {
                if (!self::activateTeacherProfile($userId)) {
                    throw new Exception('تعریف پروفایل معلم انجام نشد.');
                }
            }

            if ($newRole === 'student') {
                if (!self::activateStudentProfile($userId, $mdlId)) {
                    throw new Exception('تعریف پروفایل دانش‌آموز انجام نشد.');
                }
            }

            DB::query("UPDATE {$CFG->userstable} SET role = :role WHERE id = :id", [':role' => $newRole, ':id' => $userId]);

            DB::query('COMMIT');
            return ['success' => true, 'msg' => 'نقش کاربر با موفقیت تغییر کرد.'];
        } catch (Throwable $e) {
            DB::query('ROLLBACK');
            return ['success' => false, 'msg' => 'خطا در تغییر نقش: ' . $e->getMessage()];
        }
    }

    public static function updateSuspendTransactional(int $userId, int $suspend, int $actorUserId = 0): array {
        global $CFG;
        $user = self::getUser($userId);
        if (!$user) {
            return ['success' => false, 'msg' => 'کاربر یافت نشد.'];
        }

        $suspend = $suspend ? 1 : 0;
        $oldSuspend = (int)($user['suspend'] ?? 0);
        if ($oldSuspend === $suspend) {
            return ['success' => true, 'msg' => 'وضعیت کاربر تغییری نکرد.', 'no_change' => true];
        }

        if ($actorUserId > 0 && $actorUserId === $userId && $suspend === 1) {
            return ['success' => false, 'msg' => 'تعلیق حساب کاربر جاری مجاز نیست.'];
        }

        if ((string)($user['role'] ?? '') === 'admin' && $suspend === 1 && self::activeAdminCount() <= 1) {
            return ['success' => false, 'msg' => 'آخرین ادمین فعال را نمی‌توانید غیرفعال کنید.'];
        }

        DB::query('START TRANSACTION');
        try {
            DB::query("UPDATE {$CFG->userstable} SET suspend = :suspend WHERE id = :id", [':suspend' => $suspend, ':id' => $userId]);

            $mdlId = (int)($user['mdl_id'] ?? 0);
            $studentRow = self::studentRowByUser($userId, $mdlId);
            if ($studentRow && self::hasColumn($CFG->studentstable, 'suspend')) {
                DB::query("UPDATE {$CFG->studentstable} SET suspend = :suspend WHERE id = :id", [':suspend' => $suspend, ':id' => (int)$studentRow['id']]);
            }

            DB::query('COMMIT');
            return ['success' => true, 'msg' => 'وضعیت کاربر با موفقیت تغییر کرد.'];
        } catch (Throwable $e) {
            DB::query('ROLLBACK');
            return ['success' => false, 'msg' => 'خطا در تغییر وضعیت: ' . $e->getMessage()];
        }
    }

    public static function bulkUpdate(array $userIds, string $action, array $payload, int $actorUserId = 0): array {
        $success = 0;
        $fail = 0;
        $errors = [];

        foreach ($userIds as $id) {
            $uid = (int)$id;
            if ($uid <= 0) continue;

            if ($action === 'role') {
                $newRole = (string)($payload['role'] ?? '');
                $res = self::changeRoleTransactional($uid, $newRole, $actorUserId);
            } elseif ($action === 'suspend') {
                $res = self::updateSuspendTransactional($uid, 1, $actorUserId);
            } elseif ($action === 'activate') {
                $res = self::updateSuspendTransactional($uid, 0, $actorUserId);
            } else {
                $res = ['success' => false, 'msg' => 'عملیات نامعتبر'];
            }

            if (!empty($res['success'])) {
                $success++;
            } else {
                $fail++;
                $errors[] = "ID {$uid}: " . ($res['msg'] ?? 'خطا');
            }
        }

        return [
            'success' => $fail === 0,
            'msg' => "موفق: {$success} | ناموفق: {$fail}",
            'errors' => $errors,
        ];
    }

}
