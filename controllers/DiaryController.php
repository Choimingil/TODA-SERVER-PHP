<?php
requires();

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API No. 11
        * API Name : 다이어리 추가 API
        * 마지막 수정 날짜 : 20.09.03
        */
        case 'addDiary':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('status', 'title', 'color');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $body['status'] = $body['color']*100 + $body['status'];
            $data = array_merge($header, $body);
            addDiary($data);
            break;

        /*
        * API No. 12
        * API Name : 다이어리 친구 추가 API
        * 마지막 수정 날짜 : 20.09.03
        */
        case 'addDiaryFriend':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('userCode');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $tmp = array_merge($header, $body);

            $pathVar = isValidPathVar($vars['diaryID'], 'diary');
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isset($_GET['type'])){
                $tmp['type'] = 1;
            }
            else{
                $queryString = isValidQueryString('type');
                if($queryString['isSuccess'] == false){
                    echo json_encode($queryString, JSON_NUMERIC_CHECK);
                    return;
                }
                $tmp['type'] = $queryString['queryString'];
            }

            $data = array_merge($tmp, $pathVar);
            addDiaryFriend($data);
            break;

        /*
        * API No. 12-1
        * API Name : 유저코드 유저 조회 API
        * 마지막 수정 날짜 : 20.09.05
        */
        case 'getRequestByUserCode':
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
            $res->result = getRequestByUserCode($data);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = '성공적으로 조회되었습니다.';
            echo json_encode($res);
            break;

        /*
        * API No. 13
        * API Name : 다이어리 퇴장 API
        * 마지막 수정 날짜 : 20.09.05
        */
        case 'deleteDiary':
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
            deleteDiary($data);
            break;

        /*
        * API No. 14
        * API Name : 다이어리 수정 API
        * 마지막 수정 날짜 : 20.09.05
        */
        case 'updateDiary':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('diary', 'status', 'title', 'color');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $body['status'] = $body['color']*100 + $body['status'];
            $data = array_merge($header, $body);
            updateDiary($data);
            break;

        /*
        * API No. 15
        * API Name : 다이어리 조회 API
        * 마지막 수정 날짜 : 20.09.03
        */
        case 'getDiaries':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $queryString = isValidQueryString('page');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $header['page'] = ($queryString['queryString'] - 1)*20;
            $header['keyPage'] = $queryString['queryString'];

            $queryString = isValidQueryString('status');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $header['status'] = $queryString['queryString'];

            if(!isset($_GET['keyword'])){
                $result = getDiaries($header);
                if(empty($result)){
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = '등록된 다이어리가 없습니다.';
                    echo json_encode($res,JSON_NUMERIC_CHECK);
                }
                else{
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = '성공적으로 조회되었습니다.';
                    echo json_encode($res);
                }
                return;
            }

            $queryString = isValidQueryString('keyword');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }

            $data = array_merge($header, $queryString);
            getDiariesKeyword($data);
            break;

        /*
        * API No. 15-0
        * API Name : 다이어리 멤버 조회 API
        * 마지막 수정 날짜 : 20.09.03
        */
        case "getDiariesMember":
            http_response_code(200);

            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);

            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isset($_GET['page']) && !isset($_GET['status'])){
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
            $header['page'] = ($queryString['queryString'] - 1)*20;

            $queryString = isValidQueryString('status');

            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $header['status'] = $queryString['queryString'];

            $pathVar = isValidPathVar($vars["diaryID"], "diary");

            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($header, $pathVar);

            getDiariesMember($data);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
