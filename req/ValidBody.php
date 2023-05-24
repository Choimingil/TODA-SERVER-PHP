<?php

function isValidBody($req, $rightKey){
    if(empty($req)){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '모든 body가 다 비었습니다.';
        return $res;
    }
    $dataKey = array_intersect(array_keys($req),$rightKey);
    $data = asort($dataKey);
    $right = asort($rightKey);
    $dataKey = null;

    if($data != $right){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '빠진 Body가 있습니다.';
        return $res;
    }
//    $koreanPattern = "/[\xA1-\xFE\xA1-\xFE]/";
    foreach($req as $key => $value){
        switch ($key) {
            case 'email':
                $pattern = '/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i';
                if (!preg_match($pattern, $value)){
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 이메일 형식입니다.';
                    return $res;
                }
                else if (isValidEmail($value)){
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 104;
                    $res['message'] = '이미 존재하는 이메일입니다.';
                    return $res;
                }
                else if ($value == 'quited'){
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '회원탈퇴 후 30일이 경과되어 데이터가 파기되었습니다.';
                    return $res;
                }
                else continue 2;

            case 'id':
                $pattern = '/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i';
                if (!preg_match($pattern, $value)){
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 이메일 형식입니다.';
                    return $res;
                }
                else if (!isValidEmail($value)){
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 104;
                    $res['message'] = '존재하지 않는 이메일입니다.';
                    return $res;
                }
                else continue 2;

            case 'password':
            case 'pw':
                if (mb_strlen($value, 'UTF-8') <= 7){
//                if (!preg_match($koreanPattern,$value) || mb_strlen($value, 'UTF-8') <= 8){
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 비밀번호 형식입니다.';
                    return $res;
                }
                else continue 2;

            case 'name':
//                if (mb_strlen($value, 'UTF-8') > 7){
//                    $res['isSuccess'] = FALSE;
//                    $res['code'] = 103;
//                    $res['message'] = '잘못된 이름 형식입니다.';
//                    return $res;
//                } else continue 2;
                continue 2;

            case 'token':
            case 'fcmToken':
                if (!ctype_print($value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 토큰 형식입니다.';
                    return $res;
                } else continue 2;

            case 'image':
                if (!ctype_print($value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 이미지 형식입니다.';
                    return $res;
                } else continue 2;

            case 'imageList':
                if (gettype($value) != 'array' || sizeof($value)>3) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 이미지 리스트 형식입니다.(이미지 3장 제한)';
                    return $res;
                } else continue 2;

            case 'isAllowed':
                if ($value != 'Y' && $value != 'N') {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 입력입니다.';
                    return $res;
                } else continue 2;

            case 'birth':
            case 'date':
                if (!preg_match("/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/", $value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 날짜 형식입니다.';
                    return $res;
                } else continue 2;

            case 'color':
                if (!codeToColor($value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 104;
                    $res['message'] = '존재하지 않는 색입니다.';
                    return $res;
                } else continue 2;

            case 'diary':
                if (!isValidDiary($value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 104;
                    $res['message'] = '존재하는 다이어리가 아닙니다.';
                    return $res;
                } else continue 2;

            case 'background':
                if (!isValidBackground($value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 104;
                    $res['message'] = '존재하지 않는 속지입니다.';
                    return $res;
                } else continue 2;

            case 'title':
//                // string 입력 제한 해제(name도 같이)
//                if (mb_strlen($value, "UTF-8") > 20) {
//                    $res['isSuccess'] = FALSE;
//                    $res['code'] = 103;
//                    $res['message'] = '잘못된 제목 형식입니다.';
//                    return $res;
//                } else continue 2;
                continue 2;

            case 'notice':
                if (mb_strlen($value, "UTF-8") > 45) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 공지 형식입니다.(45글자 이내)';
                    return $res;
                } else continue 2;
            case 'reply':
            case 'text':
                continue 2;
            case 'font':
                if($value > 8 || $value < 1){
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 글꼴 형식입니다.';
                    return $res;
                } else continue 2;
            case 'time':
                if (!preg_match("/[0-9]{2}\:[0-9]{2}/", $value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 시간 형식입니다.';
                    return $res;
                } else continue 2;

            case 'aligned':
                if (!isValidAligned($value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 정렬 코드값입니다.';
                    return $res;
                } else continue 2;

            case 'mood':
                if (!isValidMood($value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 104;
                    $res['message'] = '존재하지 않는 감정입니다.';
                    return $res;
                } else continue 2;

            case 'post':
                if (!isValidPost($value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 104;
                    $res['message'] = '존재하는 게시물이 아닙니다.';
                    return $res;
                } else continue 2;

            case 'comment':
                if (!isValidComment($value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 104;
                    $res['message'] = '존재하지 않는 댓글입니다.';
                    return $res;
                } else continue 2;

            case 'schedule':
                if (!isValidSchedule($value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 104;
                    $res['message'] = '존재하지 않는 일정입니다.';
                    return $res;
                } else continue 2;

            // 수정 필요
            case 'point':
                if (!ctype_digit($value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 포인트 형식입니다.';
                    return $res;
                } else continue 2;

            case 'alarmType':
                if ($value != 0 && $value != 1 && $value != 2) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 알림 타입 형식입니다.';
                    return $res;
                } else continue 2;

            case 'stickerArr':
                if (gettype($value) != 'array') {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 스티커 입력 형식입니다.';
                    return $res;
                }
                else{
                    foreach ($value as $i=>$j){
                        if(
                            !isset($value[$i]['stickerID']) ||
                            !isset($value[$i]['device']) ||
                            !isset($value[$i]['x']) ||
                            !isset($value[$i]['y']) ||
                            !isset($value[$i]['rotate']) ||
                            !isset($value[$i]['scale']) ||
                            !isset($value[$i]['inversion']) ||
                            !isset($value[$i]['layerNum'])){
                            $res['isSuccess'] = FALSE;
                            $res['code'] = 103;
                            $res['message'] = '잘못된 스티커 입력 형식입니다.';
                            return $res;
                        }
                        else if(!isValidSticker($value[$i]['stickerID'])){
                            $res['isSuccess'] = FALSE;
                            $res['code'] = 103;
                            $res['message'] = '잘못된 스티커 아이디 입력입니다.';
                            return $res;
                        }
                        else if(
                            !isset($value[$i]['rotate']['a']) ||
                            !isset($value[$i]['rotate']['b']) ||
                            !isset($value[$i]['rotate']['c']) ||
                            !isset($value[$i]['rotate']['d']) ||
                            !isset($value[$i]['rotate']['tx']) ||
                            !isset($value[$i]['rotate']['ty'])){
                            $res['isSuccess'] = FALSE;
                            $res['code'] = 103;
                            $res['message'] = '잘못된 각도 입력입니다.';
                            return $res;
                        }
                        else if(
                            !isset($value[$i]['scale']['x']) ||
                            !isset($value[$i]['scale']['y']) ||
                            !isset($value[$i]['scale']['width']) ||
                            !isset($value[$i]['scale']['height'])){
                            $res['isSuccess'] = FALSE;
                            $res['code'] = 103;
                            $res['message'] = '잘못된 크기 입력입니다.';
                            return $res;
                        }
                        else if(!isValidInversion($value[$i]['inversion'])){
                            $res['isSuccess'] = FALSE;
                            $res['code'] = 103;
                            $res['message'] = '잘못된 반전 효과 입력입니다.';
                            return $res;
                        }
                        else if(($value[$i]['layerNum'])<=0){
                            $res['isSuccess'] = FALSE;
                            $res['code'] = 103;
                            $res['message'] = '잘못된 레이어 입력입니다.';
                            return $res;
                        }
                    }
                    continue 2;
                }

            case 'usedStickerArr':
                if (gettype($value) != 'array') {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 스티커 입력 형식입니다.';
                    return $res;
                }
                else{
                    foreach ($value as $i=>$j){
                        if(
                            !isset($value[$i]['usedStickerID']) ||
                            !isset($value[$i]['device']) ||
                            !isset($value[$i]['x']) ||
                            !isset($value[$i]['y']) ||
                            !isset($value[$i]['rotate']) ||
                            !isset($value[$i]['scale']) ||
                            !isset($value[$i]['inversion']) ||
                            !isset($value[$i]['layerNum'])){
                            $res['isSuccess'] = FALSE;
                            $res['code'] = 103;
                            $res['message'] = '잘못된 스티커 입력 형식입니다.';
                            return $res;
                        }
                        else if(!isValidStickerView($value[$i]['usedStickerID'])){
                            $res['isSuccess'] = FALSE;
                            $res['code'] = 103;
                            $res['message'] = '잘못된 스티커 아이디 입력입니다.';
                            return $res;
                        }
                        else if(
                            !isset($value[$i]['rotate']['a']) ||
                            !isset($value[$i]['rotate']['b']) ||
                            !isset($value[$i]['rotate']['c']) ||
                            !isset($value[$i]['rotate']['d']) ||
                            !isset($value[$i]['rotate']['tx']) ||
                            !isset($value[$i]['rotate']['ty'])){
                            $res['isSuccess'] = FALSE;
                            $res['code'] = 103;
                            $res['message'] = '잘못된 각도 입력입니다.';
                            return $res;
                        }
                        else if(
                            !isset($value[$i]['scale']['x']) ||
                            !isset($value[$i]['scale']['y']) ||
                            !isset($value[$i]['scale']['width']) ||
                            !isset($value[$i]['scale']['height'])){
                            $res['isSuccess'] = FALSE;
                            $res['code'] = 103;
                            $res['message'] = '잘못된 크기 입력입니다.';
                            return $res;
                        }
                        else if(!isValidInversion($value[$i]['inversion'])){
                            $res['isSuccess'] = FALSE;
                            $res['code'] = 103;
                            $res['message'] = '잘못된 반전 효과 입력입니다.';
                            return $res;
                        }
                        else if(($value[$i]['layerNum'])<=0){
                            $res['isSuccess'] = FALSE;
                            $res['code'] = 103;
                            $res['message'] = '잘못된 레이어 입력입니다.';
                            return $res;
                        }
                    }
                    continue 2;
                }

            case 'userCode':
                if (!isValidUserCode($value)) {
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 사용자 코드값입니다.';
                    return $res;
                } else continue 2;

            case 'status':
                if(!isValidStatus($value)){
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '존재하지 않는 코드입니다.';
                    return $res;
                } else continue 2;

            case 'appPW':
                if (!ctype_digit($value) || mb_strlen($value, "UTF-8") != 4){
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 앱 비밀번호 형식입니다.';
                    return $res;
                } else continue 2;

            default:
                $res['isSuccess'] = FALSE;
                $res['code'] = 104;
                $res['message'] = '존재하지 않는 형식입니다.';
                return $res;
        }
    }
    $res = $req;
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '바디값 성공.';
    return $res;
}