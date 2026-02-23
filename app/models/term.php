<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class Term {
    private const CONTEXT_SESSION_KEY = 'classyar_term_context_id';

    private static function readContextTermId(): int {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return 0;
        }
        return max(0, (int)($_SESSION[self::CONTEXT_SESSION_KEY] ?? 0));
    }

    private static function writeContextTermId(int $termId): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        if ($termId > 0) {
            $_SESSION[self::CONTEXT_SESSION_KEY] = $termId;
            return;
        }
        unset($_SESSION[self::CONTEXT_SESSION_KEY]);
    }

    public static function setContextTerm(int $termId): bool {
        $termId = (int)$termId;
        if ($termId <= 0) {
            return false;
        }
        $term = self::getTerm(id: $termId);
        if (!is_array($term) || empty($term['id'])) {
            return false;
        }
        self::writeContextTermId((int)$term['id']);
        return true;
    }

    public static function clearContextTerm(): void {
        self::writeContextTermId(0);
    }

    public static function getRealActiveTerm(int $deleted = 0): ?array {
        global $CFG;
        $deleted = (int)$deleted;
        $now = time();
        $row = DB::getRow("
            SELECT * FROM {$CFG->termstable}
            WHERE `deleted` = :deleted
              AND `start` <= :now
              AND `end` >= :now
            ORDER BY `start` DESC, `id` DESC
            LIMIT 1
        ", [':deleted' => $deleted, ':now' => $now]);
        return is_array($row) ? $row : null;
    }

    public static function getLatestTerm(int $deleted = 0): ?array {
        global $CFG;
        $deleted = (int)$deleted;
        $row = DB::getRow("
            SELECT * FROM {$CFG->termstable}
            WHERE `deleted` = :deleted
            ORDER BY `start` DESC, `id` DESC
            LIMIT 1
        ", [':deleted' => $deleted]);
        return is_array($row) ? $row : null;
    }

    public static function getContextTerm(int $deleted = 0): ?array {
        $deleted = (int)$deleted;
        $contextId = self::readContextTermId();
        if ($contextId > 0) {
            $contextTerm = self::getTerm(id: $contextId, deleted: $deleted);
            if (is_array($contextTerm) && !empty($contextTerm['id'])) {
                return $contextTerm;
            }
            self::clearContextTerm();
        }

        $active = self::getRealActiveTerm($deleted);
        if ($active) {
            return $active;
        }

        return self::getLatestTerm($deleted);
    }

    public static function getContextInfo(): array {
        $selectedId = self::readContextTermId();
        $effective = self::getContextTerm(0);
        $realActive = self::getRealActiveTerm(0);
        $isOverridden = ($selectedId > 0) && (!empty($effective['id'])) && ((int)$effective['id'] !== (int)($realActive['id'] ?? 0));
        return [
            'selected_id' => $selectedId,
            'effective_term' => $effective,
            'real_active_term' => $realActive,
            'is_overridden' => $isOverridden,
        ];
    }

    public static function hasOverlap($startTs, $endTs, $excludeId = null) {
        global $CFG;
        if (!$startTs || !$endTs) return false;
        $params = [':start' => $startTs, ':end' => $endTs];
        $sql = "
            SELECT id FROM {$CFG->termstable}
            WHERE `deleted` = 0
              AND NOT (`end` < :start OR `start` > :end)
        ";
        if ($excludeId) {
            $sql .= " AND `id` <> :id";
            $params[':id'] = (int)$excludeId;
        }
        $sql .= " LIMIT 1";
        return (bool) DB::getRow($sql, $params);
    }
    public static function getTerm($id = NULL, $name = NULL, $mode = 'auto', $deleted = 0) {
        global $CFG;
        $deleted = (int)$deleted;

        if ($id) {
            $id = (int)$id;
            if ($id <= 0) {
                return NULL;
            }
            return DB::getRow("
                SELECT * FROM {$CFG->termstable} 
                WHERE `id` = :id AND `deleted` = :deleted
                LIMIT 1
            ", [':id' => $id, ':deleted' => $deleted]);
        }

        if ($name) {
            return DB::getRow("
                SELECT * FROM {$CFG->termstable} 
                WHERE `name` = :name AND `deleted` = :deleted
                LIMIT 1
            ", [':name' => $name, ':deleted' => $deleted]);
        }

        if ($mode === 'active') {
            return self::getContextTerm($deleted);
        }

        if ($mode === 'real_active') {
            return self::getRealActiveTerm($deleted);
        }

        if ($mode === 'all') {
            return DB::getAll("
                SELECT * FROM {$CFG->termstable} 
                WHERE `deleted` = :deleted
                ORDER BY id DESC
            ", [':deleted' => $deleted]);
        }

        return null;
    }

    public static function create($name, $start, $end, $first_open_time = null, $close_time = null, $editable = 1) {
        global $CFG;
        if (!$name || !$start || !$end) {
            return false;
        }

        return DB::insert($CFG->termstable, [
            'name' => $name,
            'start' => $start,
            'end' => $end,
            'first_open_time' => $first_open_time ?: null,
            'close_time' => $close_time ?: null,
            'editable' => (int)$editable,
            'deleted' => 0
        ]);
    }

    public static function update($id, $name, $start, $end, $first_open_time = null, $close_time = null, $editable = 1) {
        global $CFG;
        if (!$id || !$name || !$start || !$end) {
            return false;
        }
        try {
            $id = (int)$id;
        } catch (Exception $e) {
            return false;
        }

        return DB::update($CFG->termstable, [
            'name' => $name,
            'start' => $start,
            'end' => $end,
            'first_open_time' => $first_open_time ?: null,
            'close_time' => $close_time ?: null,
            'editable' => (int)$editable
        ], "`id` = $id");
    }

    public static function softDelete($id) {
        global $CFG;
        if (!$id) {
            return false;
        }
        try {
            $id = (int)$id;
        } catch (Exception $e) {
            return false;
        }

        return DB::update($CFG->termstable, [
            'deleted' => 1
        ], "`id` = $id");
    }

    public static function getDeleteBlockers($id): array {
        global $CFG;
        $id = (int)$id;
        if ($id <= 0) {
            return [];
        }

        $classes = DB::getRow("
            SELECT COUNT(*) AS c
            FROM {$CFG->classestable}
            WHERE term_id = :id AND deleted = 0
        ", [':id' => $id]);

        $classCount = (int)($classes['c'] ?? 0);
        if ($classCount <= 0) {
            return [];
        }

        return [
            'classes' => $classCount
        ];
    }
}
