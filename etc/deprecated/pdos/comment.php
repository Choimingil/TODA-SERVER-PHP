<?php

function postCommentVer2($userID,$body): int{
    // 댓글 추가
    $query = 'INSERT INTO Comment (userID, postID, text) VALUES (?, ?, ?);';
    $commentID = lastInsertID($query,[$userID, $body['post'], $body['reply']]);

    // 로그 저장
    $LogArray = array();
    if(!empty($body['userList'])){
        $logExecuteValue = Array();
        foreach($body['userList'] as $i=>$value){
            array_push($LogArray,'(?,?,?,?)');

            array_push($logExecuteValue,$body['userList'][$i]);
            array_push($logExecuteValue,5);
            array_push($logExecuteValue,$commentID);
            array_push($logExecuteValue,$userID);
        }
        $log = implode(',',$LogArray);
        $queryLog = 'INSERT INTO Log (receiveID, type, typeID, sendID) VALUES '.$log.';';
        execute($queryLog,$logExecuteValue);
    }

//    // 서버 아이디 동기화
//    // url : POST /id
//    // $req : { 'userID' : ?, 'type' : ?, 'typeID' : ?, 'serverID' : ? }
//    // userID : 유저 아이디,
//    // type : 0(다이어리), 1(게시글), 2(댓글),
//    // typeID : 각 타입 별 클라 아이디
//    // serverID : 각 타입 별 서버 아이디
//    $sendData = Array(
//        'userID' => $userID,
//        'type' => 2,
//        'typeID' => $body['commentID'],
//        'serverID' => $commentID
//    );
//    sendToCacheServer('/id',$sendData);

//    // 알림 전송
//    $tokenList = Array(
//        'token'=>$body['tokenList'],
//        'device'=>$body['deviceList']
//    );
//    $sendData = Array(
//        'type' => 5,
//        'userName' => $body['userName'],
//        'reply' => $body['reply'],
//        'postID' => $body['post'],
//        'tokenList' => $tokenList
//    );
//    sendToFcmServer($sendData);
    return $commentID;
}

function postReCommentVer2($userID,$body,$commentID): int{
    // 대댓글 추가
    $query = 'INSERT INTO Comment (userID, postID, text, parent) VALUES (?, ?, ?, ?);';
    $recommentID = lastInsertID($query,[$userID, $body['post'], $body['reply'], $commentID]);

    // 로그 저장
    $LogArray = array();
    if(!empty($body['userList'])){
        $logExecuteValue = Array();
        foreach($body['userList'] as $i=>$value){
            array_push($LogArray,'(?,?,?,?)');

            array_push($logExecuteValue,$body['userList'][$i]);
            array_push($logExecuteValue,6);
            array_push($logExecuteValue,$recommentID);
            array_push($logExecuteValue,$userID);
        }
        $log = implode(',',$LogArray);
        $queryLog = 'INSERT INTO Log (receiveID, type, typeID, sendID) VALUES '.$log.';';
        execute($queryLog,$logExecuteValue);
    }

//    // 서버 아이디 동기화
//    // url : POST /id
//    // $req : { 'userID' : ?, 'type' : ?, 'typeID' : ?, 'serverID' : ? }
//    // userID : 유저 아이디,
//    // type : 0(다이어리), 1(게시글), 2(댓글),
//    // typeID : 각 타입 별 클라 아이디
//    // serverID : 각 타입 별 서버 아이디
//    $sendData = Array(
//        'userID' => $userID,
//        'type' => 2,
//        'typeID' => $body['commentID'],
//        'serverID' => $recommentID
//    );
//    sendToCacheServer('/id',$sendData);

//    // 알림 전송
//    $tokenList = Array(
//        'token'=>$body['tokenList'],
//        'device'=>$body['deviceList']
//    );
//    $sendData = Array(
//        'type' => 6,
//        'userName' => $body['userName'],
//        'reply' => $body['reply'],
//        'postID' => $body['post'],
//        'tokenList' => $tokenList
//    );
//    sendToFcmServer($sendData);
    return $recommentID;
}

function deleteComment($userID,$commentID){
    $query = 'UPDATE Comment SET status = 0 WHERE ID = ? or parent = ?;';
    execute($query,[$commentID, $commentID]);
}

function updateComment($userID,$body){
    $query = 'UPDATE Comment SET text = ? WHERE ID = ?';
    execute($query,[$body['reply'], $body['comment']]);
}