<?php

function createSimpleJwt($jwt, $code){
    switch ($code){
        case 1:
            $opts = array(
                CURLOPT_URL => KAKAO_OAUTH_URL,
//                CURLOPT_POST => true,
//                CURLOPT_POSTFIELDS => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array( 'Authorization: Bearer ' . $jwt )
            );

            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $result=json_decode(curl_exec($ch),true);
            curl_close($ch);

            if(!isset($result['id'])){
                $res['isSuccess'] = FALSE;
                $res['code'] = 103;
                $res['message'] = '잘못되거나 만료된 토큰입니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if(isValidKakao($result['id'])){
                $data['kakao'] = $result['id'];
                $jwt = getJWTokenKakao($data, JWT_SECRET_KEY);
                $res['result'] = $jwt;
                $res['isSuccess'] = TRUE;
                $res['code'] = 100;
                $res['message'] = '성공적으로 로그인되었습니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            createUserKakao($result['id']);
            $data['kakao'] = $result['id'];
            $jwt = getJWTokenKakao($data, JWT_SECRET_KEY);
            $res['result'] = $jwt;
            $res['isSuccess'] = TRUE;
            $res['code'] = 200;
            $res['message'] = '인증되었습니다. 정보 기입 화면으로 넘어갑니다.';
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;
//        case 2:
//            $opts = array(
//                CURLOPT_URL => NAVER_OAUTH_URL,
////                CURLOPT_POST => true,
////                CURLOPT_POSTFIELDS => false,
//                CURLOPT_RETURNTRANSFER => true,
//                CURLOPT_HTTPHEADER => array( "Authorization: Bearer " . $jwt )
//            );
//
//            $ch = curl_init();
//            curl_setopt_array($ch, $opts);
//            $result=json_decode(curl_exec($ch),true);
//            curl_close($ch);
//
//
//            $res[0] = TRUE;
//            $res[1] = $result;
//            return $res;
//        case 3:
//            $opts = array(
//                CURLOPT_URL => GOOGLE_OAUTH_URL."?access_token=".$jwt,
////                CURLOPT_POST => true,
////                CURLOPT_POSTFIELDS => false,
//                CURLOPT_RETURNTRANSFER => true
////                CURLOPT_HTTPHEADER => array( "Authorization: Bearer " . $code )
//            );
//
//            $ch = curl_init();
//            curl_setopt_array($ch, $opts);
//            $result=json_decode(curl_exec($ch),true);
//            curl_close($ch);
//
//            if(!isset($result['id'])){
//                $res[0] = FALSE;
//                $res[1] = 101;
//                $res[2] = "잘못되거나 만료된 토큰입니다.";
//                return $res;
//            }
//
//            if(isExistGoogle($result['id'])){
//                $data['id'] = $result['id'];
//                $data['type'] = $code;
//                $jwt = getJWToken($data, JWT_SECRET_KEY);
//                $res[0] = TRUE;
//                $res[1] = $jwt;
//                return $res;
//            }
//
//            createUserGoogle($result['id']);
//            $data['id'] = $result['id'];
//            $data['type'] = $code;
//            $jwt = getJWToken($data, JWT_SECRET_KEY);
//            $res[0] = TRUE;
//            $res[1] = $jwt;
////            $res[1] = $result['id'];
//            return $res;
        default:
            $res[0] = FALSE;
            $res[1] = 103;
            $res[2] = '존재하지 않는 로그인 방식입니다.';
            return $res;
    }
}

function postPoint($data){
    if($data['point']<0){
        $res['isSuccess'] = FALSE;
        $res['code'] = 319;
        $res['message'] = "잘못된 포인트 사용입니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    $query = "select point from User where ID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$data['id']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $value = $st->fetchAll();

    $st = null;

    $point = $data['point'] + $value[0]['point'];

    $query = "UPDATE User SET point = ? WHERE ID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$point, $data['id']]);

    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "포인트가 적립되었습니다.";
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}

function buySticker($data){
    if(isValidUserSticker($data['id'], $data['sticker'])){
        $res['isSuccess'] = false;
        $res['code'] = 520;
        $res['message'] = "이미 구매한 스티커입니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $pdo = pdoSqlConnect();
    $query = "select point from User where ID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$data['id']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $userPoint = $st->fetchAll();
    $st = null;

    $query = "select point from Sticker where ID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$data['sticker']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $stickerPoint = $st->fetchAll();
    $st = null;

    if($userPoint[0]['point'] < $stickerPoint[0]['point']){
        $res['isSuccess'] = false;
        $res['code'] = 105;
        $res['message'] = "보유 포인트가 부족합니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $remainPoint = $userPoint[0]['point'] - $stickerPoint[0]['point'];

    $query = "INSERT INTO UserSticker (userID, stickerID) VALUES (?, ?);";
    $st = $pdo->prepare($query);
    $st->execute([$data['id'], $data['sticker']]);
    $st = null;

    $query = "UPDATE User SET point = ? WHERE ID = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$remainPoint, $data['id']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "스티커 구매가 완료되었습니다.";
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function addSchedule($data){
    if(!isDiaryUser($data['id'], $data['diary'])){
        $res['isSuccess'] = false;
        $res['code'] = 401;
        $res['message'] = "다이어리에 등록된 유저가 아닙니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Calender (diaryID, date, name) VALUES (?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$data['diary'], $data['date'], $data['title']]);

    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "일정이 추가되었습니다.";
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}

function deleteSchedule($data){
    if(!isDiaryUserSchedule($data['id'],$data['pathVar'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 418;
        $res['message'] = "일정에 접근할 수 있는 권한이 없습니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    $query = "UPDATE Calender SET status = 0 WHERE ID = ?";

    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "일정이 삭제되었습니다.";
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}

function updateSchedule($data){
    if(!isDiaryUserSchedule($data['id'],$data['schedule'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 418;
        $res['message'] = "일정에 접근할 수 있는 권한이 없습니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    $query = "UPDATE Calender SET date = ?, name = ? WHERE ID = ?";

    $st = $pdo->prepare($query);
    $st->execute([$data['date'], $data['title'], $data['schedule']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "일정이 수정되었습니다.";
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}