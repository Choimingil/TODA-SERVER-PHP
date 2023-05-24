<?php

function isMyEmail($email,$userID): bool
{
    $query = "SELECT EXISTS(SELECT * FROM User WHERE email= ? and ID = ?) AS exist;";
    $res = execute($query,[$email, $userID]);
    return intval($res[0]['exist']);
}

function isValidUserCode($code): int
{
    $query = "SELECT EXISTS(SELECT * FROM User WHERE code= ? and status not like 99999) AS exist;";
    $res = execute($query,[$code]);
    return intval($res[0]['exist']);
}

function isExistUserID($userID){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from User where ID = ?) as exist";
    $st = $pdo->prepare($query);
    $st->execute([$userID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st=null;$pdo = null;
    if($res[0]['exist']==1) return true;
    else return false;
}

function isExistSelfie($userID): int
{
    $query = "SELECT EXISTS(SELECT * FROM UserImage WHERE userID=?) AS exist;";
    $res = execute($query,[$userID]);
    return intval($res[0]['exist']);
}

function isAlreadyFinish($id){
    $pdo = pdoSqlConnect();
    $query = "SELECT ifnull(name,0) as name, ifnull(birth,0) as birth FROM User WHERE ID = ? and status not like 99999";
    $st = $pdo->prepare($query);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st=null;$pdo = null;

    if($res[0]['name'] == '0' || $res[0]['birth'] == '0') return false;
    else return true;
}

function createCode(): string
{
    $characters  = '0123456789';
    $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string_generated = '';
    $nmr_loops = 9;

    while ($nmr_loops--) $string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];
    if(isValidUserCode($string_generated)) createCode();
    return $string_generated;
}

function createCodeKey($num): string
{
    $characters  = '0123456789';
    $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string_generated = '';
    $nmr_loops = $num;

    while ($nmr_loops--) $string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];
    if(isValidUserCode($string_generated)) createCode();
    return $string_generated;
}

function getUserByCode($code)
{
    $query = "SELECT ID as userID, name as userName FROM User WHERE code= ? and status not like 99999;";
    $res = execute($query,[$code]);
    return $res[0];
}

function isValidKakao($kakao){
    if(!isset($kakao)){
        return false;
    }

    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE kakao= ? and status not like 99999) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$kakao]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]['exist']);

}

function getUserStatusKakao($kakaoID){
    if(!isset($kakaoID)){
        return false;
    }

    $pdo = pdoSqlConnect();
    $query = "select status from User where kakao = ?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$kakaoID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return $res[0]['status'];
}