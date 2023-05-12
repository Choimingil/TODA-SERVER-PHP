<?php
require './pdos/alarm.php';

header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API URL. GET /alarm/remind
        * API Name : 리마인드 알림 발송 API
        * 마지막 수정 날짜 : 21.12.02
        */
        case 'sendRemindAlarm':
            http_response_code(200);
            $body = getBody($req, array('token','device'));
            sendRemindAlarm($body);
            new DefaultResponse(true,100,'성공적으로 발송되었습니다.');
            break;

        /*
        * API URL. POST /alarm/event
        * API Name : 이벤트 알림 발송 API
        * 마지막 수정 날짜 : 21.12.02
        */
        case 'sendEventAlarm':
            http_response_code(200);
            sendEventAlarm();
            new DefaultResponse(true,100,'성공적으로 발송되었습니다.');
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
