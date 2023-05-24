<?php

function nameToID($name){
    $query = "select ID from User where name = ? and status not like 99999;";
    $res = execute($query,[$name]);
    return $res[0]['ID'];
}

function IDToName($id){
    $query = "select name from User where ID = ? and status not like 99999;";
    $res = execute($query,[$id]);
    return $res[0]['name'];
}

function emailToID($email){
    $query = "select ID from User where email = ? and status not like 99999;";
    $res = execute($query,[$email]);
    return $res[0]['ID'];
}

function IDToEmail($id){
    $query = "select email from User where ID = ? and status not like 99999;";
    $res = execute($query,[$id]);
    return $res[0]['email'];
}

function kakaoToID($id){
    $query = "select ID from User where kakao = ? and status not like 99999;";
    $res = execute($query,[$id]);
    return $res[0]['ID'];
}

function userCodeToID($code){
    $query = "select ID from User where code = ? and status not like 99999;";
    $res = execute($query,[$code]);
    return $res[0]['ID'];
}

function IDToCode($id){
    $query = "select code from User where ID = ? and status not like 99999;";
    $res = execute($query,[$id]);
    return $res[0]['code'];
}

function EmailToCode($email){
    $query = "select code from User where email = ? and status not like 99999;";
    $res = execute($query,[$email]);
    return $res[0]['code'];
}

function diaryToUser($diaryID){
    $query = "select userID as ID from UserDiary where diaryID = ? and status not like 999;";
    $res = execute($query,[$diaryID]);
    return $res[0]['ID'];
}

function postToDiary($postID){
    $query = "select diaryID as ID from Post where ID = ?;";
    $res = execute($query,[$postID]);
    return $res[0]['ID'];
}

function commentToPost($commentID){
    $query = "select postID as ID from Comment where ID = ?;";
    $res = execute($query,[$commentID]);
    return $res[0]['ID'];
}

function codeToDeviceType($code){
    switch ($code){
        case 1:
            return "IOS";
        case 2:
            return "Android";
    }
}

function codeToColor($code){
    switch ($code){
        case 1:
            return 16740464;
//            return "FF7070";
        case 2:
            return 16745308;
//            return "FF835C";
        case 3:
            return 16770930;
//            return "FFE772";
        case 4:
            return 16759088;
//            return "FFB930";
        case 5:
            return 7328675;
//            return "6FD3A3";
        case 6:
            return 6936269;
//            return "69D6CD";
        case 7:
            return 6529976;
//            return "63A3B8";
        case 8:
            return 13354731;
//            return "CBC6EB";
        case 9:
            return 16766947;
//            return "FFD7E3";
        case 10:
            return 12171191;
//            return "B9B7B7";
        case 11:
            return 16777215;
//            return "FFFFFF";
        case 12:
            return 4013374;
//            return "3D3D3E";
        default:
            return false;
    }
}

function codeToMood($code){
    $value = $code/100;
    $data = floor(round($value - floor($value),3)*100);
    switch ($data){
        case 1:
            return '기분 등록 없음';
        case 2:
            return '행복해요';
        case 3:
            return '슬퍼요';
        case 4:
            return '화나요';
        case 5:
            return '우울해요';
        case 6:
            return '설레요';
        case 7:
            return '멍때려요';
        default:
            return '잘못된 감정';
    }
}

function codeToIcon($code){ //아이콘 좌표 쓰기
    $value = $code/100;
    $data = floor(round($value - floor($value),3)*100);
    return $data;
}

function codeToBackground($code){ //속지 좌표 쓰기
    $dir = SERVER_URL."/uploads/background/";
    return $code;
}

function convertDate($date){
    $dateDiff = (int)$date;
    if($dateDiff < 60) return floor($dateDiff).'초 전';
    else if($dateDiff < 60*60) return floor($dateDiff/60).'분 전';
    else if($dateDiff < 60*60*24) return floor($dateDiff/(60*60)).'시간 전';
    else if($dateDiff < 60*60*24*30) return floor($dateDiff/(60*60*24)).'일 전';
    else if($dateDiff < 60*60*24*30*12) return floor($dateDiff/(60*60*24*30)).'달 전';
    else return floor($dateDiff/(60*60*24*30*12)).'년 전';
}

