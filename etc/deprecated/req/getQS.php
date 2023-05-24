<?php

function getQS($keyword){
    if(!isset($_GET[$keyword])) return new DefaultResponse(FALSE,102,'쿼리스트링이 등록되지 않았습니다.');

    $value = $_GET[$keyword];
    $datePattern = "/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/";

    switch ($keyword){
        case 'type':
            if($value!=1 && $value!=2) return new DefaultResponse(FALSE,103,'잘못된 쿼리스트링 타입입니다.(type)');
            break;
        case 'page':
        case 'num':
            if(!ctype_digit($value) && $value < 1) return new DefaultResponse(FALSE,103,'잘못된 쿼리스트링 타입입니다.(page)');
            break;
        case 'post':
            if(!ctype_digit($value)) return new DefaultResponse(FALSE,103,'잘못된 쿼리스트링 타입입니다.(post)');
            else if(!isValidPost($value)) return new DefaultResponse(FALSE,102,'존재하지 않는 게시물입니다.');
            break;
        case 'diary':
            if(!ctype_digit($value)) return new DefaultResponse(FALSE,103,'잘못된 쿼리스트링 타입입니다.(diary)');
            else if(!isValidDiary($value)) return new DefaultResponse(FALSE,102,'존재하지 않는 다이어리입니다.');
            break;
        case 'fcmToken':
            if(!isExistOnlyToken($value)) return new DefaultResponse(FALSE,102,'존재하지 않는 토큰입니다.');
            break;
        case 'comment':
            if(!ctype_digit($value)) return new DefaultResponse(FALSE,103,'잘못된 쿼리스트링 타입입니다.(comment)');
            else if(!isValidComment($value)) return new DefaultResponse(FALSE,102,'존재하지 않는 댓글입니다.');
            break;
        case 'sticker':
            if(!ctype_digit($value)) return new DefaultResponse(FALSE,103,'잘못된 쿼리스트링 타입입니다.(keyword)');
            else if(!isValidSticker($value)) return new DefaultResponse(FALSE,102,'존재하지 않는 스티커입니다.');
            break;
        case 'alarm':
            if($value != 1) return new DefaultResponse(FALSE,103,'잘못된 쿼리스트링 타입입니다.(alarm)');
            break;
        case 'keyword':
        case 'version':
            break;
        case 'date':
            if (!preg_match($datePattern, $value)) return new DefaultResponse(FALSE,103,'잘못된 날짜 형식입니다.');
            break;
        case 'status':
            if(!isValidStatus($value || $value==3)) return new DefaultResponse(FALSE,102,'존재하지 않는 코드입니다.');
            break;
    }
    return $value;
}