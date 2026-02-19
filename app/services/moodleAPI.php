<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class Moodle {

    private static function callApi($function, $params = []) {
        global $MDL;

        $serverurl = $MDL->wwwroot . '/webservice/rest/server.php' .
            '?wstoken=' . $MDL->token .
            '&wsfunction=' . $function .
            '&moodlewsrestformat=json';

        $ch = curl_init($serverurl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['exception'])) {
            $msg = $data['message'] ?? 'unknown moodle error';
            $code = $data['errorcode'] ?? '';
            $debug = $data['debuginfo'] ?? '';
            $parts = ["Moodle API error: {$msg}"];
            if ($code !== '') $parts[] = "code={$code}";
            if ($debug !== '') $parts[] = "debug={$debug}";
            throw new Exception(implode(' | ', $parts));
        }

        return $data;
    }

    /**
     * گرفتن یوزر از مودل
     *
     * @param string|null $key
     * @param string|int|null $value
     * @param string $mode single|all
     * @return array|null
     */
    public static function getUser($key = null, $value = null, $mode = 'single') {
        global $MDL;

        if ($mode === 'all') {
            // گرفتن همه کاربران
            $params = [
                'criteria' => [
                    ['key' => 'deleted', 'value' => '0']
                ]
            ];
            $data = self::callApi($MDL->getUsers, $params);
            return $data['users'] ?? [];
        }

        if ($mode === 'single' && $key && $value) {
            $params = ['criteria' => [['key' => $key, 'value' => $value]]];
            $data = self::callApi($MDL->getUsers, $params);
            return (!empty($data['users'])) ? $data['users'][0] : null;
        }

        return null;
    }

    public static function createCourse(array $courseData) {
        global $MDL;
        $params = [
            'courses' => [$courseData]
        ];
        $data = self::callApi($MDL->createCourses, $params);
        if (!is_array($data) || empty($data[0]['id'])) {
            throw new Exception('Moodle create course response is invalid.');
        }
        return (int)$data[0]['id'];
    }

    public static function getCourseByField(string $field, string $value) {
        $data = self::getCoursesByField($field, $value);
        if (!empty($data['courses'][0])) {
            return $data['courses'][0];
        }
        return null;
    }

    public static function getCoursesByField(string $field, string $value) {
        global $MDL;
        return self::callApi($MDL->getCoursesByField, [
            'field' => $field,
            'value' => $value
        ]);
    }

    public static function updateCourse(array $courseData) {
        global $MDL;
        $params = [
            'courses' => [$courseData]
        ];
        self::callApi($MDL->updateCourses, $params);
        return true;
    }

    public static function createCategory(array $categoryData): int {
        global $MDL;
        $params = [
            'categories' => [$categoryData]
        ];
        $data = self::callApi($MDL->createCategories, $params);
        if (!is_array($data) || empty($data[0]['id'])) {
            throw new Exception('Moodle create category response is invalid.');
        }
        return (int)$data[0]['id'];
    }

    public static function updateCategory(array $categoryData): bool {
        global $MDL;
        $params = [
            'categories' => [$categoryData]
        ];
        self::callApi($MDL->updateCategories, $params);
        return true;
    }

    public static function enrolUsers(array $enrolments) {
        global $MDL;
        if (empty($enrolments)) {
            return true;
        }
        return self::callApi($MDL->enrollUsers, ['enrolments' => $enrolments]);
    }

    public static function unenrolUsers(array $enrolments) {
        global $MDL;
        if (empty($enrolments)) {
            return true;
        }
        return self::callApi($MDL->unenrolUsers, ['enrolments' => $enrolments]);
    }

    public static function getEnrolledUsers(int $courseId, array $options = []) {
        global $MDL;
        $params = array_merge([
            'courseid' => $courseId
        ], $options);
        return self::callApi($MDL->getEnrolledUsers, $params);
    }

    public static function deleteCourses(array $courseIds) {
        global $MDL;
        $ids = array_values(array_filter(array_map('intval', $courseIds), fn($id) => $id > 0));
        if (empty($ids)) {
            return true;
        }
        $fn = $MDL->deleteCourses ?? 'core_course_delete_courses';
        return self::callApi($fn, ['courseids' => $ids]);
    }

    public static function assignRoles(array $assignments) {
        global $MDL;
        if (empty($assignments)) {
            return true;
        }
        return self::callApi($MDL->assignRoles, ['assignments' => $assignments]);
    }
}
