<?php
defined('CLASSYAR_APP') || die('No direct access allowed!');

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
            throw new Exception("Moodle API error: " . $data['message']);
        }

        return $data;
    }

    /**
     * گرفتن یک یوزر بر اساس فیلد خاص
     *
     * @param string $key کلید جستجو (id, username, email, idnumber)
     * @param string|int $value مقدار کلید
     * @return array|null اطلاعات یوزر یا null
     */
    public static function getUser($key, $value) {
        global $MDL;
        
        $params = [
            'criteria' => [
                ['key' => $key, 'value' => $value]
            ]
        ];

        $data = self::callApi($MDL->getUsers, $params);

        if (!empty($data['users']) && count($data['users']) > 0) {
            return $data['users'][0]; // فقط اولین کاربر
        }

        return null;
    }
}
