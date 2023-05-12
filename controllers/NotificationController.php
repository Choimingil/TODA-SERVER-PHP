<?php
requires();

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API No. 1-5
        * API Name : 알림 토큰 저장 API
        * 마지막 수정 날짜 : 20.12.27
        */
        case 'checkNotification':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);

            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('token','isAllowed');
            $body = isValidBody($req, $key);

            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $tmp = array_merge($header, $body);
            $queryString = isValidQueryString('type');
            if($queryString['isSuccess'] == false){
                checkNotification($tmp);
                sendToAlarmServer('/alarm/notification/ios',$tmp);
            }
            else{
                $data = array_merge($tmp, $queryString);
                checkNotificationAndroid($data);
                sendToAlarmServer('/alarm/notification/aos',$data);
            }
            break;

        /*
        * API No. 1-7
        * API Name : 알림 허용 여부 확인 API
        * 마지막 수정 날짜 : 20.12.27
        */
        case 'checkAlarm':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            checkAlarm($header);
            break;

        /*
        * API No. 1-8
        * API Name : 알림 허용 여부 확인 API
        * 마지막 수정 날짜 : 20.12.27
        */
        case 'updateAlarm':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            updateAlarm($header);
            sendToAlarmServer('/alarm',$header);
            break;

        /*
        * API No. 7-5
        * API Name : 알림 허용 여부 확인 API
        * 마지막 수정 날짜 : 20.12.27
        */
        case 'checkAlarmVer2':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $queryString = isValidQueryString('fcmToken');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $data = array_merge($header, $queryString);
            checkAlarmVer2($data);
            break;

        /*
        * API No. 7-6
        * API Name : 알림 허용 여부 변경 API
        * 마지막 수정 날짜 : 20.12.27
        */
        case 'updateAlarmVer2':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('alarmType','fcmToken');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($header, $body);
            updateAlarmVer2($data);
            sendToAlarmServer('/alarm/ver2',$data);
            break;

        /*
        * API No. 7-7
        * API Name : 알림 시간 조회 API
        * 마지막 수정 날짜 : 21.01.30
        */
        case 'getAlarmTime':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }
            $queryString = isValidQueryString('fcmToken');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $data = array_merge($header, $queryString);
            getAlarmTime($data);
            break;

        /*
        * API No. 7-8
        * API Name : 알림 시간 변경 API
        * 마지막 수정 날짜 : 21.01.30
        */
        case 'updateAlarmTime':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('time','fcmToken');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($header, $body);

            updateAlarmTime($data);
            sendToAlarmServer('/alarm/time',$data);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
