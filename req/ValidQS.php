<?php

function isValidQueryString($keyword){
//    if($keyword=='page' && !isset($_GET[$keyword])){
    if(!isset($_GET[$keyword])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '쿼리스트링이 등록되지 않았습니다.';
        return $res;
    }
    $value = $_GET[$keyword];
    switch ($keyword){
        case 'type':
            if($value!=1 && $value!=2){
                $res['isSuccess'] = FALSE;
                $res['code'] = 103;
                $res['message'] = "잘못된 쿼리스트링 타입입니다.(type)";
                return $res;
            }
            break;
        case 'page':
        case 'num':
            if(!ctype_digit($value) && $value < 1){
                $res['isSuccess'] = FALSE;
                $res['code'] = 103;
                $res['message'] = "잘못된 쿼리스트링 타입입니다.(page)";
                return $res;
            }
            break;

        case 'post':
            if(!ctype_digit($value)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 103;
                $res['message'] = '잘못된 쿼리스트링 타입입니다.(post)';
                return $res;
            }
            if(!isValidPost($value)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 102;
                $res['message'] = '존재하지 않는 게시물입니다.';
                return $res;
            }
            break;

        case 'diary':
            if(!ctype_digit($value)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 103;
                $res['message'] = '잘못된 쿼리스트링 타입입니다.(diary)';
                return $res;
            }
            if(!isValidDiary($value)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 102;
                $res['message'] = '존재하지 않는 다이어리입니다.';
                return $res;
            }
            break;

        case 'fcmToken':
            if(!isExistOnlyToken($value)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 102;
                $res['message'] = '존재하지 않는 토큰입니다.';
                return $res;
            }
            break;

        case 'comment':
            if(!ctype_digit($value)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 103;
                $res['message'] = '잘못된 쿼리스트링 타입입니다.(comment)';
                return $res;
            }
            if(!isValidComment($value)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 102;
                $res['message'] = '존재하지 않는 댓글입니다.';
                return $res;
            }
            break;

        case 'sticker':
            if(!ctype_digit($value)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 103;
                $res['message'] = '잘못된 쿼리스트링 타입입니다.(keyword)';
                return $res;
            }
            if(!isValidSticker($value)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 102;
                $res['message'] = '존재하지 않는 스티커입니다.';
                return $res;
            }
            break;

        case 'alarm':
            if($value != 1){
                $res['isSuccess'] = FALSE;
                $res['code'] = 103;
                $res['message'] = '잘못된 쿼리스트링 타입입니다.(alarm)';
                return $res;
            }
            break;

        case 'keyword':
        case 'version':
            break;

        case 'date':
            if (!preg_match("/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/", $value)) {
                $res['isSuccess'] = FALSE;
                $res['code'] = 103;
                $res['message'] = '잘못된 날짜 형식입니다.';
                return $res;
            }
            break;

        case 'status':
            if(!isValidStatus((int)$value)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 102;
                $res['message'] = '존재하지 않는 코드입니다.';
                return $res;
            }
            break;
    }
    // 벨리데이션을 통해서 위처럼 거르고 최종 결과물은 함수값
    $res['queryString'] = $value;
    $res['isSuccess'] = True;
    $res['code'] = 100;
    $res['message'] = '쿼리스트링 조회 성공';
    return $res;
}