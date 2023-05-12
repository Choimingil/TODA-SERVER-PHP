<?php

function addPostVer4($userID,$body): int{
    // 현재의 시간값 추가
    $currentDateTime = date('H:i:s');
    if(substr($body['date'],-1,1)=='-') $body['date'] = substr($body['date'],0,10);
    $newDate = $body['date'].' '.$currentDateTime;

    // Post 추가
    $query = 'INSERT INTO Post (userID, diaryID, title, status, createAt) VALUES (?, ?, ?, ?,?);';
    $postID = lastInsertID($query,[$userID,$body['diary'],$body['title'],$body['status'],$newDate]);

    // 본문 저장
    $queryText = 'INSERT INTO PostText (postID, text, aligned) VALUES (?,?,?);';
    execute($queryText,[$postID,$body['text'],$body['statusText']]);

    // 이미지 저장
    $imageArray = array();
    if(!empty($body['imageList'])){
        $imageExecuteValue = Array();
        foreach($body['imageList'] as $i=>$value){
            array_push($imageArray,'(?,?,?)');

            array_push($imageExecuteValue,$postID);
            array_push($imageExecuteValue,$body['imageList'][$i]);
            array_push($imageExecuteValue,100);
        }
        $image = implode(',',$imageArray);
        $queryImage = 'INSERT INTO PostImage (postID, URL, size) VALUES '.$image.';';
        execute($queryImage,$imageExecuteValue);
    }

    // 로그 저장
    $LogArray = array();
    if(!empty($body['userList'])){
        $logExecuteValue = Array();
        foreach($body['userList'] as $i=>$value){
            array_push($LogArray,'(?,?,?,?)');

            array_push($logExecuteValue,$body['userList'][$i]);
            array_push($logExecuteValue,3);
            array_push($logExecuteValue,$postID);
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
//        'type' => 1,
//        'typeID' => $body['postID'],
//        'serverID' => $postID
//    );
//    sendToCacheServer('/id',$sendData);

//    // 알림 전송
//    $tokenList = Array(
//        'token'=>$body['tokenList'],
//        'device'=>$body['deviceList']
//    );
//    $sendData = Array(
//        'type' => 3,
//        'userName' => $body['userName'],
//        'diaryID' => $body['diary'],
//        'postID' => $postID,
//        'tokenList' => $tokenList
//    );
//    sendToFcmServer($sendData);

    return $postID;
}

function deletePost($userID,$postID){
    $query = 'UPDATE Post SET status = 0 WHERE userID = ? and ID = ?';
    execute($query,[$userID, $postID]);
}

function updatePostVer3($userID,$body){
    // 현재의 시간값 추가
    $currentDateTime = date('H:i:s');
    if(substr($body['date'],-1,1)=='-') $body['date'] = substr($body['date'],0,10);
    $newDate = $body['date'].' '.$currentDateTime;

    // 수정사항 반영
    $query = 'UPDATE Post SET title = ?, status = ?, createAt = ? WHERE ID = ?;
              UPDATE PostText SET text = ?, aligned = ? WHERE postID = ?;
              UPDATE PostImage SET status = 0 WHERE postID = ? and status not like 0;';
    execute($query,[$body['title'], $body['status'], $newDate, $body['post'],$body['text'], $body['statusText'], $body['post'],$body['post']]);

    $executeKey=array();
    $executeQuery=array();
    if(!empty($body['imageList'])){
        if(!isExistImage($body['post'])){
            foreach($body['imageList'] as $i=>$value){
                $query = 'INSERT INTO PostImage (postID, URL, size) VALUES ('.$body['post'].', ?, 100);';
                $executeQuery[$i] = $query;
                $executeKey[$i] = $body['imageList'][$i];
            }
        }
        else{
            foreach($body['imageList'] as $i=>$value){
                $isExist = false;
                $remainURL = getURL($body['post']);
                foreach($remainURL as $remainURLValue){
                    if($body['imageList'][$i] == $remainURLValue['url']){
                        $query = 'UPDATE PostImage SET status = 100 WHERE url = ?;';
                        $executeQuery[$i] = $query;
                        $executeKey[$i] = $remainURLValue['url'];
                        $isExist = true;
                        break;
                    }
                }
                if(!$isExist){
                    $query = 'INSERT INTO PostImage (postID, URL, size) VALUES ('.$body['post'].', ?, 100);';
                    $executeQuery[$i] = $query;
                    $executeKey[$i] = $body['imageList'][$i];
                }
            }
        }
        $query = implode($executeQuery);
        execute($query,$executeKey);
    }
}

function postLikeVer2($userID,$body){
    $query = 'INSERT INTO Heart (userID, postID, status) VALUES (?, ?, ?);';
    execute($query,[$userID,$body['post'],$body['mood']]);

    if(!isPostUser($userID,$body['post'])){
        $receive = getPostUser($body['post']);

        // 로그 추가
        $queryLog = 'INSERT INTO Log (receiveID, type, typeID, sendID) VALUES (?, ?, ?, ?);';
        execute($queryLog,[$receive['ID'], 4, $body['post'], $userID]);

//        // 알림 전송
//        $tokenList = Array(
//            'token'=>$body['tokenList'],
//            'device'=>$body['deviceList']
//        );
//        $sendData = Array(
//            'type' => 4,
//            'userName' => $body['userName'],
//            'receiveName' => $receive['name'],
//            'diaryID' => getPostDiary($body['post']),
//            'postID' => $body['post'],
//            'tokenList' => $tokenList
//        );
//        sendToFcmServer($sendData);
    }
}

function deleteLike($userID,$body){
    $query = 'UPDATE Heart SET status = 0 WHERE userID = ? and postID = ?';
    execute($query,[$userID, $body['post']]);
}

function repostLike($userID,$body){
    $query = 'UPDATE Heart SET status = ? WHERE userID = ? and postID = ?';
    execute($query,[$body['mood'], $userID, $body['post']]);
}