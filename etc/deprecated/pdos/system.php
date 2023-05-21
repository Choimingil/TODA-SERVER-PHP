<?php

function createJwt($data, $secretKey): object{
    $jwt = getJWToken($data,$secretKey);

    if (isUpdating()['isUpdating'] == 'Y') {
        $res = (object)Array(
            'jwt' => $jwt,
            'isUpdating' => true,
            'startTime' => isUpdating()['startTime'],
            'finishTime' => isUpdating()['finishTime']
        );
    } else {
        $res = (object)Array(
            'jwt' => $jwt,
            'isUpdating' => false,
            'startTime' => false,
            'finishTime' => false
        );
    }
    return $res;
}

function checkNotification($userID, $body){
    if($body['type'] == 1) $status = 100;
    else $status = 200;

    if(isExistOnlyToken($body['token'])){
        if(isJustExistToken($userID,$body['token'])){
            $query = 'UPDATE Notification SET status = 0 WHERE userID in (select * from (select userID from Notification where token = ?) tmp) and token = ?;
                UPDATE Notification SET status = ?, isAllowed = ?, isRemindAllowed = ?, isEventAllowed = ? WHERE userID = ? and token = ?;';
            $data = [$body['token'],$body['token'],$status,$body['isAllowed'],$body['isAllowed'],$body['isAllowed'],$userID,$body['token']];
        }
        else{
            $query = 'UPDATE Notification SET status = 0 WHERE userID in (select * from (select userID from Notification where token = ?) tmp) and token = ?;
                INSERT INTO Notification (userID, token, isAllowed, isRemindAllowed, isEventAllowed) VALUES (?,?,?,?,?);';
            $data = [$body['token'],$body['token'],$userID, $body['token'], $body['isAllowed'], $body['isAllowed'], $body['isAllowed']];
        }
    }
    else{
        if(isJustExistToken($userID,$body['token'])){
            $query = "UPDATE Notification SET status = ?, isAllowed = ?, isRemindAllowed = ?, isEventAllowed = ? WHERE userID = ? and token = ?;";
            $data = [$status,$body['isAllowed'],$body['isAllowed'],$body['isAllowed'],$userID, $body['token']];
        }
        else{
            $query = "INSERT INTO Notification (userID, token, isAllowed, isRemindAllowed, isEventAllowed) VALUES (?,?,?,?,?);";
            $data = [$userID, $body['token'], $body['isAllowed'], $body['isAllowed'], $body['isAllowed']];
        }
    }
    return execute($query,$data);
}

function updateAlarmVer2($userID,$body): int
{
    $code = 102;
    switch ($body['alarmType']){
        case 0:
            $query = 'UPDATE Notification SET isAllowed = ? WHERE userID = ? and token=?';
            if(getTokenAllowedByDevice($userID,$body['fcmToken'])){
                $data = ['N',$userID,$body['fcmToken']];
                $code = 200;
            }
            else{
                $data = ['Y',$userID,$body['fcmToken']];
                $code = 100;
            }
            $res = execute($query,$data);
//            if(!$res) $code = 102;
            break;
        case 1:
            $query = 'UPDATE Notification SET isRemindAllowed = ? WHERE userID = ? and token=?';
            if(getRemindAllowedByDevice($userID,$body['fcmToken'])){
                $data = ['N',$userID,$body['fcmToken']];
                $code = 200;
            }
            else{
                $data = ['Y',$userID,$body['fcmToken']];
                $code = 100;
            }
            $res = execute($query,$data);
//            if(!$res) $code = 102;
            break;
        case 2:
            $query = 'UPDATE Notification SET isEventAllowed = ? WHERE userID = ? and token=?';
            if(getEventAllowedByDevice($userID,$body['fcmToken'])){
                $data = ['N',$userID,$body['fcmToken']];
                $code = 200;
            }
            else{
                $data = ['Y',$userID,$body['fcmToken']];
                $code = 100;
            }
            $res = execute($query,$data);
//            if(!$res) $code = 102;
            break;
    }
    return $code;
}

function updateAlarmTime($userID,$body): bool
{
    $query = 'UPDATE Notification SET time = ? WHERE userID = ? and token=?';
    execute($query,[$body['time'],$userID,$body['fcmToken']]);
    return true;
}

function getJwtCache($userID)
{
    $url = "\'".SERVER_URL.'/uploads/user/default.png'."\'";
    if(isExistSelfie($userID)){
        $query =
            "select User.id as userID, User.code as userCode, User.status as appPW, ifnull(User.email,'카카오 로그인') as email, User.name as name,
        concat(year(User.birth),'-',if(month(User.birth)<10, concat(0,month(User.birth)),month(User.birth)),'-',day(User.birth)) as birth,
        ifnull(UserImage.URL,"+$url+") as selfie
        from User left join UserImage on User.id = UserImage.userID where User.ID = ? and UserImage.status not like 0;";
    }
    else{
        $query =
            "select User.id as userID, User.code as userCode, User.status as appPW, ifnull(User.email,'카카오 로그인') as email, User.name as name,
        concat(year(User.birth),'-',if(month(User.birth)<10, concat(0,month(User.birth)),month(User.birth)),'-',day(User.birth)) as birth,
        "+$url+" as selfie
        from User where User.ID = ?;";
    }

    $res = execute($query,[$userID]);
    $res[0]['userID'] = (int)$res[0]['userID'];
    $res[0]['appPW'] = (int)$res[0]['appPW'];
   return getJWTokenCache($res[0],JWT_SECRET_KEY);
}

function checkUpdateVer2($type,$version){
    if(isUpdating()['isUpdating'] == 'Y') return 300;
    switch ($type) {
        case 1:
            if (IOSversion == $version || IOSversionOld == $version) return 100;
            else return 200;
        case 2:
            if (AOSversion == $version || AOSversionOld == $version) return 100;
            else return 200;
    }
}

function decodeTokenVer2(){
    $res = (array)getDataByJWToken($_SERVER['HTTP_X_ACCESS_TOKEN'], JWT_SECRET_KEY);
    if(isset($res['code'])){
        // 캐시 토큰
        return array(
            'date' => (string)$res['date'],
            'id' => (int)$res['id'],
            'appPW' => (int)getUserStatus($res['email']),
            'email' => (string)$res['email'],
            'code' => (string)$res['code']
        );
    }
    else{
        // 로그인 토큰
        return array(
            'date' => (string)$res['date'],
            'id' => (int)emailToID($res['id']),
            'appPW' => (int)$res['appPW'],
            'email' => (string)$res['id'],
            'code' => (string)EmailToCode($res['id'])
        );
    }
}

function readAnnouncement($userID,$announcementID): bool
{
    $query = "select exists(select * from UserAnnouncement where userID = ? and announcementID = ?) as exist";
    $res = execute($query,[$userID,$announcementID]);
    if($res[0]['exist']==0){
        $query = 'INSERT INTO UserAnnouncement (userID,announcementID) values (?,?);';
        execute($query,[$userID,$announcementID]);
    }
    return true;
}