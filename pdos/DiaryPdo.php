<?php

function addDiary($data){
    $pdo = pdoSqlConnect();
    $query = 'INSERT INTO Diary (name, status) VALUES (?, ?);';
    $st = $pdo->prepare($query);
    $st->execute([$data['title'], $data['status']]);
    $st = null;
    $diaryID = $pdo->lastInsertId();

    $query = "INSERT INTO UserDiary (userID, diaryID, diaryName, status) VALUES (?, ?, ?, ?);
              INSERT INTO Notice (diaryID,userID,notice) VALUES (?, ?, '');";
    $st = $pdo->prepare($query);
    $st->execute([$data['id'], $diaryID, $data['title'], $data['status'],$diaryID,$data['id']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '다이어리가 추가되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function addDiaryFriend($data){
    $receiveID = userCodeToID($data['userCode']);
    if($data['id'] == $receiveID){
        $res['isSuccess'] = FALSE;
        $res['code'] = 501;
        $res['message'] = '자기 자신을 등록할 수 없습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $pdo = pdoSqlConnect();
    $query = 'select name, status from Diary where ID = ?;';
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    if(isSendRequest($data['id'], $data['pathVar'])){
        $pdo = pdoSqlConnect();
        $query = 'UPDATE UserDiary,Log SET UserDiary.status=?,Log.status=999 WHERE UserDiary.userID=? and UserDiary.diaryID=? and Log.receiveID=? and Log.type=1 and Log.typeID=?;
                  INSERT INTO Log (receiveID, type, typeID, sendID) VALUES (?, ?, ?, ?);';
        $st = $pdo->prepare($query);
        $st->execute([$result[0]['status'], $data['id'], $data['pathVar'],$data['id'],$data['pathVar'],$receiveID, 2, $data['pathVar'], $data['id']]);
        $st = null;
        $pdo = null;

        $pdo = pdoSqlConnect();
        $query =
"select 
       User.name as name, 
       User.code as code, 
       ifnull(Notification.token,'') as token, 
       UserDiary.diaryName as diaryName, 
       Notification.status as status 
from User
    left join UserDiary on UserDiary.userID = User.ID
    left join Notification on Notification.userID = User.ID
        and Notification.status not like 0 
        and Notification.isAllowed like 'Y' 
where User.ID in (?,?) 
  and UserDiary.diaryID = ? 
  and User.status not like 99999 
order by User.ID asc;";
        $st = $pdo->prepare($query);
        $st->execute([$data['id'],$receiveID,$data['pathVar']]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $user = $st->fetchAll();
        $st = null;
        $pdo = null;

        $sendData = Array(
            'user' => $user,
            'userCode' => $data['userCode']
        );
        sendToAlarmServer('/push/diary/accept',$sendData);

        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '다이어리 초대 요청을 승낙하였습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    if(!isDiaryUser($data['id'],$data['pathVar'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 401;
        $res['message'] = '다이어리에 등록되지 않은 사용자입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    if(isAloneDiary($data['pathVar'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 310;
        $res['message'] = '혼자 쓰는 다이어리에 친구를 초대할 수 없습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    if(isDiaryUser($receiveID,$data['pathVar'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 501;
        $res['message'] = '이미 다이어리에 등록된 사용자입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    if(isSendRequest($receiveID,$data['pathVar'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 501;
        $res['message'] = '이미 초대한 사용자입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    if(isDeletedDiaryUser($receiveID,$data['pathVar'])){
        $pdo = pdoSqlConnect();
        $query = "UPDATE UserDiary SET UserDiary.status=? WHERE UserDiary.userID=? and UserDiary.diaryID=?;
              INSERT INTO Log (receiveID, type, typeID, sendID) VALUES (?, ?, ?, ?);";
        $st = $pdo->prepare($query);
        $st->execute([$data['id']*10, $receiveID, $data['pathVar'],$receiveID, 1, $data['pathVar'], $data['id']]);
        $st = null;
        $pdo = null;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO UserDiary (userID, diaryID, diaryName, status) VALUES (?, ?, ?, ?);
              INSERT INTO Log (receiveID, type, typeID, sendID) VALUES (?, ?, ?, ?);";
        $st = $pdo->prepare($query);
        $st->execute([$receiveID, $data['pathVar'], $result[0]['name'], $data['id']*10,$receiveID, 1, $data['pathVar'], $data['id']]);
        $st = null;
        $pdo = null;
    }

    $pdo = pdoSqlConnect();
    $query =
"select 
       User.name as name, 
       User.code as code, 
       ifnull(Notification.token,'') as token, 
       UserDiary.diaryName as diaryName, 
       Notification.status as status 
from User 
    left join UserDiary on UserDiary.userID = User.ID
    left join Notification on Notification.userID = User.ID
        and Notification.status not like 0 
        and Notification.isAllowed like 'Y' 
where User.ID in (?,?) 
  and UserDiary.diaryID = ? 
  and User.status not like 99999 
order by User.ID asc;";
    $st = $pdo->prepare($query);
    $st->execute([$data['id'],$receiveID,$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $user = $st->fetchAll();
    $st = null;
    $pdo = null;

    $sendData = Array(
        'user' => $user,
        'userCode' => $data['userCode']
    );
    sendToAlarmServer('/push/diary/send',$sendData);

    $res['isSuccess'] = TRUE;
    $res['code'] = 200;
    $res['message'] = '다이어리 초대 요청이 발송되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function getRequestByUserCode($data){
    $diaryData = implode(',',createCodeArray(1,12,1,4));
    $pdo = pdoSqlConnect();
    $query =
"select
       User.id as userID,
       User.code as userCode,
       ifnull(User.email,'카카오 로그인') as email,
       User.name as name,
       concat(year(User.birth),'-',if(month(User.birth)<10, concat(0,month(User.birth)),month(User.birth)),'-',day(User.birth)) as birth,
       UserImage.URL as selfie,
       UserDiary.diaryID as diaryID,
       UserDiary.diaryName as diaryName,
       TIMESTAMPDIFF(SECOND, UserDiary.createAt, now()) as date
from User
left join UserImage on User.id = UserImage.userID
    and UserImage.status not like 0
left join UserDiary on UserDiary.status/10 = User.ID
    and UserDiary.diaryID=?
    and UserDiary.status/10 in
    (select truncate(status/10,0) as userID from UserDiary where userID = ? and status not in (".$diaryData."))
where UserDiary.userID = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar'],$data['id'],$data['id']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    $none = Array();
    if(empty($result)) return $none;
    else{
        $result[0]['date'] = convertDate((int)$result[0]['date']);
        $result[0]['userID'] = (int)$result[0]['userID'];
        $result[0]['diaryID'] = (int)$result[0]['diaryID'];
        return $result[0];
    }
}

function deleteDiary($data){
    if(isSendRequest($data['id'],$data['pathVar'])){
        $pdo = pdoSqlConnect();
        //다이어리 조회 때 확인. 초대 실수로 거절했을 때 대처법
        $query = "UPDATE UserDiary,Log SET UserDiary.status=999,Log.status=999 WHERE UserDiary.userID=? and UserDiary.diaryID=? and Log.receiveID=? and Log.type=1 and Log.typeID=?;";
        $st = $pdo->prepare($query);
        $st->execute([$data['id'], $data['pathVar'],$data['id'],$data['pathVar']]);
        $st = null;
        $pdo = null;

        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '다이어리 초대가 거절되었습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    if(!isDiaryUser($data['id'],$data['pathVar'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 401;
        $res['message'] = '다이어리에 등록되지 않은 사용자입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

//    // setting redis
//    $userID = $data['id'];
//    $status = getDiaryStatus($data['id'],$data['pathVar']) - floor(getDiaryStatus($data['id'],$data['pathVar'])/10)*10;
//    $lastPage = getFinalPageDiary($userID,$status);
//    deleteDiaryMethod('diary',$userID,$status,$lastPage,'diaryID',$data['pathVar']);
//
//    // 승낙/탈퇴의 경우 최신화가 잘 안되어서 redis값 초기화하기
//    $memberIDArray = getDiaryMemberID($data['pathVar']);
//    $sizeOfMemberIDArray = sizeof($memberIDArray);
//    for($j=0;$j<$sizeOfMemberIDArray;$j++){
//        for($i=1;$i<=$lastPage;$i++){
//            $listKey = getDiaryListKey('diary',$memberIDArray[$j]['ID'],$status,$i);
//            setRedis($listKey,0);
//        }
//    }

    $pdo = pdoSqlConnect();
    $query = 'UPDATE UserDiary SET status = 999 WHERE userID = ? and diaryID = ?';
    $st = $pdo->prepare($query);
    $st->execute([$data['id'], $data['pathVar']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '다이어리에서 나갔습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function updateDiary($data){
    if(!isDiaryUser($data['id'],$data['diary'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '다이어리에 등록되지 않은 사용자입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $pdo = pdoSqlConnect();
    $query = 'select status - truncate(status,-1) as status from UserDiary where userID = ? and diaryID = ?';
    $st = $pdo->prepare($query);
    $st->execute([$data['id'], $data['diary']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $status = $data['status'] - $data['color']*100;

    if(($result[0]['status'] == 3 && $status == 2) or ($result[0]['status'] == 4 && $status == 1) or ($result[0]['status'] == 3 && $status == 4)){
        $res['isSuccess'] = FALSE;
        $res['code'] = 103;
        $res['message'] = '잘못된 다이어리 변경입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    if($result[0]['status'] == 2 && $status == 3){
        $query = 'UPDATE UserDiary SET diaryName = ?, status = ? WHERE userID = ? and diaryID = ? and status not like 999;';
        $st = $pdo->prepare($query);
        $st->execute([$data['title'], $data['color']*100 + 4, $data['id'], $data['diary']]);
        $st = null;
        $pdo = null;
    }
    else if($result[0]['status'] == 4 && $status == 3){
        $query = 'UPDATE UserDiary SET diaryName = ?, status = ? WHERE userID = ? and diaryID = ? and status not like 999;';
        $st = $pdo->prepare($query);
        $st->execute([$data['title'], $data['color']*100 + 4, $data['id'], $data['diary']]);
        $st = null;
        $pdo = null;
    }
    else{
        $query = 'UPDATE UserDiary SET diaryName = ?, status = ? WHERE userID = ? and diaryID = ? and status not like 999;';
        $st = $pdo->prepare($query);
        $st->execute([$data['title'], $data['status'], $data['id'], $data['diary']]);
        $st = null;
        $pdo = null;
    }

//    // redis값 초기화하기
//    $userID = $data['id'];
//    for($status=1;$status<=4;$status++){
//        $lastPage = getFinalPageDiary($userID,$status);
//        for($i=1;$i<=$lastPage;$i++){
//            $listKey = getDiaryListKey('diary',$userID,$status,$i);
//            setRedis($listKey,0);
//        }
//    }

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '다이어리 수정이 완료되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function getDiaries($data){
    $pdo = pdoSqlConnect();
    $query =
"select
       User.name as userName,
       Diary.ID as diaryID,
       UserDiary.diaryName as name,
       UserDiary.status - truncate(UserDiary.status, -1) as status,
       truncate(UserDiary.status/100,0) as color,
       truncate(UserDiary.status/100,0) as colorCode,
       0 as userNum
from Diary
    left join UserDiary on UserDiary.diaryID = Diary.ID
    and UserDiary.status - truncate(UserDiary.status, -1) in (?,?)
    and UserDiary.status not like 999
    and UserDiary.status%10 not like 0
    left join User on UserDiary.userID = User.ID
where UserDiary.userID = ?
order by Diary.createAt desc limit ".$data['page'].", 20;";
    $st = $pdo->prepare($query);
    if($data['status']==3) $st->execute([$data['status'],$data['status']+1,$data['id']]);
    else if($data['status']==4) $st->execute([$data['status']-1,$data['status'],$data['id']]);
    else $st->execute([$data['status'],$data['status']+2,$data['id']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    $diaryIDArr = Array();
    foreach($result as $i => $value){
        $result[$i]['color'] = (int)(codeToColor($result[$i]['color']));
        $result[$i]['diaryID'] = (int)$result[$i]['diaryID'];
        array_push($diaryIDArr,$result[$i]['diaryID']);
        $result[$i]['status'] = (int)$result[$i]['status'];
        $result[$i]['colorCode'] = (int)$result[$i]['colorCode'];
        $result[$i]['userNum'] = (int)$result[$i]['userNum'];
    }
    $diaryIDList = implode(',',$diaryIDArr);

    if(!empty($diaryIDArr)){
        $pdo = pdoSqlConnect();
        $query =
            'select 
       count(UserDiary.userID) as num,
       UserDiary.diaryID as diaryID
from UserDiary
    left join User on User.ID = UserDiary.userID
where User.status not like 99999
and UserDiary.status not like 999
and UserDiary.status%10 not like 0
and UserDiary.diaryID in ('.$diaryIDList.')
group by UserDiary.diaryID;';
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $diaryNum = $st->fetchAll();
        $st = null;
        $pdo = null;

        foreach($result as $i=>$value){
            foreach($diaryNum as $num){
                if($value['diaryID'] == (int)$num['diaryID']){
                    $result[$i]['userNum'] = (int)$num['num'];
                }
            }
        }
    }

    $none = Array();
    if(empty($result)) return $none;
    else return $result;
}

function getDiariesKeyword($data){
    $pdo = pdoSqlConnect();
    $query =
        "select
       User.name as userName,
       Diary.ID as diaryID,
       UserDiary.diaryName as name,
       UserDiary.status - truncate(UserDiary.status, -1) as status,
       truncate(UserDiary.status/100,0) as color,
       truncate(UserDiary.status/100,0) as colorCode,
       0 as userNum
from Diary
    left join UserDiary on UserDiary.diaryID = Diary.ID
    and UserDiary.status - truncate(UserDiary.status, -1) in (?,?)
    and UserDiary.status not like 999
    and UserDiary.status%10 not like 0
    left join User on UserDiary.userID = User.ID
where UserDiary.userID = ? and UserDiary.diaryName like concat('%' , ? , '%')
order by Diary.createAt desc limit ".$data['page'].", 20;";
    $st = $pdo->prepare($query);
    if($data['status']==3) $st->execute([$data['status'],$data['status']+1,$data['id'],$data['queryString']]);
    else if($data['status']==4) $st->execute([$data['status']-1,$data['status'],$data['id'],$data['queryString']]);
    else $st->execute([$data['status'],$data['status']+2,$data['id'],$data['queryString']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    $diaryIDArr = Array();
    foreach($result as $i => $value){
        $result[$i]['color'] = (int)(codeToColor($result[$i]['color']));
        $result[$i]['diaryID'] = (int)$result[$i]['diaryID'];
        array_push($diaryIDArr,$result[$i]['diaryID']);
        $result[$i]['status'] = (int)$result[$i]['status'];
        $result[$i]['colorCode'] = (int)$result[$i]['colorCode'];
        $result[$i]['userNum'] = (int)$result[$i]['userNum'];
    }
    $diaryIDList = implode(',',$diaryIDArr);

    if(!empty($diaryIDArr)){
        $pdo = pdoSqlConnect();
        $query =
            'select 
       count(UserDiary.userID) as num,
       UserDiary.diaryID as diaryID
from UserDiary
    left join User on User.ID = UserDiary.userID
where User.status not like 99999
and UserDiary.status not like 999
and UserDiary.status%10 not like 0
and UserDiary.diaryID in ('.$diaryIDList.')
group by UserDiary.diaryID;';
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $diaryNum = $st->fetchAll();
        $st = null;
        $pdo = null;

        foreach($result as $i=>$value){
            foreach($diaryNum as $num){
                if($value['diaryID'] == (int)$num['diaryID']){
                    $result[$i]['userNum'] = (int)$num['num'];
                }
            }
        }
    }

    $none = Array();
    if(empty($result)){
        $res['result'] = $none;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = "등록된 다이어리가 없습니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
    else{
        $res['result'] = $result;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = "성공적으로 조회되었습니다.";
        echo json_encode($res);
    }
}

function getDiariesMember($data){
    if(!isDiaryUser($data['id'],$data['pathVar'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '다이어리에 등록되지 않은 사용자입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    $query =
"select
       Diary.ID as diaryID,
       UserDiary.diaryName as name,
       0 as userNum,
       User.ID as userID,
       User.name as userName,
       UserImage.URL as userSelfie
from Diary
    left join UserDiary on UserDiary.diaryID = Diary.ID
        and UserDiary.status - truncate(UserDiary.status, -1) in (?,?)
        and UserDiary.status not like 999
        and UserDiary.status%10 not like 0
    left join User on UserDiary.userID = User.ID and User.status not like 99999
    left join UserImage on User.ID = UserImage.userID and UserImage.status not like 0
where UserDiary.diaryID = ? limit ".$data['page'].", 20;";
    $st = $pdo->prepare($query);
    if($data['status']==3) $st->execute([$data['status'],$data['status']+1,$data['pathVar']]);
    else if($data['status']==4) $st->execute([$data['status']-1,$data['status'],$data['pathVar']]);
    else $st->execute([$data['status'],$data['status']+2,$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    $diaryIDArr = Array();
    foreach($result as $i => $value){
        $result[$i]['diaryID'] = (int)$value['diaryID'];
        array_push($diaryIDArr,$result[$i]['diaryID']);
        $result[$i]['userID'] = (int)$value['userID'];
    }
    $diaryIDList = implode(',',$diaryIDArr);

    if(!empty($diaryIDArr)){
        $pdo = pdoSqlConnect();
        $query =
            'select
       count(UserDiary.userID) as num,
       UserDiary.diaryID as diaryID
from UserDiary
    left join User on User.ID = UserDiary.userID
where User.status not like 99999
and UserDiary.status not like 999
and UserDiary.status%10 not like 0
and UserDiary.diaryID in ('.$diaryIDList.')
group by UserDiary.diaryID;';
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $diaryNum = $st->fetchAll();
        $st = null;
        $pdo = null;

        foreach($result as $i=>$value){
            foreach($diaryNum as $num){
                if($value['diaryID'] == (int)$num['diaryID']){
                    $result[$i]['userNum'] = $num['num'];
                }
            }
        }
    }

    $none = Array();
    if(empty($result)){
        $res['result'] = $none;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '멤버가 존재하지 않습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
    else{
        $res['result'] = $result;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '성공적으로 조회되었습니다.';
        echo json_encode($res);
    }
}