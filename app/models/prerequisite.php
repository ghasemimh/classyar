<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class Prerequisite {
    public static function getByClass($classId, $deleted = 0) {
        global $CFG;
        $classId = (int)$classId;
        return DB::getAll("
            SELECT * FROM {$CFG->prerequisitestable}
            WHERE `class_id` = :class_id AND `deleted` = :deleted
            ORDER BY `id` ASC
        ", [':class_id' => $classId, ':deleted' => (int)$deleted]);
    }

    public static function getAll($deleted = 0) {
        global $CFG;
        return DB::getAll("
            SELECT * FROM {$CFG->prerequisitestable}
            WHERE `deleted` = :deleted
            ORDER BY `id` ASC
        ", [':deleted' => (int)$deleted]);
    }

    public static function softDeleteByClass($classId) {
        global $CFG;
        $classId = (int)$classId;
        return DB::update($CFG->prerequisitestable, [
            'deleted' => 1
        ], "`class_id` = $classId");
    }

    public static function deleteByClass($classId) {
        global $CFG;
        $classId = (int)$classId;
        return DB::query("
            DELETE FROM {$CFG->prerequisitestable}
            WHERE `class_id` = :class_id
        ", [':class_id' => $classId]);
    }

    public static function createMany($classId, $items = []) {
        global $CFG;
        $classId = (int)$classId;
        if (!$classId || empty($items)) return true;

        $seen = [];
        foreach ($items as $item) {
            $courseId = isset($item['course_id']) ? (int)$item['course_id'] : null;
            $altText = isset($item['alternative_text']) ? trim($item['alternative_text']) : null;
            $key = $courseId ? ('course:' . $courseId) : ('text:' . $altText);
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            if ($courseId || ($altText !== null && $altText !== '')) {
                DB::insert($CFG->prerequisitestable, [
                    'class_id' => $classId,
                    'course_id' => $courseId ?: null,
                    'alternative_text' => $altText ?: null,
                    'deleted' => 0
                ]);
            }
        }
        return true;
    }
}
