<?php
requires();

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API No. 34
        * API Name : 일정 추가 API
        * 마지막 수정 날짜 : 20.09.12
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
        * API No. 35
        * API Name : 일정 삭제 API
        * 마지막 수정 날짜 : 20.09.12
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
        * API No. 36
        * API Name : 일정 수정 API
        * 마지막 수정 날짜 : 20.09.12
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

        /*
        * API No. 37
        * API Name : 일정 조회 API
        * 마지막 수정 날짜 : 20.09.12
        */
        case "getSchedule":
            http_response_code(200);

            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);

            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $pathVar = isValidPathVar($vars["diaryID"], "diary");

            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($header, $pathVar);

            if(!isset($_GET['page'])){
                $res->isSuccess = FALSE;
                $res->code = 104;
                $res->message = "쿼리스트링이 등록되지 않았습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $queryString = isValidQueryString('page');

            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $data['page'] = ($queryString['queryString'] - 1)*20;

            getSchedule($data);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
