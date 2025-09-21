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
            throw new Exception("Moodle API error: " . $data['message']);
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
}
