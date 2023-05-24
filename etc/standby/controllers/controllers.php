<?php
require './etc/standby/pdos.php';
require './etc/standby/functions.php';

header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API URL. POST /login/{code:\d+}
        * API Name : 간편 로그인 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'createSimpleJwt':
            http_response_code(200);

            $pathVar = isValidPathVar($vars['code'], 'code');
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('token');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($pathVar, $body);

            createSimpleJwt($data['token'], $data['pathVar']);
            break;

        /*
        * API URL. POST /points
        * API Name : 포인트 사용 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case "postPoint":
            http_response_code(200);

            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);

            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('point');
            $body = isValidBody($req, $key);

            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($header, $body);

            postPoint($data);
            break;

        /*
        * API URL. POST /buy
        * API Name : 스티커 구매 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case "buySticker":
            http_response_code(200);

            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);

            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('sticker');
            $body = isValidBody($req, $key);

            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($header, $body);

            buySticker($data);
            break;

        /*
        * API URL. POST /schedule
        * API Name : 일정 추가 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case "addSchedule":
            http_response_code(200);

            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);

            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('diary', 'date', 'title');
            $body = isValidBody($req, $key);

            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($header, $body);

            addSchedule($data);
            break;

        /*
        * API URL. DELETE /schedule/{scheduleID:\d+}
        * API Name : 일정 삭제 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case "deleteSchedule":
            http_response_code(200);

            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);

            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $pathVar = isValidPathVar($vars["scheduleID"], "schedule");

            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($header, $pathVar);

            deleteSchedule($data);
            break;

        /*
        * API URL. PATCH /schedule
        * API Name : 일정 수정 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case "updateSchedule":
            http_response_code(200);

            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);

            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('schedule', 'date', 'title');
            $body = isValidBody($req, $key);

            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($header, $body);

            updateSchedule($data);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
