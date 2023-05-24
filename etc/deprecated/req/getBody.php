<?php

function getBody($req, $rightKey){
    if(empty($req)) return new DefaultResponse(FALSE,102,'모든 body가 다 비었습니다.');

    $emailPattern = '/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i';
    $datePattern = "/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/";
    $timePattern = "/[0-9]{2}\:[0-9]{2}/";
//    $koreanPattern = "/[\xA1-\xFE\xA1-\xFE]/";

    $dataKey = array_intersect(array_keys($req),$rightKey);
    $data = asort($dataKey);
    $right = asort($rightKey);
    $dataKey = null;

    if($data != $right) return new DefaultResponse(FALSE,102,'빠진 Body가 있습니다.');

    foreach($req as $key => $value){
        switch ($key) {
            case 'email':
                if (!preg_match($emailPattern, $value)) return new DefaultResponse(FALSE,103,'잘못된 이메일 형식입니다.');
                else if (isValidEmail($value)) return new DefaultResponse(FALSE,104,'이미 존재하는 이메일입니다.');
                else continue 2;
            case 'id':
                if (!preg_match($emailPattern, $value)) return new DefaultResponse(FALSE,103,'잘못된 이메일 형식입니다.');
                else if (!isValidEmail($value)) return new DefaultResponse(FALSE,104,'존재하지 않는 이메일입니다.');
                else continue 2;
            case 'password':
            case 'pw':
                if (mb_strlen($value, 'UTF-8') <= 8) return new DefaultResponse(FALSE,103,'잘못된 비밀번호 형식입니다.');
                else continue 2;
            case 'name':
            case 'userName':
            case 'diaryName':
                continue 2;
            case 'token':
            case 'fcmToken':
                if (!ctype_print($value))  return new DefaultResponse(FALSE,103,'잘못된 토큰 형식입니다.');
                else continue 2;
            case 'userList':
            case 'tokenList':
            case 'deviceList':
                if(gettype($value) != 'array') return new DefaultResponse(FALSE,103,'잘못된 리스트 형식입니다.');
                else continue 2;
            case 'image':
                if (!ctype_print($value)) return new DefaultResponse(FALSE,103,'잘못된 이미지 형식입니다.');
                else continue 2;
            case 'imageList':
                if (gettype($value) != 'array' || sizeof($value)>3) return new DefaultResponse(FALSE,103,'잘못된 이미지 리스트 형식입니다.(이미지 3장 제한)');
                else continue 2;
            case 'isAllowed':
                if ($value != 'Y' && $value != 'N') return new DefaultResponse(FALSE,103,'잘못된 입력입니다.');
                else continue 2;
            case 'birth':
            case 'date':
                if (!preg_match($datePattern, $value)) return new DefaultResponse(FALSE,103,'잘못된 날짜 형식입니다.');
                else continue 2;
            case 'color':
                if (!codeToColor($value)) return new DefaultResponse(FALSE,104,'존재하지 않는 색입니다.');
                else continue 2;
            case 'diary':
                if (!isValidDiary($value)) return new DefaultResponse(FALSE,104,'존재하는 다이어리가 아닙니다.');
                else continue 2;
            case 'diaryID':
            case 'postID':
            case 'commentID':
            case 'announcementID':
                if (gettype($value) != 'integer') return new DefaultResponse(FALSE,103,'잘못된 아이디 형식입니다.');
                else continue 2;
            case 'background':
                if (!isValidBackground($value)) return new DefaultResponse(FALSE,104,'존재하지 않는 속지입니다.');
                else continue 2;
            case 'title':
                continue 2;
            case 'notice':
                if (mb_strlen($value, "UTF-8") > 45) return new DefaultResponse(FALSE,103,'잘못된 공지 형식입니다.(45글자 이내)');
                else continue 2;
            case 'reply':
            case 'text':
                continue 2;
            case 'font':
                if($value > 8 || $value < 1) return new DefaultResponse(FALSE,103,'잘못된 글꼴 형식입니다.');
                else continue 2;
            case 'time':
                if (!preg_match($timePattern, $value)) return new DefaultResponse(FALSE,103,'잘못된 시간 형식입니다.');
                else continue 2;
            case 'aligned':
                if (!isValidAligned($value)) return new DefaultResponse(FALSE,103,'잘못된 정렬 코드값입니다.');
                else continue 2;
            case 'mood':
                if (!isValidMood($value)) return new DefaultResponse(FALSE,104,'존재하지 않는 감정입니다.');
                else continue 2;
            case 'post':
                if (!isValidPost($value)) return new DefaultResponse(FALSE,104,'존재하는 게시물이 아닙니다.');
                else continue 2;
            case 'comment':
                if (!isValidComment($value)) return new DefaultResponse(FALSE,104,'존재하지 않는 댓글입니다.');
                else continue 2;
            case 'schedule':
                if (!isValidSchedule($value)) return new DefaultResponse(FALSE,104,'존재하지 않는 일정입니다.');
                else continue 2;
            case 'point':
                if (gettype($value) != 'integer' || $value < 0) return new DefaultResponse(FALSE,103,'잘못된 포인트 형식입니다.');
                else continue 2;
            case 'alarmType':
            case 'type':
                if ($value != 0 && $value != 1 && $value != 2) return new DefaultResponse(FALSE,103,'잘못된 알림 타입 형식입니다.');
                else continue 2;
            case 'device':
                if ($value != 100 && $value != 200) return new DefaultResponse(FALSE,103,'잘못된 device 타입 형식입니다.');
                else continue 2;
            case 'stickerArr':
            case 'usedStickerArr':
                if (gettype($value) != 'array') return new DefaultResponse(FALSE,103,'잘못된 스티커 입력 형식입니다.');
                else continue 2;
            case 'userCode':
                if (!isValidUserCode($value)) return new DefaultResponse(FALSE,103,'잘못된 사용자 코드값입니다.');
                else continue 2;
            case 'status':
                if(!isValidStatus($value)) return new DefaultResponse(FALSE,103,'존재하지 않는 코드입니다.');
                else continue 2;
            case 'diaryStatus':
                if(!isValidDiaryStatus($value)) return new DefaultResponse(FALSE,103,'존재하지 않는 status입니다.');
                else continue 2;
            case 'appPW':
                if (!ctype_digit($value) || mb_strlen($value, "UTF-8") != 4) return new DefaultResponse(FALSE,103,'잘못된 앱 비밀번호 형식입니다.');
                else continue 2;
            default:
                return new DefaultResponse(FALSE,104,'존재하지 않는 형식입니다.');
        }
    }
    return $req;
}