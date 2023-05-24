<?php

function isValidEmail($email): int
{
    $query = "SELECT EXISTS(SELECT * FROM User WHERE email= ? and status not like 99999) AS exist;";
    $res = execute($query,[$email]);
    return intval($res[0]['exist']);
}

function isValidID($id): int
{
    $query = "SELECT EXISTS(SELECT * FROM User WHERE ID= ? and status not like 99999) AS exist;";
    $res = execute($query,[$id]);
    return intval($res[0]['exist']);
}

function isValidUser($email, $password): int
{
    $query = "SELECT EXISTS(SELECT * FROM User WHERE email = ? and password= ? and status not like 99999) AS exist;";
    $res = execute($query,[$email, $password]);
    return intval($res[0]['exist']);
}

function isValidPassword($id, $password): int
{
    $query = "SELECT EXISTS(SELECT * FROM User WHERE ID = ? and password= ? and status not like 99999) AS exist;";
    $res = execute($query,[$id, $password]);
    return intval($res[0]['exist']);

}

function isValidUserStatus($email, $password, $status): int
{
    $query = "SELECT EXISTS(SELECT * FROM User WHERE email = ? and password= ? and status= ? and email not like 'quited' and password not like 'quited' and status not like 99999) AS exist;";
    $res = execute($query,[$email, $password, $status]);
    return intval($res[0]['exist']);
}

function isValidKakaoStatus($kakao, $status): int
{
    $query = "SELECT EXISTS(SELECT * FROM User WHERE kakao= ? and status= ?) AS exist;";
    $res = execute($query,[$kakao, $status]);
    return intval($res[0]['exist']);
}

function isValidCode($code): bool
{
    return match ($code) {
        1, 2, 3 => true,
        default => false,
    };
}

function isValidAnnouncement($announcementID): int
{
    $query = "SELECT EXISTS(SELECT * FROM Announcement WHERE ID = ? and status not like 0) AS exist;";
    $res = execute($query,[$announcementID]);
    return intval($res[0]['exist']);
}

function isExistToken($userID): int
{
    $query = "select EXISTS(select * from Notification where userID = ? and status not like 0) AS exist;";
    $res = execute($query,[$userID]);
    return intval($res[0]['exist']);
}

function isExistOnlyToken($token): int
{
    $query = "select EXISTS(select * from Notification where token = ? and status not like 0) AS exist;";
    $res = execute($query,[$token]);
    return intval($res[0]['exist']);
}

function isJustExistToken($id,$token): int
{
    $query = "select EXISTS(select * from Notification where userID = ? and token = ?) AS exist;";
    $res = execute($query,[$id,$token]);
    return intval($res[0]['exist']);
}

function isUpdating(){
    $query = 'select isUpdating, hour(startTime) as startTime, hour(finishTime) as finishTime from isUpdating';
    $res = execute($query,[]);
    return $res[0];
}

function isExistLog($data){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from Log where receiveID = ? and type = ? and typeID = ? and sendID = ?) AS exist;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$data['receiveID'], $data['type'], $data['typeID'], $data['sendID']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st=null;$pdo = null;
    return intval($res[0]['exist']);
}

function isAlreadyReadAnnouncement($data){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from UserAnnouncement where userID = ? and announcementID = ?) AS exist;";
    $st = $pdo->prepare($query);
    $st->execute([$data['id'], $data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st=null;$pdo = null;
    return intval($res[0]['exist']);
}

function getTokenAllowed($userID){
    $pdo = pdoSqlConnect();
    $query = 'select isAllowed from Notification where userID = ?';
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st=null;$pdo = null;
    if($res[0]['isAllowed']=='Y') return true;
    else return false;
}

function isAlreadyUsedToken($id,$token){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from Notification where userID = ? and token = ? and status like 0) AS exist;";
    $st = $pdo->prepare($query);
    $st->execute([$id,$token]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st=null;$pdo = null;
    return intval($res[0]['exist']);
}

function isReadAnnouncement($userID, $announcementID){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from UserAnnouncement where userID = ? and announcementID = ?) AS exist;";
    $st = $pdo->prepare($query);
    $st->execute([$userID,$announcementID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st=null;$pdo = null;
    if(intval($res[0]['exist'])==1) return true;
    else return false;
}

function getDateCode($date){
    $year = (int)substr($date,0,4);
    $month = (int)substr($date,5,2);
    $day = (int)substr($date,8,2);
//    echo $year*10000 + $month*100 + $day;
    return $year*10000 + $month*100 + $day;
}

function getUserStatus($userID){
    $query = "select status from User where email=? and status not like 99999;";
    $res = execute($query,[$userID]);
    return $res[0]['status'];
}

function getTokenAllowedByDevice($userID,$token): bool
{
    $query = 'select isAllowed from Notification where userID = ? and token = ?';
    $res = execute($query,[$userID,$token]);
    if($res[0]['isAllowed']=='Y') return true;
    else return false;
}

function getRemindAllowedByDevice($userID,$token): bool
{
    $query = 'select isRemindAllowed from Notification where userID = ? and token=?';
    $res = execute($query,[$userID,$token]);
    if(empty($res)) return false;
    else if($res[0]['isRemindAllowed']=='Y') return true;
    else return false;
}

function getEventAllowedByDevice($userID,$token): bool
{
    $query = 'select isEventAllowed from Notification where userID = ? and token=?';
    $res = execute($query,[$userID,$token]);
    if($res[0]['isEventAllowed']=='Y') return true;
    else return false;
}

function setString($value): string{
    if(strpos($value, "'") !== false){
        $newValue = str_replace("'","''",$value);
        return '\''.$newValue.'\'';
    }
    else return '\''.$value.'\'';
}

function getFinalPageLog($userID){
    $query = 'select count(*) as num from Log where receiveID = ? and status not like 999;';
    $res = execute($query,[$userID]);
    if($res[0]['num'] == 0) return 1;
    else return floor(($res[0]['num']-1)/20)+1;
}