<?php
requires();

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);
$jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {

        // 테스트
        case "test":
            http_response_code(200);
            echo json_encode($req, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 21
        * API Name : 스티커 구매 API
        * 마지막 수정 날짜 : 20.09.14
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
        * API No. 22
        * API Name : 스티커 사용 API
        * 마지막 수정 날짜 : 21.01.27
        */
        case "addSticker":
            http_response_code(200);
            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('stickerArr');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $tmp = array_merge($header, $body);

            $pathVar = isValidPathVar($vars["postID"], "post");
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($tmp, $pathVar);

            addSticker($data);
            break;

        /*
        * API No. 23
        * API Name : 스티커 수정 API
        * 마지막 수정 날짜 : 21.01.27
        */
        case "updateSticker":
            http_response_code(200);
            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('usedStickerArr');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $tmp = array_merge($header, $body);

            $pathVar = isValidPathVar($vars["postID"], "post");
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($tmp, $pathVar);

            updateSticker($data);
            break;

        /*
        * API No. 7-1
        * API Name : 사용자가 보유한 스티커 리스트 조회 API
        * 마지막 수정 날짜 : 21.01.27
        */
        case "getUserStickers":
            http_response_code(200);
            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

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
            $header['page'] = ($queryString['queryString'] - 1)*10;

            getUserStickers($header);
            break;

        /*
        * API No. 24-1
        * API Name : 스티커 상세 조회 API
        * 마지막 수정 날짜 : 21.01.27
        */
        case "getStickerDetail":
            http_response_code(200);
            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $pathVar = isValidPathVar($vars["stickerPackID"], "stickerPack");
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($header, $pathVar);
            getStickerDetail($data);
            break;

        /*
        * API No. 24-2
        * API Name : 스티커 상점 조회 API
        * 마지막 수정 날짜 : 21.01.27
        */
        case "getStore":
            http_response_code(200);
            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

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
            $header['page'] = ($queryString['queryString'] - 1)*20;

            getStore($header);
            break;

        /*
        * API No. 25
        * API Name : 등록된 스티커 리스트 조회 API
        * 마지막 수정 날짜 : 20.09.12
        */
        case "getStickerView":
            http_response_code(200);
            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $pathVar = isValidPathVar($vars["postID"], "post");
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

            $result = getStickerView($data);
            if($result == 103){
                $res->isSuccess = false;
                $res->code = 103;
                $res->message = "게시물을 볼 수 있는 권한이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            else{
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "성공적으로 조회되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
