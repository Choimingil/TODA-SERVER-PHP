<?php

function addDiaryVer2($userID,$body){
    $status = $body['color']*100 + $body['status'];
    $query = "INSERT INTO Diary (name, status) VALUES (?, ?);";
    $diaryID = lastInsertID($query,[$body['title'], $status]);

    $queryDiary = "INSERT INTO UserDiary (userID, diaryID, diaryName, status) VALUES (?, ?, ?, ?);
              INSERT INTO Notice (diaryID,userID,notice) VALUES (?, ?, '');";
    execute($queryDiary,[$userID, $diaryID, $body['title'], $status,$diaryID,$userID]);

    // 서버 아이디 동기화
    // url : POST /id
    // $req : { 'userID' : ?, 'type' : ?, 'typeID' : ?, 'serverID' : ? }
    // userID : 유저 아이디,
    // type : 0(다이어리), 1(게시글), 2(댓글),
    // typeID : 각 타입 별 클라 아이디
    // serverID : 각 타입 별 서버 아이디
    $sendData = Array(
        'userID' => $userID,
        'type' => 0,
        'typeID' => $body['diaryID'],
        'serverID' => $diaryID
    );
    sendToCacheServer('/id',$sendData);
}

function sendDiaryFriend($userID,$diaryID,$body,$receiveUser){
    $receiveID = $receiveUser['userID'];
    if(isDeletedDiaryUser($receiveUser['userID'],$diaryID)){
        $query = "UPDATE UserDiary SET UserDiary.status=? WHERE UserDiary.userID=? and UserDiary.diaryID=?;
              INSERT INTO Log (receiveID, type, typeID, sendID) VALUES (?, ?, ?, ?);";
        execute($query,[$userID*10,$receiveID,$diaryID,$receiveID,1,$diaryID,$userID]);
    }
    else{
        $query = "INSERT INTO UserDiary (userID, diaryID, diaryName, status) VALUES (?, ?, ?, ?);
              INSERT INTO Log (receiveID, type, typeID, sendID) VALUES (?, ?, ?, ?);";
        execute($query,[$receiveID,$diaryID,$body['diaryName'],$userID*10,$receiveID,1,$diaryID,$userID]);
    }

//    $tokenList = Array(
//        'token'=>$body['tokenList'],
//        'device'=>$body['deviceList']
//    );
//    $sendData = Array(
//        'type' => 1,
//        'userName' => $body['userName'],
//        'userCode' => $body['userCode'],
//        'diaryID' => $diaryID,
//        'diaryName' => $body['diaryName'],
//        'receiveName' => $receiveUser['userName'],
//        'tokenList' => $tokenList
//    );
//    sendToFcmServer($sendData);
}

function acceptDiaryFriend($userID,$diaryID,$body,$receiveUser){
    $query = "UPDATE UserDiary,Log SET UserDiary.status=?,Log.status=999 WHERE UserDiary.userID=? and UserDiary.diaryID=? and Log.receiveID=? and Log.type=1 and Log.typeID=?;
                  INSERT INTO Log (receiveID, type, typeID, sendID) VALUES (?, ?, ?, ?);";
    execute($query,[$body['diaryStatus'],$userID,$diaryID,$userID,$diaryID,$receiveUser['userID'],2,$diaryID,$userID]);

//    $tokenList = Array(
//        'token'=>$body['tokenList'],
//        'device'=>$body['deviceList']
//    );
//    $sendData = Array(
//        'type' => 2,
//        'userName' => $body['userName'],
//        'userCode' => $body['userCode'],
//        'diaryName' => $body['diaryName'],
//        'receiveName' => $receiveUser['userName'],
//        'tokenList' => $tokenList
//    );
//    sendToFcmServer($sendData);
}

function rejectDiary($userID,$diaryID){
    $query =
        "UPDATE UserDiary SET UserDiary.status=999 WHERE UserDiary.userID=? and UserDiary.diaryID=?;
        UPDATE Log SET Log.status=999 WHERE Log.receiveID=? and Log.type=1 and Log.typeID=?;";
    execute($query,[$userID,$diaryID,$userID,$diaryID]);
}

function deleteDiary($userID,$diaryID){
    $query = 'UPDATE UserDiary SET status = 999 WHERE userID = ? and diaryID = ?';
    execute($query,[$userID,$diaryID]);
}

function updateDiary($userID,$body,$prevStatus,$currStatus){
    $query = 'UPDATE UserDiary SET diaryName = ?, status = ? WHERE userID = ? and diaryID = ? and status not like 999;';
    if($prevStatus == 2 && $currStatus == 3)
        execute($query,[$body['title'],$body['color']*100 + 4,$userID,$body['diary']]);
    else if($prevStatus == 4 && $currStatus == 3)
        execute($query,[$body['title'],$body['color']*100 + 4,$userID,$body['diary']]);
    else
        execute($query,[$body['title'],$body['color']*100 + $body['status'],$userID,$body['diary']]);
}

function postNotice($userID,$body){
    if(isExistNotice($body['diary'])){
        $query = 'UPDATE Notice SET userID = ?, notice = ? WHERE diaryID = ?;';
        execute($query,[$userID,$body['notice'],$body['diary']]);
    }
    else if(isDeletedNotice($body['diary'])){
        $query = 'UPDATE Notice SET userID = ?, notice = ?, status = 100 WHERE diaryID = ?;';
        execute($query,[$userID,$body['notice'],$body['diary']]);
    }
    else{
        $query = 'INSERT INTO Notice (diaryID, userID, notice) VALUES (?, ?, ?);';
        execute($query,[$body['diary'],$userID,$body['notice']]);
    }
}

function deleteNotice($userID,$diaryID){
    $query = 'UPDATE Notice SET status = 0 WHERE diaryID = ?';
    execute($query,[$diaryID]);
}