<?php
requires();

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API No. 3
        * API Name : 회원탈퇴 API
        * 마지막 수정 날짜 : 20.08.21
        */
        case 'deleteUser':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            deleteUser($header);
            break;

        /*
        * API No. 4
        * API Name : 닉네임 변경 API
        * 마지막 수정 날짜 : 20.08.22
        */
        case 'updateName':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('name');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $body);
            updateName($data);
            break;

        /*
        * API No. 5
        * API Name : 비밀번호 변경 API
        * 마지막 수정 날짜 : 20.09.04
        */
        case 'updatePassword':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('pw');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $body);
            updatePassword($data, JWT_SECRET_KEY);
            break;

        /*
        * API No. 6
        * API Name : 유저 정보 변경 API(프사&닉네임)
        * 마지막 수정 날짜 : 21.01.04
        */
        case 'updateUser':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isset($req['name'])){
                $key = array('image');
                $body = isValidBody($req, $key);
                if($body['isSuccess'] == false){
                    echo json_encode($body, JSON_NUMERIC_CHECK);
                    break;
                }

                $data = array_merge($header, $body);
                updateSelfie($data);
            }

            else if(!isset($req['image'])){
                $key = array('name');
                $body = isValidBody($req, $key);
                if($body['isSuccess'] == false){
                    echo json_encode($body, JSON_NUMERIC_CHECK);
                    break;
                }

                $data = array_merge($header, $body);
                updateName($data);
            }

            else if(!isset($req['name']) && !isset($req['image'])){
                $res['isSuccess'] = TRUE;
                $res['code'] = 100;
                $res['message'] = '유저 정보가 변경되지 않았습니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
            }
            else{
                $key = array('name','image');
                $body = isValidBody($req, $key);
                if($body['isSuccess'] == false){
                    echo json_encode($body, JSON_NUMERIC_CHECK);
                    break;
                }

                $data = array_merge($header, $body);
                updateUser($data);
            }
            break;

        /*
        * API No. 6-0
        * API Name : 프로필 사진 삭제 API
        * 마지막 수정 날짜 : 20.12.31
        */
        case 'deleteSelfie':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            deleteSelfie($header);
            break;

        /*
        * API No. 6-1
        * API Name : 생일 변경 API
        * 마지막 수정 날짜 : 20.09.26
        */
        case 'updateBirth':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('birth');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $body);
            updateBirth($data);
            break;

        /*
        * API No. 7
        * API Name : 회원정보조회 API
        * 마지막 수정 날짜 : 20.08.21
        */
        case 'getUser':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isStickerSet($header['id'])) setBasicStickers($header['id']);
            getUser($header);
            break;

        /*
        * API No. 7-0
        * API Name : 유저코드를 통한 회원정보 조회 API
        * 마지막 수정 날짜 : 20.08.21
        */
        case 'getUserByUserCode':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $pathVar = isValidPathVar($vars['userCode'], 'userCode');
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $pathVar);
            getUserByUserCode($data);
            break;

        /*
        * API No. 7-2
        * API Name : 임시 비밀번호 발급 API
        * 마지막 수정 날짜 : 20.08.21
        */
        case 'getTmpPw':
            http_response_code(200);
            $key = array('id');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            getTmpPw($body);
            break;

        /*
        * API No. 10
        * API Name : 알림 조회 API
        * 마지막 수정 날짜 : 21.01.08
        */
        case 'getLog':
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

            getLog($header);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
