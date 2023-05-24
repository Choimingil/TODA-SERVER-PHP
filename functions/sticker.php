<?php

function isValidSticker($stickerID): int
{
    $query = "SELECT EXISTS(SELECT * FROM Sticker left join StickerPack SP on Sticker.stickerPackID = SP.ID WHERE Sticker.ID = ? and SP.status not like 0) AS exist;";
    $res = execute($query,[$stickerID]);
    return intval($res[0]['exist']);
}

function isValidStickerPack($stickerPackID): int
{
    $query = "SELECT EXISTS(SELECT * FROM StickerPack WHERE ID = ? and status not like 0) AS exist;";
    $res = execute($query,[$stickerPackID]);
    return intval($res[0]['exist']);
}

function isValidUserSticker($userID, $stickerID): int
{
    $query = "select exists(select U.ID from UserSticker U left join Sticker S on U.stickerPackID = S.stickerPackID
            where U.userID = ? and S.ID = ?) as exist;";
    $res = execute($query,[$userID, $stickerID]);
    return intval($res[0]['exist']);
}

function isStickerSet($userID){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from UserSticker where userID = ? and stickerPackID in (1,2,3,4)) as exist";
    $st = $pdo->prepare($query);
    $st->execute([$userID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st=null;$pdo = null;
    if($res[0]['exist']==1) return true;
    else return false;
}

function setBasicStickers($userID){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO UserSticker(userID, stickerPackID) VALUES (?,1), (?,2), (?,3), (?,4)";
    $st = $pdo->prepare($query);
    $st->execute([$userID,$userID,$userID,$userID]);
    $st = null;
    $pdo = null;
}

function getStickerIDAndImage($queryStringList,$usedStickerIDArray){
    $pdo = pdoSqlConnect();
    $query = "select Sticker.ID as stickerID, Sticker.URL as image from Sticker left join PostSticker on Sticker.ID = PostSticker.stickerID
            where PostSticker.ID in ($queryStringList)";
    $st = $pdo->prepare($query);
    $st->execute($usedStickerIDArray);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st=null;$pdo = null;
    return $res;
}

function isMySticker($userID,$usedStickerID){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from PostSticker where userID = ? and ID = ?) as exist";
    $st = $pdo->prepare($query);
    $st->execute([$userID,$usedStickerID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st=null;$pdo = null;
    if($res[0]['exist']==1) return true;
    else return false;
}

function isValidStickerView($stickerViewID){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from PostSticker where ID = ? and status not like 0) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$stickerViewID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]['exist']);
}

function isValidUserStickerView($id, $usedStickerID){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from PostSticker where userID = ? and ID = ? and status not like 0) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id, $usedStickerID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]['exist']);
}

function isValidInversion($inversion){
    switch ($inversion){
        case 1:
        case 2:
        case 0:
        case 3:
            return true;
            break;

        default:
            return false;

    }
}

function getFinalPageSticker($postID){
    $pdo = pdoSqlConnect();
    $query = 'select count(*) as num from PostSticker where postID = ? and status not like 0;';
    $st = $pdo->prepare($query);
    $st->execute([$postID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;$pdo = null;
    if($res[0]['num'] == 0) return 1;
    else return floor(($res[0]['num']-1)/20)+1;
}