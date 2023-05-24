<?php
require './vendor/autoload.php';

function checkNotification($data){
    $pdo = pdoSqlConnect();
    if(isExistOnlyToken($data['token'])){
        if(isJustExistToken($data['id'],$data['token'])){
            $query = 'UPDATE Notification SET status = 0 WHERE userID in (select * from (select userID from Notification where token = ?) tmp) and token = ?;
                UPDATE Notification SET status = 100, isAllowed = ?, isRemindAllowed = ?, isEventAllowed = ? WHERE userID = ? and token = ?;';
            $st = $pdo->prepare($query);
            $st->execute([$data['token'],$data['token'],$data['isAllowed'],$data['isAllowed'],$data['isAllowed'],$data['id'],$data['token']]);
            $st = null;
        }
        else{
            $query = 'UPDATE Notification SET status = 0 WHERE userID in (select * from (select userID from Notification where token = ?) tmp) and token = ?;
                INSERT INTO Notification (userID, token, isAllowed, isRemindAllowed, isEventAllowed) VALUES (?,?,?,?,?);';
            $st = $pdo->prepare($query);
            $st->execute([$data['token'],$data['token'],$data['id'], $data['token'], $data['isAllowed'], $data['isAllowed'], $data['isAllowed']]);
            $st = null;
        }
    }
    else{
        if(isJustExistToken($data['id'],$data['token'])){
            $query = "UPDATE Notification SET status = 100, isAllowed = ?, isRemindAllowed = ?, isEventAllowed = ? WHERE userID = ? and token = ?;";
            $st = $pdo->prepare($query);
            $st->execute([$data['isAllowed'],$data['isAllowed'],$data['isAllowed'],$data['id'], $data['token']]);
            $st = null;
        }
        else{
            $query = "INSERT INTO Notification (userID, token, isAllowed, isRemindAllowed, isEventAllowed) VALUES (?,?,?,?,?);";
            $st = $pdo->prepare($query);
            $st->execute([$data['id'], $data['token'], $data['isAllowed'], $data['isAllowed'], $data['isAllowed']]);
            $st = null;
        }
    }
    $pdo = null;
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '토큰이 저장되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function checkNotificationAndroid($data){
    $pdo = pdoSqlConnect();
    if(isExistOnlyToken($data['token'])){
        if(isJustExistToken($data['id'],$data['token'])){
            $query = 'UPDATE Notification SET status = 0 WHERE userID in (select * from (select userID from Notification where token = ?) tmp) and token = ?;
                UPDATE Notification SET status = 200, isAllowed = ?, isRemindAllowed = ?, isEventAllowed = ? WHERE userID = ? and token = ?;';
            $st = $pdo->prepare($query);
            $st->execute([$data['token'],$data['token'],$data['isAllowed'],$data['isAllowed'],$data['isAllowed'],$data['id'],$data['token']]);
            $st = null;
        }
        else{
            $query = 'UPDATE Notification SET status = 0 WHERE userID in (select * from (select userID from Notification where token = ?) tmp) and token = ?;
                INSERT INTO Notification (userID, token, isAllowed, isRemindAllowed, isEventAllowed,status) VALUES (?,?,?,?,?,200);';
            $st = $pdo->prepare($query);
            $st->execute([$data['token'],$data['token'],$data['id'], $data['token'], $data['isAllowed'], $data['isAllowed'], $data['isAllowed']]);
            $st = null;
        }
    }
    else{
        if(isJustExistToken($data['id'],$data['token'])){
            $query = "UPDATE Notification SET status = 200, isAllowed = ?, isRemindAllowed = ?, isEventAllowed = ? WHERE userID = ? and token = ?;";
            $st = $pdo->prepare($query);
            $st->execute([$data['isAllowed'],$data['isAllowed'],$data['isAllowed'],$data['id'], $data['token']]);
            $st = null;
        }
        else{
            $query = "INSERT INTO Notification (userID, token, isAllowed, isRemindAllowed, isEventAllowed,status) VALUES (?,?,?,?,?,200);";
            $st = $pdo->prepare($query);
            $st->execute([$data['id'], $data['token'], $data['isAllowed'], $data['isAllowed'], $data['isAllowed']]);
            $st = null;
        }
    }
    $pdo = null;
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '토큰이 저장되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function checkAlarm($data){
    $pdo = pdoSqlConnect();
    if(!isExistToken($data['id'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '알림 토큰이 저장되어 있지 않습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $res['result'] = getTokenAllowed($data['id']);
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '성공적으로 조회되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function updateAlarm($data){
    $pdo = pdoSqlConnect();
    if(!isExistToken($data['id'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '알림 토큰이 저장되어있지 않습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    if(getTokenAllowed($data['id'])){
        $query = 'UPDATE Notification SET isAllowed = ? WHERE userID = ?';
        $st = $pdo->prepare($query);
        $st->execute(['N',$data['id']]);
        $st = null;
        $pdo = null;
        $res['isSuccess'] = TRUE;
        $res['code'] = 200;
        $res['message'] = '알림이 해제되었습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
    else{
        $query = 'UPDATE Notification SET isAllowed = ? WHERE userID = ?';
        $st = $pdo->prepare($query);
        $st->execute(['Y',$data['id']]);
        $st = null;
        $pdo = null;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '알림이 허용되었습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
}

function checkAlarmVer2($data){
    if(!isExistToken($data['id'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '알림 토큰이 저장되어 있지 않습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $result['isBasicAllowed'] = getTokenAllowedByDevice($data['id'],$data['queryString']);
    $result['isRemindAllowed'] = getRemindAllowedByDevice($data['id'],$data['queryString']);
    $result['isEventAllowed'] = getEventAllowedByDevice($data['id'],$data['queryString']);

    $res['result'] = $result;
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '성공적으로 조회되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function updateAlarmVer2($data){
    $pdo = pdoSqlConnect();
    if(!isExistToken($data['id'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '알림 토큰이 저장되어있지 않습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    switch ($data['alarmType']){
        case 0:
            if(getTokenAllowedByDevice($data['id'],$data['fcmToken'])){
                $query = 'UPDATE Notification SET isAllowed = ? WHERE userID = ? and token=?';
                $st = $pdo->prepare($query);
                $st->execute(['N',$data['id'],$data['fcmToken']]);
                $st = null;
                $pdo = null;
                $res['isSuccess'] = TRUE;
                $res['code'] = 200;
                $res['message'] = '알림이 해제되었습니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            else{
                $query = 'UPDATE Notification SET isAllowed = ? WHERE userID = ? and token=?';
                $st = $pdo->prepare($query);
                $st->execute(['Y',$data['id'],$data['fcmToken']]);
                $st = null;
                $pdo = null;
                $res['isSuccess'] = TRUE;
                $res['code'] = 100;
                $res['message'] = '알림이 허용되었습니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            break;
        case 1:
            if(getRemindAllowedByDevice($data['id'],$data['fcmToken'])){
                $query = 'UPDATE Notification SET isRemindAllowed = ? WHERE userID = ? and token=?';
                $st = $pdo->prepare($query);
                $st->execute(['N',$data['id'],$data['fcmToken']]);
                $st = null;
                $pdo = null;
                $res['isSuccess'] = TRUE;
                $res['code'] = 200;
                $res['message'] = '알림이 해제되었습니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            else{
                $query = 'UPDATE Notification SET isRemindAllowed = ? WHERE userID = ? and token=?';
                $st = $pdo->prepare($query);
                $st->execute(['Y',$data['id'],$data['fcmToken']]);
                $st = null;
                $pdo = null;
                $res['isSuccess'] = TRUE;
                $res['code'] = 100;
                $res['message'] = '알림이 허용되었습니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            break;
        case 2:
            if(getEventAllowedByDevice($data['id'],$data['fcmToken'])){
                $query = 'UPDATE Notification SET isEventAllowed = ? WHERE userID = ? and token=?';
                $st = $pdo->prepare($query);
                $st->execute(['N',$data['id'],$data['fcmToken']]);
                $st = null;
                $pdo = null;
                $res['isSuccess'] = TRUE;
                $res['code'] = 200;
                $res['message'] = '알림이 해제되었습니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            else{
                $query = 'UPDATE Notification SET isEventAllowed = ? WHERE userID = ? and token=?';
                $st = $pdo->prepare($query);
                $st->execute(['Y',$data['id'],$data['fcmToken']]);
                $st = null;
                $pdo = null;
                $res['isSuccess'] = TRUE;
                $res['code'] = 100;
                $res['message'] = '알림이 허용되었습니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            break;
    }
}

function getAlarmTime($data){
//    if(!getRemindAllowedByDevice($data['id'],$data['queryString'])){
//        $res['isSuccess'] = FALSE;
//        $res['code'] = 102;
//        $res['message'] = '리마인드 알림이 거절된 상태입니다.';
//        echo json_encode($res, JSON_NUMERIC_CHECK);
//        return;
//    }
    $pdo = pdoSqlConnect();
    $query = 'select time from Notification where userID = ? and token=?;';
    $st = $pdo->prepare($query);
    $st->execute([$data['id'],$data['queryString']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    if(empty($result)){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '본인의 토큰값이 아닙니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $res['result'] = $result[0]['time'];
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '성공적으로 조회되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function updateAlarmTime($data){
    if(!isExistToken($data['id'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '알림 토큰이 저장되어있지 않습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    if(!getRemindAllowedByDevice($data['id'],$data['fcmToken'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '리마인드 알림이 거절된 상태 혹은 토큰이 존재하지 않은 상태입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    $query = 'UPDATE Notification SET time = ? WHERE userID = ? and token=?';
    $st = $pdo->prepare($query);
    $st->execute([$data['time'],$data['id'],$data['fcmToken']]);
    $st = null;
    $pdo = null;
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '시간이 변경되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}
