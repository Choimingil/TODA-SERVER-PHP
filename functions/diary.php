<?php

function isValidDiary($diaryID): int
{
    $query = "SELECT EXISTS(SELECT * FROM Diary WHERE ID = ?) AS exist;";
    $res = execute($query,[$diaryID]);
    return intval($res[0]['exist']);
}

function isValidStatus($status): bool
{
    return match ($status) {
        1, 2, 3 => true,
        default => false
    };
}

function isValidDiaryStatus($status): bool
{
    $diaryData = createCodeArray(1,12,1,4);
    if(array_search($status,$diaryData) == false) return false;
    else return true;
}

function isSendRequest($userID, $diaryID): int
{
    $query = 'SELECT EXISTS(SELECT * FROM UserDiary WHERE userID = ? and diaryID= ? and status%10 like 0) AS exist;';
    $res = execute($query,[$userID, $diaryID]);
    return intval($res[0]['exist']);
}

function isDiaryUser($userID, $diaryID): int
{
    $diaryData = implode(',',createCodeArray(1,12,1,4));
    $query = 'select EXISTS(select * from UserDiary where userID = ? and diaryID = ? and status in ('.$diaryData.')) AS exist;';
    $res = execute($query,[$userID, $diaryID]);
    return intval($res[0]['exist']);
}

function isAloneDiary($diaryID): int
{
    $query = "SELECT EXISTS(SELECT * FROM Diary WHERE ID = ? and status - truncate(status, -1) = 2) AS exist;";
    $res = execute($query,[$diaryID]);
    return intval($res[0]['exist']);

}

function isDeletedDiaryUser($userID, $diaryID): int
{
    $query = "select EXISTS(select * from UserDiary where userID = ? and diaryID = ? and status = 999) AS exist;";
    $res = execute($query,[$userID, $diaryID]);
    return intval($res[0]['exist']);
}

function isExistNotice($diaryID): int
{
    $query = "SELECT EXISTS(SELECT * FROM Notice WHERE diaryID= ? and status not like 0) AS exist;";
    $res = execute($query,[$diaryID]);
    return intval($res[0]['exist']);
}

function isDeletedNotice($diaryID): int
{
    $query = "SELECT EXISTS(SELECT * FROM Notice WHERE diaryID= ? and status like 0) AS exist;";
    $res = execute($query,[$diaryID]);
    return intval($res[0]['exist']);
}

function isExistTmp($data){
    $pdo = pdoSqlConnect();
    $query = "select tmpID from UserDiary where userID = ? and diaryID = ?";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$data['id'],$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st=null;$pdo = null;
    return $res[0]['tmpID'];
}

function isValidNotice($diary){
    if(!isset($diary)){
        return false;
    }

    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Notice WHERE diaryID= ? and status not like 0) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$diary]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]['exist']);

}

function isAlreadyBookmarked($diaryID){
    if(!isset($diaryID)){
        return false;
    }

    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Diary WHERE ID = ? and status - truncate(status, -1) = 3) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$diaryID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]['exist']);

}

function getDiaryMemberID($diaryID){
    $pdo = pdoSqlConnect();
    $query = 'select userID as ID from UserDiary where diaryID = ? and status not like 0;';
    $st = $pdo->prepare($query);
    $st->execute([$diaryID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;$pdo = null;
    return $res;
}

function getFinalPageDiary($userID, $status){
    $pdo = pdoSqlConnect();
    $query = 'select count(*) as num from UserDiary where userID = ? and status - truncate(status, -1) like ?;';
    $st = $pdo->prepare($query);
    $st->execute([$userID,$status]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;$pdo = null;
    if($res[0]['num'] == 0) return 1;
    else return floor(($res[0]['num']-1)/20)+1;
}

function getUserToDiary($userID){
    $pdo = pdoSqlConnect();
    $query = 'select diaryID as ID from UserDiary where status not like 999 and userID like ?;';
    $st = $pdo->prepare($query);
    $st->execute([$userID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;$pdo = null;
    return $res;
}

function getDiaryStatus($userID,$diaryID){
    $query = 'select status as status from UserDiary where userID = ? and diaryID = ? and status not like 0;';
    $res = execute($query,[$userID, $diaryID]);
    return $res[0]['status'];
}

function createCodeArray($colorStart, $colorFinish, $statusStart, $statusFinish): array
{
    $diaryDataArray = Array(0);
    for($i=$colorStart;$i<$colorFinish+1;$i++){
        for($j=$statusStart;$j<$statusFinish+1;$j++){
            array_push($diaryDataArray, $i*100+$j);
        }
    }
    return $diaryDataArray;
}
