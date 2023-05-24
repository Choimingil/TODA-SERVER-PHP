<?php

function addSchedule($data){
    if(!isDiaryUser($data['id'], $data['diary'])){
        $res['isSuccess'] = false;
        $res['code'] = 401;
        $res['message'] = "다이어리에 등록된 유저가 아닙니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Calender (diaryID, date, name) VALUES (?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$data['diary'], $data['date'], $data['title']]);

    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "일정이 추가되었습니다.";
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}

function deleteSchedule($data){
    if(!isDiaryUserSchedule($data['id'],$data['pathVar'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 418;
        $res['message'] = "일정에 접근할 수 있는 권한이 없습니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    $query = "UPDATE Calender SET status = 0 WHERE ID = ?";

    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "일정이 삭제되었습니다.";
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}

function updateSchedule($data){
    if(!isDiaryUserSchedule($data['id'],$data['schedule'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 418;
        $res['message'] = "일정에 접근할 수 있는 권한이 없습니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    $query = "UPDATE Calender SET date = ?, name = ? WHERE ID = ?";

    $st = $pdo->prepare($query);
    $st->execute([$data['date'], $data['title'], $data['schedule']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "일정이 수정되었습니다.";
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}

function getSchedule($data){
    if(!isDiaryUser($data['id'], $data['pathVar'])){
        $res['isSuccess'] = false;
        $res['code'] = 401;
        $res['message'] = "다이어리에 등록된 유저가 아닙니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    $query =
        "select diaryID, ID as scheduleID, 
       case
           when TIMESTAMPDIFF(SECOND, date, now()) < 60
               then concat(floor(TIMESTAMPDIFF(SECOND, date, now())),'초 전')
           when TIMESTAMPDIFF(SECOND, date, now()) < 60*60
               then concat(floor(TIMESTAMPDIFF(SECOND, date, now())/60),'분 전')
           when TIMESTAMPDIFF(SECOND, date, now()) < 60*60*24
               then concat(floor(TIMESTAMPDIFF(SECOND, date, now())/(60*60)),'시간 전')
           when TIMESTAMPDIFF(SECOND, date, now()) < 60*60*24*30
               then concat(floor(TIMESTAMPDIFF(SECOND, date, now())/(60*60*24)),'일 전')
           when TIMESTAMPDIFF(SECOND, date, now()) < 60*60*24*30*365
               then concat(floor(TIMESTAMPDIFF(SECOND, date, now())/(60*60*24*30)),'달 전')
           else concat(floor(TIMESTAMPDIFF(SECOND, date, now())/(60*60*60*24*30)),'년 전')
        end as date, name from Calender where diaryID = ? and status not like 0 limit ".$data['page'].", 20;";

    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();

    $st = null;
    $pdo = null;

    for($i=0;$i<sizeof($result);$i++){
        $result[$i]['diaryID'] = (int)$result[$i]['diaryID'];
        $result[$i]['scheduleID'] = (int)$result[$i]['scheduleID'];
    }

    $none = Array(
        'message' => "등록된 일정이 없습니다."
    );

    if(empty($result)){
        $res['result'] = $none;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = "성공적으로 조회되었습니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    else{
        $res['result'] = $result;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = "성공적으로 조회되었습니다.";
        echo json_encode($res);
        return;
    }
}