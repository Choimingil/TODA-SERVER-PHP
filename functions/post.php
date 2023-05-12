<?php

function isValidPost($postID): int
{
    $query = "SELECT EXISTS(SELECT * FROM Post WHERE ID = ? and status not like 0) AS exist;";
    $res = execute($query,[$postID]);
    return intval($res[0]['exist']);
}

function isValidBackground($code): bool
{
    return $code > 0 && $code < 8; // 추후 업데이트 시 99까지
    //return $code > 0 && $code < 100;
}

function isValidAligned($code): bool
{
    return match ($code) {
        1, 2, 3 => true,
        default => false,
    };
}

function isValidMood($code): bool
{
    return ($code > 0 && $code < 8 || $code = 999); // 추후 업데이트 시 99까지
    //return $code > 0 && $code < 100;
}

function isValidPostDiary($userID, $postID): int
{
    $diaryData = implode(',',createCodeArray(1,12,1,4));
    $query = 'SELECT EXISTS(select * from Post
                left join Diary on Diary.ID = Post.diaryID
                left join UserDiary on UserDiary.diaryID = Diary.ID
                where UserDiary.userID = ? and Post.ID = ? and Post.status not like 0
                and UserDiary.status in ('.$diaryData.')) AS exist;';
    $res = execute($query,[$userID, $postID]);
    return intval($res[0]['exist']);
}

function isValidMoodString($mood){
    if(strlen($mood)>4) return false;

    $value = substr($mood,-2,2);
    return match ($value) {
        '01', '02', '03', '04', '05', '06', '07' => true,
        default => false,
    };
}

function isMyPost($userID,$postID){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from Post where userID = ? and ID = ?) as exist";
    $st = $pdo->prepare($query);
    $st->execute([$userID,$postID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st=null;$pdo = null;
    if($res[0]['exist']==1) return true;
    else return false;
}

function isExistLike($userID, $postID): int
{
    $query = "select EXISTS(select * from Heart where userID = ? and postID = ?) AS exist;";
    $res = execute($query,[$userID, $postID]);
    return intval($res[0]['exist']);
}

function isExistURL($postID){
    $pdo = pdoSqlConnect();
    $query = 'select url from PostImage where postID=?';
    $st = $pdo->prepare($query);
    $st->execute([$postID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;$pdo = null;
    return $res;
}

function isSameMood($userID, $postID, $mood): bool
{
    $query = "select status from Heart where userID = ? and postID = ?;";
    $res = execute($query,[$userID, $postID]);
    if($res[0]['status'] == $mood) return true;
    else return false;
}

function isPostUser($userID, $postID): int
{
    $query = "select EXISTS(select * from Post where userID = ? and ID = ? and status not like 0) AS exist;";
    $res = execute($query,[$userID, $postID]);
    return intval($res[0]['exist']);
}

function isExistImage($postID): int
{
    $query = 'select EXISTS(select url from PostImage where postID=?) as exist;';
    $res = execute($query,[$postID]);
    return intval($res[0]['exist']);
}

function isValidLike($userID, $postID){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from Heart where userID = ? and postID = ? and status not like 0) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userID, $postID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]['exist']);
}

function getMood($userID, $postID){
    if(!isset($userID) ||!isset($postID)){
        return false;
    }

    $pdo = pdoSqlConnect();
    $query = "select status from Heart where userID = ? and postID = ?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userID, $postID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return $res[0]['status'];
}

function getPostUser($postID){
    $query =
'select
       User.ID as ID,
       User. name as name
from Post
inner join User on User.ID = Post.userID
where Post.status not like 0 and Post.ID like ?;';
    $res = execute($query,[$postID]);
    return $res[0];
}

function getPostDiary($postID){
    $query = 'select diaryID from Post where status not like 0 and ID like ?;';
    $res = execute($query,[$postID]);
    return $res[0]['diaryID'];
}

function getURL($postID){
    $query = 'select url from PostImage where postID=?';
    return execute($query,[$postID]);
}

function getFinalPagePost($diaryID){
    $pdo = pdoSqlConnect();
    $query = 'select count(*) as num from Post where diaryID = ? and status not like 0;';
    $st = $pdo->prepare($query);
    $st->execute([$diaryID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;$pdo = null;
    if($res[0]['num'] == 0) return 1;
    else return floor(($res[0]['num']-1)/20)+1;
}

function getUserToPost($userID){
    $pdo = pdoSqlConnect();
    $query = 'select ID as ID from Post where status not like 0 and userID like ?;';
    $st = $pdo->prepare($query);
    $st->execute([$userID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;$pdo = null;
    return $res;
}

function getPostToUser($postID){
    $query = 'select userID as ID from Post where status not like 0 and ID like ?;';
    $res = execute($query,[$postID]);
    return $res[0]['ID'];
}

function getPostDate($postID){
    $pdo = pdoSqlConnect();
    $query =
        'select TIMESTAMPDIFF(SECOND, updateAt, now()) as date
from Post where status not like 0 and ID like ?;';
    $st = $pdo->prepare($query);
    $st->execute([$postID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;$pdo = null;
    return $res[0]['date'];
}