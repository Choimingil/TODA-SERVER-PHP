<?php

function postNotice($data){
    if(!isDiaryUser($data['id'],$data['diary'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '다이어리에 등록되지 않은 사용자입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    if(isValidNotice($data['diary'])){
        $pdo = pdoSqlConnect();
        $query = 'UPDATE Notice SET userID = ?, notice = ? WHERE diaryID = ?;';
        $st = $pdo->prepare($query);
        $st->execute([$data['id'], $data['notice'], $data['diary']]);
        $st = null;
        $pdo = null;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '다이어리 공지가 등록되었습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    if(isDeletedNotice($data['diary'])){
        $pdo = pdoSqlConnect();
        $query = 'UPDATE Notice SET userID = ?, notice = ?, status = 100 WHERE diaryID = ?;';
        $st = $pdo->prepare($query);
        $st->execute([$data['id'], $data['notice'], $data['diary']]);
        $st = null;
        $pdo = null;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '다이어리 공지가 등록되었습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    else{
        $pdo = pdoSqlConnect();
        $query = 'INSERT INTO Notice (diaryID, userID, notice) VALUES (?, ?, ?);';
        $st = $pdo->prepare($query);
        $st->execute([$data['diary'], $data['id'], $data['notice']]);
        $st = null;
        $pdo = null;

        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '다이어리 공지가 등록되었습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
}

function deleteNotice($data){
    if(!isDiaryUser($data['id'],$data['pathVar'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '다이어리에 등록되지 않은 사용자입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    if(!isValidNotice($data['pathVar'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '존재하지 않는 공지입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $pdo = pdoSqlConnect();
    $query = 'UPDATE Notice SET status = 0 WHERE diaryID = ?';
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '다이어리 공지가 삭제되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}

function updateNotice($data){
    if(!isDiaryUser($data['id'],$data['diary'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '다이어리에 등록되지 않은 사용자입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    if(!isValidNotice($data['diary'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '존재하지 않는 공지입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $pdo = pdoSqlConnect();
    $query = 'UPDATE Notice SET userID = ?, notice = ? WHERE diaryID = ?;';
    $st = $pdo->prepare($query);
    $st->execute([$data['id'], $data['notice'], $data['diary']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '다이어리 공지 수정이 완료되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}

function getNotice($data){
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
       Notice.diaryID as diaryID, 
       Diary.Name as diaryName, 
       Notice.userID as userID, 
       User.name as userName, 
       Notice.notice as notice, 
       TIMESTAMPDIFF(SECOND, Notice.updateAt, now()) as date
from Notice 
    left join Diary on Diary.ID = Notice.diaryID 
    left join User on User.ID = Notice.userID
where Notice.diaryID = ? 
    and Notice.status not like 0;";
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    foreach ($result as $i=>$value){
        $result[$i]['date'] = convertDate($result[$i]['date']);
        $result[$i]['diaryID'] = (int)$result[$i]['diaryID'];
        $result[$i]['userID'] = (int)$result[$i]['userID'];
    }

    $none = Array(
        'diaryID' => $data['pathVar'],
        'diaryName' => 'none',
        'userID' => $data['id'],
        'userName' => 'none',
        'notice' => '아직 등록된 공지가 없습니다 :D',
        'date' => 'none'
    );

    if(empty($result) || $result[0]['notice']==''){
        $res['result'] = $none;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = "성공적으로 조회되었습니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
    else{
        $res['result'] = $result[0];
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = "성공적으로 조회되었습니다.";
        echo json_encode($res);
    }
}