<?php

function getPathVar($vars,$type){
    if(!isset($vars[$type])) return new DefaultResponse(FALSE,102,'빠진 PathVariable이 있습니다.');
    else $var = $vars[$type];
    switch ($type){
        case 'version':
            break;
        case 'friend':
            if(!isValidID($var)) return new DefaultResponse(FALSE,104,'존재하는 사용자가 아닙니다.');
            break;
        case 'diary':
            if(!isValidDiary($var)) return new DefaultResponse(FALSE,104,'존재하지 않는 다이어리입니다.');
            break;
        case 'code':
            if(!isValidCode($var)) return new DefaultResponse(FALSE,103,'잘못된 코드입니다.');
            break;
        case 'userCode':
            if(!isValidUserCode($var)) return new DefaultResponse(FALSE,103,'잘못된 코드입니다.');
            break;
        case 'post':
            if(!isValidPost($var)) return new DefaultResponse(FALSE,104,'존재하지 않는 게시물입니다.');
            break;
        case 'comment':
            if(!isValidComment($var)) return new DefaultResponse(FALSE,104,'존재하지 않는 댓글입니다.');
            break;
        case 'schedule':
            if(!isValidSchedule($var)) return new DefaultResponse(FALSE,104,'존재하지 않는 일정입니다.');
            break;
        case 'stickerPack':
            if(!isValidStickerPack($var)) return new DefaultResponse(FALSE,104,'존재하지 않는 스티커팩입니다.');
            break;
        case 'announcement':
            if(!isValidAnnouncement($var)) return new DefaultResponse(FALSE,104,'존재하지 않는 공지사항입니다.');
            break;
    }
    return $var;
}