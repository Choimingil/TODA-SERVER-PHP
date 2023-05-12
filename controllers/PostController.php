<?php
requires();

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API No. 16
        * API Name : 게시물 작성 API
        * 마지막 수정 날짜 : 20.09.06
        */
        case 'addPostOld':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('diary', 'title', 'text', 'mood', 'background', 'aligned');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $body['status'] = $body['background']*100 + $body['mood'];
            $data = array_merge($header, $body);
            addPostOld($data);
            break;

        /*
        * API No. 16-1
        * API Name : 게시물 작성 API(이미지 추가)
        * 마지막 수정 날짜 : 20.09.06
        */
        case 'addPost':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('diary', 'title', 'text', 'mood', 'background', 'aligned', 'imageList');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isset($_GET['type'])){
                $header['type'] = 1;
            }
            else{
                $queryString = isValidQueryString('type');
                if($queryString['isSuccess'] == false){
                    echo json_encode($queryString, JSON_NUMERIC_CHECK);
                    return;
                }
                $header['type'] = $queryString['queryString'];
            }

            $body['status'] = $body['background']*100 + $body['mood'];
            $data = array_merge($header, $body);

            if(isset($_GET['date'])){
                $queryString = isValidQueryString('date');
                if($queryString['isSuccess'] == false){
                    echo json_encode($queryString, JSON_NUMERIC_CHECK);
                    return;
                }
                $data['date'] = $queryString['queryString'];
            }
            addPost($data);
            break;

        /*
        * API No. 16-2
        * API Name : 게시물 작성 API(날짜 및 폰트 추가)
        * 마지막 수정 날짜 : 21.05.11
        */

        case 'addPostVer3':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('diary', 'date', 'title', 'text', 'mood', 'background', 'aligned', 'font', 'imageList');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $body['status'] = $body['background']*100 + $body['mood'];
            $body['statusText'] = $body['aligned']*100 + $body['font'];
            $data = array_merge($header, $body);
            addPostVer3($data);
            break;

        /*
        * API No. 17
        * API Name : 게시물 삭제 API
        * 마지막 수정 날짜 : 20.09.10
        */
        case 'deletePost':
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
            deletePost($data);
            break;

        /*
        * API No. 18
        * API Name : 게시물 수정 API(구버전)
        * 마지막 수정 날짜 : 20.09.10
        */
        case 'updatePostOld':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('post', 'title', 'text', 'mood', 'background', 'aligned');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $body['status'] = $body['background']*100 + $body['mood'];

            $data = array_merge($header, $body);
            updatePostOld($data);
            break;

        /*
        * API No. 18-1
        * API Name : 게시물 수정 API
        * 마지막 수정 날짜 : 20.09.10
        */
        case 'updatePost':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('post', 'title', 'text', 'mood', 'background', 'aligned', 'imageList');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $body['status'] = $body['background']*100 + $body['mood'];

            $data = array_merge($header, $body);

            if(isset($_GET['date'])){
                $queryString = isValidQueryString('date');
                if($queryString['isSuccess'] == false){
                    echo json_encode($queryString, JSON_NUMERIC_CHECK);
                    return;
                }
                $data['date'] = $queryString['queryString'];
            }

            updatePost($data);
            break;

        /*
        * API No. 18-2
        * API Name : 게시물 수정 API(날짜와 폰트)
        * 마지막 수정 날짜 : 20.09.10
        */
        case 'updatePostVer3':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('post', 'date', 'title', 'text', 'mood', 'background', 'aligned', 'font', 'imageList');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $body['status'] = $body['background']*100 + $body['mood'];
            $body['statusText'] = $body['aligned']*100 + $body['font'];
            $data = array_merge($header, $body);
            updatePostVer3($data);
            break;

        /*
        * API No. 19
        * API Name : 게시물 리스트 조회 API
        * 마지막 수정 날짜 : 20.09.10
        */
        case 'getPostList':
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

            $queryString = isValidQueryString('page');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }

            $data['page'] = ($queryString['queryString'] - 1)*20;
            if($data['page']<0){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = '잘못된 페이지입니다.';
                echo json_encode($res);
                return;
            }

            if(!isset($_GET['post'])){
                $result = getPostList($data);
                if(empty($result)){
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = '성공적으로 조회되었습니다.';
                    echo json_encode($res);
                    return;
                }
                else if($result == 102){
                    $res->isSuccess = FALSE;
                    $res->code = 102;
                    $res->message = '다이어리에 등록된 유저가 아닙니다.';
                    echo json_encode($res);
                    return;
                }
                else{
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = '성공적으로 조회되었습니다.';
                    echo json_encode($res);
                    return;
                }
            }
            $queryString = isValidQueryString('post');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $data['post'] = $queryString['queryString'];
            $result = getPostList($data);
            switch ($result){
                case 102:
                    $res->isSuccess = FALSE;
                    $res->code = $result;
                    $res->message = '다이어리에 등록된 유저가 아닙니다.';
                    echo json_encode($res);
                    break;
                default:
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = '성공적으로 조회되었습니다.';
                    echo json_encode($res);
                    break;
            }
            break;

        /*
        * API No. 19-0
        * API Name : 게시물 리스트 조회 API(날짜)
        * 마지막 수정 날짜 : 20.09.10
        */
        case 'getPostListNew':
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

            $queryString = isValidQueryString('num');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $data['num'] = $queryString['queryString'];

            $queryString = isValidQueryString('date');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $data['date'] = $queryString['queryString'];

            if(!isset($_GET['post'])){
                $result = getPostListDate($data);
                switch ($result){
                    case 102:
                        $res->isSuccess = FALSE;
                        $res->code = $result;
                        $res->message = '다이어리에 등록된 유저가 아닙니다.';
                        echo json_encode($res);
                        break;
                    default:
                        $res->result = $result;
                        $res->isSuccess = TRUE;
                        $res->code = 100;
                        $res->message = '성공적으로 조회되었습니다.';
                        echo json_encode($res);
                        break;
                }
                return;
            }
            $queryString = isValidQueryString('post');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $data['post'] = $queryString['queryString'];
            $result = getPostListDate($data);
            switch ($result){
                case 102:
                    $res->isSuccess = FALSE;
                    $res->code = $result;
                    $res->message = '다이어리에 등록된 유저가 아닙니다.';
                    echo json_encode($res);
                    break;
                default:
                    $res->result = $result;
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = '성공적으로 조회되었습니다.';
                    echo json_encode($res);
                    break;
            }
            break;

        /*
        * API No. 19-1
        * API Name : 게시물 날짜별 개수 조회 API
        * 마지막 수정 날짜 : 21.01.18
        */
        case 'getPostNumByDate':
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

            $queryString = isValidQueryString('page');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }

            $data = array_merge($header, $pathVar);
            $data['page'] = ($queryString['queryString'] - 1)*20;
            getPostNumByDate($data);
            break;

        /*
        * API No. 20
        * API Name : 게시물 상세 조회 API
        * 마지막 수정 날짜 : 20.09.10
        */
        case 'getPostDetail':
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
            getPostDetail($data);
            break;

        /*
        * API No. 20-1
        * API Name : 게시물 상세 조회 API(날짜 및 폰트 추가 버전)
        * 마지막 수정 날짜 : 21.05.12
        */
        case 'getPostDetailVer2':
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
            getPostDetailVer2($data);
            break;

        /*
        * API No. 16-4
        * API Name : 게시글 임시저장 여부 설정 API
        * 마지막 수정 날짜 : 21.01.21
        */
        case 'addTmp':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('type', 'typeID', 'isWriting');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $body);
            addTmp($data);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
