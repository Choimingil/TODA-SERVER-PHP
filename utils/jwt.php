<?php

use Firebase\JWT\JWT;

function getTodayByTimeStamp(): string
{
    return date("Y-m-d H:i:s");
}

function getJWToken($data, $secretKey){
    $token = array(
        'date' => getTodayByTimeStamp(),
        'id' => (string)$data['id'],
        'pw' => (string)$data['pw'],
        'appPW' => (string)getUserStatus($data['id'])
    );

    return JWT::encode($token, $secretKey);
}

function getJWTokenNew($data, $secretKey){
    $token = array(
        'date' => (string)getTodayByTimeStamp(),
        'id' => (string)IDToEmail($data['id']),
        'pw' => (string)$data['pw'],
        'appPW' => (string)getUserStatus(IDToEmail($data['id']))
    );

    $jwt = JWT::encode($token, $secretKey);
    return $jwt;
}

function getJWTokenCache($data, $secretKey){
    $token = array(
        'date' => getTodayByTimeStamp(),
        'id' => (string)$data['userID'],
        'code' => (string)$data['userCode'],
        'email' => (string)$data['email']
    );

    return JWT::encode($token, $secretKey);
}

function getJWTokenKakao($data, $secretKey){
    $token = array(
        'date' => getTodayByTimeStamp(),
        'kakao' => (string)$data['kakao'],
        'status' => (string)getUserStatus($data['kakao'])
    );
    return JWT::encode($token, $secretKey);
}

function getDataByJWToken($jwt, $secretKey)
{
    try{
        $decoded = JWT::decode($jwt, $secretKey, array('HS256'));
    }catch(\Exception $e){
        return "";
    }

//    print_r($decoded);
    return $decoded;
}