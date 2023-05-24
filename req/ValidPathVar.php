<?php

function isValidPathVar($var, $type){
    if(!isset($var)){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '빠진 PathVariable이 있습니다.';
        return $res;
    }
    switch ($type){
        case 'version':
//            if($type != '1.0.8' && $type != '1.0.9'){
//                $res['isSuccess'] = FALSE;
//                $res['code'] = 104;
//                $res['message'] = '잘못된 버전입니다.';
//                return $res;
//            }
            break;
        case 'friend':
            if(!isValidID($var)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 104;
                $res['message'] = '존재하는 사용자가 아닙니다.';
                return $res;
            }
            break;
        case 'diary':
            if(!isValidDiary($var)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 104;
                $res['message'] = '존재하지 않는 다이어리입니다.';
                return $res;
            }
            break;
        case 'code':
            if(!isValidCode($var)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 103;
                $res['message'] = '잘못된 코드입니다.';
                return $res;
            }
            break;
        case 'userCode':
            if(!isValidUserCode($var)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 103;
                $res['message'] = '잘못된 코드입니다.';
                return $res;
            }
            break;
        case 'post':
            if(!isValidPost($var)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 104;
                $res['message'] = '존재하지 않는 게시물입니다.';
                return $res;
            }
            break;
        case 'comment':
            if(!isValidComment($var)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 104;
                $res['message'] = '존재하지 않는 댓글입니다.';
                return $res;
            }
            break;
        case 'schedule':
            if(!isValidSchedule($var)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 104;
                $res['message'] = '존재하지 않는 일정입니다.';
                return $res;
            }
            break;
        case 'stickerPack':
            if(!isValidStickerPack($var)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 104;
                $res['message'] = '존재하지 않는 스티커팩입니다.';
                return $res;
            }
            break;
        case 'announcement':
            if(!isValidAnnouncement($var)){
                $res['isSuccess'] = FALSE;
                $res['code'] = 104;
                $res['message'] = '존재하지 않는 공지사항입니다.';
                return $res;
            }
            break;
    }
    $res['pathVar'] = $var;
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = 'PathVariable값 성공';
    return $res;
}