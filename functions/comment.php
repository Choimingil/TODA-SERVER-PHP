<?php

function isValidComment($commentId): int
{
    $query = "SELECT EXISTS(SELECT * FROM Comment WHERE ID = ? and status not like 0) AS exist;";
    $res = execute($query,[$commentId]);
    return intval($res[0]['exist']);
}

function isValidCommentPostDiary($userID, $postID, $commentID): int
{
    $diaryData = implode(',',createCodeArray(1,12,1,4));
    $query = 'SELECT EXISTS(select * from Comment
                left join Post on Post.ID = Comment.postID
                left join Diary on Diary.ID = Post.diaryID
                left join UserDiary on UserDiary.diaryID = Diary.ID
                where UserDiary.userID = ? and Comment.postID = ? and Comment.ID = ?
                and Post.status not like 0 and UserDiary.status in ('.$diaryData.')) AS exist;';
    $res = execute($query,[$userID, $postID, $commentID]);
    return intval($res[0]['exist']);
}

function isCommentUser($userID, $commentID): int
{
    $query = "select EXISTS(select * from Comment where userID = ? and ID = ? and status not like 0) AS exist;";
    $res = execute($query,[$userID, $commentID]);
    return intval($res[0]['exist']);

}

function isExistReComment($commentId){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Comment WHERE parent = ? and status not like 0) AS exist;";
    $st = $pdo->prepare($query);
    $st->execute([$commentId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st=null;$pdo = null;
    return intval($res[0]['exist']);
}

function getFinalPageComment($postID){
    $pdo = pdoSqlConnect();
    $query = 'select count(*) as num from Comment where postID = ? and status not like 0 and parent like 0;';
    $st = $pdo->prepare($query);
    $st->execute([$postID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;$pdo = null;
    if($res[0]['num'] == 0) return 1;
    else return floor(($res[0]['num']-1)/20)+1;
}

function getReCommentCount($commentID){
    $pdo = pdoSqlConnect();
    $query = 'select count(*) as num from Comment where status not like 0 and parent like ?;';
    $st = $pdo->prepare($query);
    $st->execute([$commentID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;$pdo = null;
    return $res[0]['num'];
}

function getCommentParent($commentID){
    $pdo = pdoSqlConnect();
    $query = 'select parent as parentID from Comment where status not like 0 and ID like ?;';
    $st = $pdo->prepare($query);
    $st->execute([$commentID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;$pdo = null;
    return $res[0]['parentID'];
}