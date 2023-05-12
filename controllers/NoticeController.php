<?php
requires();

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API No. 15-1
        * API Name : 다이어리 공지 등록 API
        * 마지막 수정 날짜 : 20.09.20
        */
        case 'postNotice':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('diary', 'notice');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $body);
            postNotice($data);
            break;

        /*
        * API No. 15-2
        * API Name : 다이어리 공지 삭제 API
        * 마지막 수정 날짜 : 20.09.20
        */
        case 'deleteNotice':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $pathVar = isValidPathVar($vars['diaryID'], 'diary');
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $pathVar);
            deleteNotice($data);
            break;

        /*
        * API No. 15-3
        * API Name : 다이어리 공지 수정 API
        * 마지막 수정 날짜 : 20.09.20
        */
        case 'updateNotice':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('diary', 'notice');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $body);
            updateNotice($data);
            break;

        /*
        * API No. 15-4
        * API Name : 다이어리 공지 조회 API
        * 마지막 수정 날짜 : 20.09.20
        */
        case 'getNotice':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $pathVar = isValidPathVar($vars['diaryID'], 'diary');
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $pathVar);
            getNotice($data);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
