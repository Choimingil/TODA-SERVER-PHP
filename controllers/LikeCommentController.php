<?php
requires();

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API No. 28
        * API Name : 좋아요 API
        * 마지막 수정 날짜 : 20.09.11
        */
        case 'postLike':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('mood');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $body['status'] = $body['mood'];
            $tmp = array_merge($header, $body);

            $pathVar = isValidPathVar($vars['postID'], 'post');
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
            postLike($data);
            break;

        /*
        * API No. 30
        * API Name : 댓글 작성 API
        * 마지막 수정 날짜 : 20.09.11
        */
        case 'postComment':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('post', 'reply');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $tmp = array_merge($header, $body);

            if(!isset($_GET['comment'])){
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
                postComment($tmp);
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

            $queryString = isValidQueryString('comment');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }

            $data = array_merge($tmp, $queryString);
            postReComment($data);
            break;

        /*
        * API No. 31
        * API Name : 댓글 삭제 API
        * 마지막 수정 날짜 : 20.09.11
        */
        case 'deleteComment':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $pathVar = isValidPathVar($vars['commentID'], 'comment');
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $pathVar);
            deleteComment($data);
            break;

        /*
        * API No. 32
        * API Name : 댓글 수정 API
        * 마지막 수정 날짜 : 20.09.11
        */
        case 'updateComment':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('comment', 'reply');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $body);
            updateComment($data);
            break;

        /*
        * API No. 33
        * API Name : 댓글 리스트 조회 API
        * 마지막 수정 날짜 : 20.09.11
        */
        case 'getComment':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $pathVar = isValidPathVar($vars['postID'], 'post');
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($header, $pathVar);

            $queryString = isValidQueryString('page');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $data['page'] = ($queryString['queryString'] - 1)*20;

            $result = getComment($data);
            if(empty($result)){
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = '성공적으로 조회되었습니다.';
                echo json_encode($res);
            }
            else if($result == 103){
                $res->isSuccess = false;
                $res->code = 103;
                $res->message = '게시물을 볼 수 있는 권한이 없습니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
            }
            else{
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = '성공적으로 조회되었습니다.';
                echo json_encode($res);
            }
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
