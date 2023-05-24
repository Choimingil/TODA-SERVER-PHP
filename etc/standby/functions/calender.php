<?php

function isValidSchedule($scheduleID){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Calender WHERE ID = ? and status not like 0) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$scheduleID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]['exist']);

}

function isDiaryUserSchedule($id, $scheduleID){
    if(!isset($id) || !isset($scheduleID)){
        return false;
    }
    $diaryData = createCodeArray(1,12,1,4);

    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(select * from Calender
                left join Diary on Diary.ID = Calender.diaryID
                left join UserDiary on UserDiary.diaryID = Diary.ID
                where UserDiary.userID = ? and Calender.ID = ? and Calender.status not like 0 
                and UserDiary.status in (".$diaryData.")) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id, $scheduleID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]['exist']);

}