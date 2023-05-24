<?php

function isKeyExist($key){
    try {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);
        if(!($redis->exists($key))) return false;
        else return true;
    } catch(Exception $e) {
        die($e->getMessage());
    }
}

function getRedis($key){
    try {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);
        if(!($redis->exists($key))) return 0;
        $res = $redis->get($key);
        $redis = null;
        return $res;
    } catch(Exception $e) {
        die($e->getMessage());
    }
}

function setRedis($key,$val){
    try {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);
        if(!($redis->exists($key))) $redis->del($key);
        $redis->set($key,$val);
        $redis = null;
//        echo $redis->get($key);
    } catch(Exception $e) {
        die($e->getMessage());
    }
}

function removeRedis($key){
    try {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);

        // 키값 비우기
        $redis->del($key);
//        echo json_encode($redis->lRange($key,0,0));

//        // 전체 키 삭제
//        $redis->flushAll();
    } catch(Exception $e) {
        die($e->getMessage());
    }
}

function clearRedis(){
    try {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);

//        // 키값 비우기
//        $redis->del($key);
//        echo json_encode($redis->lRange($key,0,0));

        // 전체 키 삭제
        $redis->flushAll();
    } catch(Exception $e) {
        die($e->getMessage());
    }
}

function getListKey($code,$id,$page){
    // 리스트 redis :  $type_$ID_$page

    $array = Array(
        $code,
        $id,
        $page
    );
    return implode('_',$array);
}

function getDateKey($code,$id){
    // 리스트 redis :  $type_$ID_$page

    $array = Array(
        $code,
        $id,
        'date'
    );
    return implode('_',$array);
}


function getDiaryListKey($code,$id,$status,$page){
    // 리스트 redis :  $type_$ID_$page

    $array = Array(
        $code,
        $id,
        $status,
        $page
    );
    return implode('_',$array);
}

function getDiaryDateKey($code,$id,$status){
    // 리스트 redis :  $type_$ID_$page

    $array = Array(
        $code,
        $id,
        $status,
        'date'
    );
    return implode('_',$array);
}








// deprecated
function generateKey($array,$isListRedis){
    // 수정 여부 확인 redis : $type_$ID_isChanged
    // 리스트 redis :  $type_$ID_$page

    if($isListRedis) return implode('_',$array);
    else{
        array_push($array,'isChanged');
        return implode('_',$array);
    }
}
