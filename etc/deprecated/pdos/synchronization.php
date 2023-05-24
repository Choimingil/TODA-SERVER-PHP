<?php

use JetBrains\PhpStorm\ArrayShape;

function getSynchronization($userID, $page){
    switch ($page){

        // User
        case 1:
            $array = Array(
                'ID' => false,
                'name' => true,
                'code' => true,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in =
'select distinct
       User.ID,
       User.name,
       User.code,
       User.status,
       User.createAt,
       User.updateAt
from User
inner join UserDiary on UserDiary.userID = User.ID
where UserDiary.diaryID in (select diaryID from UserDiary where userID = ? and status not like 999);';
            $in2 =
'select
       User.ID,
       User.name,
       User.code,
       User.status,
       User.createAt,
       User.updateAt
from User
where User.ID = ?';
            $out = 'INSERT INTO User (ID, name, code, status, createAt, updateAt) VALUES ';
            $res = getQueries(1,$array,$in,$out,$userID);
            if($res['query']=="") $res = getQueries(1,$array,$in2,$out,$userID);
            return $res;

        // Profile
        case 2:
            $array = Array(
                'userID' => false,
                'URL' => true,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in =
'select userID, URL, status, createAt, updateAt from UserImage
where userID in 
      (select userID from UserDiary where diaryID in (select diaryID from UserDiary where userID = ? and status not like 999))
  and status not like 0;';
            $in2 =
'select userID, URL, status, createAt, updateAt from UserImage where userID = ? and status not like 0;';
            $out = 'INSERT INTO Profile (userID, URL, status, createAt, updateAt) VALUES ';
            $res = getQueries(2,$array,$in,$out,$userID);
            if($res['query']=="") $res = getQueries(2,$array,$in2,$out,$userID);
            return $res;

        // Notification
        case 3:
            $array = Array(
                'userID' => false,
                'device' => false,
                'token' => true,
                'time' => true,
                'status' => true,
                'createAt' => true,
                'updateAt' => true
            );
            $in =
"select userID,status as device, token, time,
    case
        when isAllowed = 'Y' and isRemindAllowed = 'Y' and isEventAllowed = 'Y'
            then '111'
        when isAllowed = 'Y' and isRemindAllowed = 'Y' and isEventAllowed = 'N'
            then '110'
        when isAllowed = 'Y' and isRemindAllowed = 'N' and isEventAllowed = 'Y'
            then '101'
        when isAllowed = 'N' and isRemindAllowed = 'Y' and isEventAllowed = 'Y'
            then '011'
        when isAllowed = 'Y' and isRemindAllowed = 'N' and isEventAllowed = 'N'
            then '100'
        when isAllowed = 'N' and isRemindAllowed = 'Y' and isEventAllowed = 'N'
            then '010'
        when isAllowed = 'N' and isRemindAllowed = 'N' and isEventAllowed = 'Y'
            then '001'
        when isAllowed = 'N' and isRemindAllowed = 'N' and isEventAllowed = 'N'
            then '000'
        end as status,createAt,updateAt
from Notification
where userID in
      (select userID from UserDiary where diaryID in (select diaryID from UserDiary where userID = ? and status not like 999))
and status not like 0 and userID not like ?;";
            $out = 'INSERT INTO Notification (userID,device,token,time,status,createAt,updateAt) VALUES ';
            return getQueries(3,$array,$in,$out,$userID);

        // Announcement
        case 4:
            $array = Array(
                'title' => true,
                'text' => true,
                'image' => true,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in = "select title,text,image,
                    if(exists(
                        select * from UserAnnouncement where userID = ? and announcementID = Announcement.ID),200,100
                    ) as status,createAt,updateAt
                    from Announcement;";
            $out = 'INSERT INTO Announcement (title,text,image,status,createAt,updateAt) VALUES ';
            return getQueries(4,$array,$in,$out,$userID);

        // Diary
        case 5:
            $array = Array(
                'ID' => false,
                'serverID' => false,
                'diaryName' => true,
                'diaryNum' => false,
                'notice' => true,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in =
"select
       FLOOR(1 + RAND() * 1000000000) as ID,
       UserDiary.diaryID as serverID,
       UserDiary.diaryName as diaryName,
       ifnull(userNum.num,0) as diaryNum,
       ifnull(
           if(Notice.status=0 || Notice.notice='','아직 등록된 공지가 없습니다 :D',Notice.notice)
           ,'아직 등록된 공지가 없습니다 :D') as notice,
       if(UserDiary.status%10=0,Diary.status*10,UserDiary.status) as status,
       UserDiary.createAt as createAt,
       UserDiary.updateAt as updateAt
from UserDiary
left join Notice on Notice.diaryID = UserDiary.diaryID
inner join Diary on Diary.ID = UserDiary.diaryID
left join (
        select count(UserDiary.userID) as num,
               UserDiary.diaryID as diaryID
        from UserDiary
            left join User on User.ID = UserDiary.userID
        where User.status not like 99999 and UserDiary.status%10 not like 0 and UserDiary.status not like 999
        group by UserDiary.diaryID
        ) userNum on userNum.diaryID = UserDiary.diaryID
where UserDiary.userID = ?;";
            $out = 'INSERT INTO Diary (ID,serverID,diaryName,diaryNum,notice,status,createAt,updateAt) VALUES ';
            return getQueries(5,$array,$in,$out,$userID);

        // FriendDiary
        case 6:
            $array = Array(
                'userID' => false,
                'diaryID' => false,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in = 'select userID, diaryID, if(status=999,999,100) as status, createAt, updateAt from UserDiary where diaryID in (select diaryID from UserDiary where userID = ?);';
            $out = 'INSERT INTO FriendDiary (userID,diaryID,status,createAt,updateAt) VALUES ';
            return getQueries(6,$array,$in,$out,$userID);

        // Post
        case 7:
            $array = Array(
                'ID' => false,
                'userID' => false,
                'diaryID' => false,
                'serverID' => false,
                'title' => true,
                'isMyLike' => false,
                'likeNum' => false,
                'commentNum' => false,
                'status' => false,
                'date' => true,
                'createAt' => true,
                'updateAt' => true
            );
            $in =
"select
       FLOOR(1 + RAND() * 1000000000) as ID,
       Post.userID as userID,
       concat('(select ID from Diary where serverID = ',Post.diaryID,')') as diaryID,
       Post.ID as serverID,
       Post.title as title,
       if(h.userID = Post.userID,1,0) as isMyLike,
       ifnull(h.num,0) as likeNum,
       ifnull(c.num,0) as commentNum,
       Post.status as status,
       ifnull(Post.createAt,now()) as date,
       Post.updateAt as createAt,
       Post.updateAt as updateAt
from Post
left join (select Heart.postID as postID, Heart.userID as userID, count(*) as num from Heart where status not like 0 group by Heart.postID)
h on h.postID = Post.ID
left join (select Comment.postID as postID, count(*) as num from Comment where status not like 0 group by Comment.postID)
c on c.postID = Post.ID
where Post.diaryID in (select diaryID from UserDiary where userID = ? and status not like 999 and status%10 not like 0) and substr(Post.status,-length(Post.status)+2)<8;";
            $out = 'INSERT INTO Post (ID,userID,diaryID,serverID,title,isMyLike,likeNum,commentNum,status,date,createAt,updateAt) VALUES ';
            return getQueries(7,$array,$in,$out,$userID);

        // PostText
        case 8:
            $array = Array(
                'postID' => false,
                'text' => true,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in =
"select
       concat('(select ID from Post where serverID = ',Post.ID,')') as postID,
       PostText.text as text,
       if(PostText.aligned<10,PostText.aligned*100+1,PostText.aligned) as status,
       PostText.createAt as createAt,
       PostText.updateAt as updateAt
from PostText
inner join Post on Post.ID = PostText.postID
where Post.diaryID in (select diaryID from UserDiary where userID = ? and status not like 999 and status%10 not like 0);";
            $out = 'INSERT INTO PostText (postID,text,status,createAt,updateAt) VALUES ';
            return getQueries(8,$array,$in,$out,$userID);

        // PostImage
        case 9:
            $array = Array(
                'postID' => false,
                'URL' => true,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in =
"select
       concat('(select ID from Post where serverID = ',Post.ID,')') as postID,
       PostImage.url as URL,
       PostImage.status as status,
       PostImage.createAt as createAt,
       PostImage.updateAt as updateAt
from PostImage
inner join Post on Post.ID = PostImage.postID
where Post.diaryID in (select diaryID from UserDiary where userID = ? and status not like 999 and status%10 not like 0) and PostImage.status not like 0;";
            $out = 'INSERT INTO PostImage (postID,URL,status,createAt,updateAt) VALUES ';
            return getQueries(9,$array,$in,$out,$userID);

        // Heart
        case 10:
            $array = Array(
                'ID'=> false,
                'userID' => false,
                'postID' => false,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in =
"select
       concat('(select ',Heart.userID,'||ID from Post where serverID = ',Heart.postID,')') as ID,
       Heart.userID as userID,
       concat('(select ID from Post where serverID = ',Heart.postID,')') as postID,
       if(Heart.status=999,100,0) as status,
       Heart.createAt as createAt,
       Heart.updateAt as updateAt
from Heart
inner join Post on Post.ID = Heart.postID
where Post.diaryID in (select diaryID from UserDiary where userID = ? and status not like 999 and status%10 not like 0);";
            $out = 'INSERT INTO Heart (ID,userID,postID,status,createAt,updateAt) VALUES ';
            return getQueries(10,$array,$in,$out,$userID);

        // Comment
        case 11:
            $array = Array(
                'ID' => false,
                'userID' => false,
                'postID' => false,
                'serverID' => false,
                'text' => true,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in =
"select
       FLOOR(1 + RAND() * 1000000000) as ID,
       Comment.userID as userID,
       concat('(select ID from Post where serverID = ',Comment.postID,')') as postID,
       Comment.ID as serverID,
       Comment.text as text,
       if(Comment.status=0,-1,0) as status,
       Comment.createAt as createAt,
       Comment.updateAt as updateAt
from Comment
inner join Post on Post.ID = Comment.postID
where Post.diaryID in (select diaryID from UserDiary where userID = ? and status not like 999 and status%10 not like 0) and Comment.parent = 0;";
            $out = 'INSERT INTO Comment (ID,userID,postID,serverID,text,status,createAt,updateAt) VALUES ';
            return getQueries(11,$array,$in,$out,$userID);

        // ReComment
        case 12:
            $array = Array(
                'ID' => false,
                'userID' => false,
                'postID' => false,
                'serverID' => false,
                'text' => true,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in =
"select
       FLOOR(1 + RAND() * 1000000000) as ID,
       Comment.userID as userID,
       concat('(select ID from Post where serverID = ',Comment.postID,')') as postID,
       Comment.ID as serverID,
       Comment.text as text,
       if(Comment.status=0,-1,concat('(select ifnull((select ID from Comment where serverID = ',Comment.parent,'),0))')) as status,
       Comment.createAt as createAt,
       Comment.updateAt as updateAt
from Comment
inner join Post on Post.ID = Comment.postID
where Post.diaryID in
      (select diaryID from UserDiary where userID = ? and status not like 999 and status%10 not like 0)
  and Comment.parent not like 0;";
            $out = 'INSERT INTO Comment (ID,userID,postID,serverID,text,status,createAt,updateAt) VALUES ';
            return getQueries(12,$array,$in,$out,$userID);

        // StickerPack
        case 13:
            $array = Array(
                'ID' => false,
                'name' => true,
                'point' => false,
                'thumbnail' => true,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in =
"select
       StickerPack.ID,
       StickerPack.name,
       StickerPack.point,
       StickerPack.miniticon as thumbnail,
       ifnull(UserSticker.status,0) as status,
       StickerPack.createAt,
       StickerPack.updateAt
from StickerPack
left join
    UserSticker on UserSticker.stickerPackID = StickerPack.ID and UserSticker.userID = ?
where StickerPack.status not like 0;";
            $out = 'INSERT INTO StickerPack (ID,name,point,thumbnail,status,createAt,updateAt) VALUES ';
            return getQueries(13,$array,$in,$out,$userID);

        // StickerList
        case 14:
            $array = Array(
                'ID' => false,
                'stickerPackID' => false,
                'URL' => true,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in = "select ID,stickerPackID,URL,100 as status,createAt,updateAt from Sticker;";
            $out = 'INSERT INTO StickerList (ID,stickerPackID,URL,status,createAt,updateAt) VALUES ';
            return getQueries(14,$array,$in,$out,$userID);

        // PostSticker
        case 15:
            $array = Array(
                'userID' => false,
                'postID' => false,
                'stickerID' => false,
                'x' => false,
                'y' => false,
                'deviceWidth' => false,
                'deviceHeight' => false,
                'width' => false,
                'height' => false,
                'a' => false,
                'b' => false,
                'c' => false,
                'd' => false,
                'tx' => false,
                'ty' => false,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in =
"select
       PostSticker.userID as userID,
       concat('(select ID from Post where serverID = ',PostSticker.postID,')') as postID,
       PostSticker.stickerID as stickerID,
       PostSticker.x as x,
       PostSticker.y as y,
       PostSticker.device as deviceWidth,
       PostStickerScale.y as deviceHeight,
       PostStickerScale.width as width,
       PostStickerScale.height as height,
       PostStickerRotate.a as a,
       PostStickerRotate.b as b,
       PostStickerRotate.c as c,
       PostStickerRotate.d as d,
       PostStickerRotate.tx as tx,
       PostStickerRotate.ty as ty,
       PostSticker.status as status,
       PostSticker.createAt as createAt,
       PostSticker.updateAt as updateAt
from PostSticker
inner join Post on Post.ID = PostSticker.postID
inner join PostStickerRotate on PostStickerRotate.usedStickerID = PostSticker.ID
inner join PostStickerScale on PostStickerScale.usedStickerID = PostSticker.ID
where Post.diaryID in (select diaryID from UserDiary where userID = ? and status not like 999 and status%10 not like 0);";
            $out = 'INSERT INTO PostSticker (userID,postID,stickerID,x,y,deviceWidth,deviceHeight,width,height,a,b,c,d,tx,ty,status,createAt,updateAt) VALUES ';
            return getQueries(15,$array,$in,$out,$userID);

        // UserLog
        case 16:
            $array = Array(
                'sendID' => false,
                'type' => false,
                'typeID' => false,
                'status' => false,
                'createAt' => true,
                'updateAt' => true
            );
            $in =
"select distinct sendID, type,

ifnull(
case
when type = '1' then concat('(select case when exists(select ID from Diary where serverID = ',(select diaryID from UserDiary where userID  = Log.receiveID and diaryID = Log.typeID),') = 0 then 0 else(select ID from Diary where serverID = ',(select diaryID from UserDiary where userID  = Log.receiveID and diaryID = Log.typeID),') end)')
when type = '2' then concat('(select case when exists(select ID from Diary where serverID = ',(select diaryID from UserDiary where userID  = Log.receiveID and diaryID = Log.typeID),') = 0 then 0 else(select ID from Diary where serverID = ',(select diaryID from UserDiary where userID  = Log.receiveID and diaryID = Log.typeID),') end)')
when type = '3' then concat('(select case when exists(select ID from Post where serverID = ',(select ID from Post where ID = Log.typeID),') = 0 then 0 else(select ID from Post where serverID = ',(select ID from Post where ID = Log.typeID),') end)')
when type = '4' then concat('(select case when exists(select ID from Post where serverID = ',(select ID from Post where ID = Log.typeID),') = 0 then 0 else(select ID from Post where serverID = ',(select ID from Post where ID = Log.typeID),') end)')
when type = '5' then concat('(select case when exists(select ID from Comment where serverID = ',(select ID from Comment where ID = Log.typeID and Comment.parent = 0),') = 0 then 0 else(select ID from Comment where serverID = ',(select ID from Comment where ID = Log.typeID and Comment.parent = 0),') end)')
when type = '6' then concat('(select case when exists(select ID from Comment where serverID = ',(select ID from Comment where ID = Log.typeID and Comment.parent not like 0),') = 0 then 0 else(select ID from Comment where serverID = ',(select ID from Comment where ID = Log.typeID and Comment.parent not like 0),') end)')
end,0
)as typeID,

status, createAt, updateAt

from Log
where receiveID = ? and status not like 999;";
            $out = 'INSERT INTO UserLog (sendID,type,typeID,status,createAt,updateAt) VALUES ';
            return getQueries(16,$array,$in,$out,$userID);

        default:
            new DefaultResponse(false,404,'없는 페이지');
            return false;
    }
}


#[ArrayShape(['type' => "", 'query' => "string"])]
function getQueries($index, $array, $in, $out, $userID): array
{
    if($index == 14) $result = execute($in,[]);
    else if($index == 3) $result = execute($in,[$userID,$userID]);
    else if($index == 16){
        $result = execute($in,[$userID]);
        $newRes = Array();
        foreach ($result as $i=>$value) if($value['typeID']!=0) array_push($newRes,$value);
        $result = $newRes;
    }
    else $result = execute($in,[$userID]);

    if(empty($result)) $res = "";
    else{
        $bodyArray = Array();
        foreach ($result as $value){
            $dataArray = Array();
            foreach ($array as $key=>$isString){
                if($isString) array_push($dataArray,setString($value[$key]));
                else array_push($dataArray,$value[$key]);
            }
            $data = implode(',',$dataArray);
            array_push($bodyArray, '('.$data.')');
        }
        $body = implode(',',$bodyArray);
        $res = $out.$body.';';
    }

    return Array(
        'index' => $index,
        'query' => $res
    );
}