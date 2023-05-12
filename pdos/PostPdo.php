<?php

function addPostVer3($data){
    if(!isDiaryUser($data['id'], $data['diary'])){
        $res['isSuccess'] = false;
        $res['code'] = 102;
        $res['message'] = '다이어리에 등록된 유저가 아닙니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    // 현재의 시간값 추가
    $currentDateTime = date('H:i:s');
    if(substr($data['date'],-1,1)=='-') $data['date'] = substr($data['date'],0,10);
    $newDate = $data['date'].' '.$currentDateTime;

    // Post 추가
    $pdo = pdoSqlConnect();
    $query = 'INSERT INTO Post (userID, diaryID, title, status, createAt) VALUES (?, ?, ?, ?,?);';
    $st = $pdo->prepare($query);
    $st->execute([$data['id'], $data['diary'], $data['title'], $data['status'], $newDate]);
    $st = null;
    $postID = $pdo->lastInsertId();
    $pdo = null;

    // 본문 저장
    $pdo = pdoSqlConnect();
    $query = 'INSERT INTO PostText (postID, text, aligned) VALUES (?,?,?);';
    $st = $pdo->prepare($query);
    $st->execute([$postID,$data['text'],$data['statusText']]);
    $st = null;
    $pdo = null;

    // 이미지 저장
    $imageArray = array();
    if(!empty($data['imageList'])){
        $imageExecuteValue = Array();
        foreach($data['imageList'] as $i=>$value){
            array_push($imageArray,'(?,?,?)');

            array_push($imageExecuteValue,$postID);
            array_push($imageExecuteValue,$data['imageList'][$i]);
            array_push($imageExecuteValue,100);
        }
        $pdo = pdoSqlConnect();
        $image = implode(',',$imageArray);
        $query = "INSERT INTO PostImage (postID, URL, size) VALUES ".$image.";";
        $st = $pdo->prepare($query);
        $st->execute($imageExecuteValue);
        $st = null;
        $pdo = null;
    }

    $sendData = Array(
        'diary' => (int)$data['diary'],
        'postID' => (int)$postID,
        'user' => $data['id']
    );

//    //알림 받을 사람 데이터 셀렉
//    $pdo = pdoSqlConnect();
//    $diaryData = implode(',',createCodeArray(1,12,1,4));
//    $query =
//        "select distinct
//       User.ID as userID,
//       Notification.token as token,
//       UserDiary.diaryName as diaryName,
//       Notification.status as status
//from User
//    left join UserDiary on UserDiary.userID=User.ID
//    left join Notification on Notification.userID=User.ID
//        and Notification.status not like 0
//        and Notification.isAllowed like 'Y'
//    left join Log on Log.receiveID = User.ID
//where UserDiary.diaryID=?
//  and Log.type = 3 and Log.typeID = ? and Log.sendID = ?
//  and User.status not like 99999
//  and length(ifnull(Notification.token,'none')) > 30
//  and ifnull(Notification.status,99999) not like 99999
//  and User.ID not like ?
//  and UserDiary.status in (".$diaryData.");";
//    $st = $pdo->prepare($query);
//    $st->execute([$sendData['diary'],$sendData['postID'],$sendData['user'],$sendData['user']]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $receive = $st->fetchAll();
//    $st = null;
//    $pdo = null;

    //알림 받을 사람 데이터 셀렉
    $pdo = pdoSqlConnect();
    $query =
        "select distinct
       User.ID as userID,
       Notification.token as token,
       UserDiary.diaryName as diaryName,
       Notification.status as status
from User
    left join UserDiary on UserDiary.userID=User.ID
    left join Notification on Notification.userID=User.ID
        and Notification.status not like 0
        and Notification.isAllowed like 'Y'
where UserDiary.diaryID=?
  and User.status not like 99999
  and length(ifnull(Notification.token,'none')) > 30
  and ifnull(Notification.status,99999) not like 99999
  and User.ID not like ?
  and UserDiary.status not like 999
  and UserDiary.status%10 not like 0;";
    $st = $pdo->prepare($query);
    $st->execute([$sendData['diary'],$sendData['user']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $receive = $st->fetchAll();
    $st = null;
    $pdo = null;

    // 알림 전송
    $sendData = Array(
        'sendname' => IDToName($sendData['user']),
        'diary' => (int)$sendData['diary'],
        'postID' => (int)$sendData['postID'],
        'user' => $sendData['user'],
        'receive' => $receive
    );
    sendToAlarmServer('/push/post',$sendData);

    // 로그 추가
    $logArray = array();
    $logExecute = Array();
    $typeID = 3;

    foreach($receive as $value) {
        $logData = array(
            'receiveID' => $value['userID'],
            'type' => $typeID,
            'typeID' => $sendData['postID'],
            'sendID' => $sendData['user']
        );

        array_push($logArray, '(?,?,?,?)');

        array_push($logExecute, $logData['sendID']);
        array_push($logExecute, $logData['receiveID']);
        array_push($logExecute, $logData['type']);
        array_push($logExecute, $logData['typeID']);
    }

    if(!empty($receive)){
        if(!empty($logArray)){
            $pdo = pdoSqlConnect();
            $log = implode(',',$logArray);
            $query = "INSERT INTO Log (sendID,receiveID,type,typeID) VALUES ".$log.";";
            $st = $pdo->prepare($query);
            $st->execute($logExecute);
            $st = null;
            $pdo = null;
        }
    }

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '게시물 작성이 완료되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function deletePost($data){
    if(!isPostUser($data['id'],$data['pathVar'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '자신이 작성한 게시물이 아닙니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $pdo = pdoSqlConnect();
    $query = 'UPDATE Post SET status = 0 WHERE userID = ? and ID = ?';
    $st = $pdo->prepare($query);
    $st->execute([$data['id'], $data['pathVar']]);
    $st = null;
    $pdo = null;

//    // setting redis
//    $diaryID = postToDiary($data['pathVar']);
//    $finalPage = getFinalPagePost($diaryID);
//    for($i=1;$i<=$finalPage;$i++){
//        $listKey = getListKey('post',$diaryID,$i);
//        setRedis($listKey,0);
//    }

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '게시물 삭제가 완료되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function updatePostVer3($data){
    if(!isPostUser($data['id'],$data['post'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 103;
        $res['message'] = '자신이 작성한 게시물이 아닙니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    // 현재의 시간값 추가
    $currentDateTime = date('H:i:s');
    if(substr($data['date'],-1,1)=='-') $data['date'] = substr($data['date'],0,10);
    $newDate = $data['date'].' '.$currentDateTime;

    $pdo = pdoSqlConnect();
    $query = 'UPDATE Post SET title = ?, status = ?, createAt = ? WHERE ID = ?;
              UPDATE PostText SET text = ?, aligned = ? WHERE postID = ?;
              UPDATE PostImage SET status = 0 WHERE postID = ? and status not like 0;';
    $st = $pdo->prepare($query);
    $st->execute([$data['title'], $data['status'], $newDate, $data['post'],$data['text'], $data['statusText'], $data['post'],$data['post']]);
    $st = null;

    $executeKey=array();
    $executeQuery=array();
    if(!empty($data['imageList'])){
        if(!isExistURL($data['post'])){
            foreach($data['imageList'] as $i=>$value){
                $query = 'INSERT INTO PostImage (postID, URL, size) VALUES ('.$data['post'].', ?, 100);';
                $executeQuery[$i] = $query;
                $executeKey[$i] = $data['imageList'][$i];
            }
        }
        else{
            foreach($data['imageList'] as $i=>$value){
                $isExist = false;
                $remainURL = isExistURL($data['post']);
                foreach($remainURL as $j=>$value2){
                    if($data['imageList'][$i]==$remainURL[$j]['url']){
                        $query = 'UPDATE PostImage SET status = 100 WHERE url = ?;';
                        $executeQuery[$i] = $query;
                        $executeKey[$i] = $remainURL[$j]['url'];
                        $isExist = true;
                        break;
                    }
                }
                if(!$isExist){
                    $query = 'INSERT INTO PostImage (postID, URL, size) VALUES ('.$data['post'].', ?, 100);';
                    $executeQuery[$i] = $query;
                    $executeKey[$i] = $data['imageList'][$i];
                }
            }
        }
        $query = implode($executeQuery);
        $st = $pdo->prepare($query);
        $st->execute($executeKey);
        $st = null;
        $pdo = null;
    }

//    // setting redis
//    $diaryID = postToDiary($data['post']);
//    $finalPage = getFinalPagePost($diaryID);
//    for($i=1;$i<=$finalPage;$i++){
//        $listKey = getListKey('post',$diaryID,$i);
//        setRedis($listKey,0);
//    }

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '게시물 수정이 완료되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function getPostList($data){
    if(!isDiaryUser($data['id'], $data['pathVar'])) return 102;
    else{
        $pdo = pdoSqlConnect();
        $query =
'select
       Post.diaryID as diaryID,
       User.name as name,
       Post.ID as postID,
       Post.title as title,
       TIMESTAMPDIFF(SECOND, Post.updateAt, now()) as date,
       Post.status as mood,
       Post.status as icon,
       0 as isMyLike,
       0 as likeNum,
       0 as commentNum,
       Post.createAt as dateFull
from Post
inner join User on User.ID = Post.userID
where Post.diaryID = ? and Post.status not like 0 and Post.status%10 not like 0
order by dateFull desc limit '.$data['page'].', 20;';

        $st = $pdo->prepare($query);
        $st->execute([$data['pathVar']]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $result = $st->fetchAll();
        $st = null;
        $pdo = null;

        $postIDArr = Array();
        foreach($result as $i=>$value){
            $result[$i]['diaryID'] = (int)$result[$i]['diaryID'];
            $result[$i]['postID'] = (int)$result[$i]['postID'];
            array_push($postIDArr,$result[$i]['postID']);
            $result[$i]['title'] = (string)$result[$i]['title'];
        }
        if(empty($postIDArr)) $postIDList = 0;
        else $postIDList = implode(',', $postIDArr);

        $pdo = pdoSqlConnect();
        $query =
'select 
       Heart.postID as postID, 
       if(Heart.userID = ?,1,0) as isMyLike,
       count(*) as num 
from Heart where status not like 0 and postID in ('.$postIDList.') group by Heart.postID;';
        $st = $pdo->prepare($query);
        $st->execute([$data['id']]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $heart = $st->fetchAll();
        $st = null;
        $pdo = null;


        $pdo = pdoSqlConnect();
        $query =
'select 
       Comment.postID as postID, 
       count(*) as num 
from Comment where status not like 0 and postID in ('.$postIDList.') group by Comment.postID;';
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $comment = $st->fetchAll();
        $st = null;
        $pdo = null;


        if(empty($result)){
            return Array();
        }
        else{
            if(isset($data['addMethod'])) return $result;
            foreach($result as $i=>$value){
                $result[$i]['mood'] = codeToMood($result[$i]['mood']);
                $result[$i]['icon'] = codeToIcon($result[$i]['icon']);
                $result[$i]['date'] = convertDate((int)$result[$i]['date']);
                $result[$i]['isMyLike'] = FALSE;
                $result[$i]['likeNum'] = 0;
                $result[$i]['commentNum'] = 0;
                unset($result[$i]['dateFull']);

                foreach($heart as $hValue){
                    if($result[$i]['postID'] == $hValue['postID']){
                        if((int)$hValue['isMyLike']==1) $result[$i]['isMyLike'] = TRUE;
                        $result[$i]['likeNum'] = (int)$hValue['num'];
                    }
                }

                foreach($comment as $cValue){
                    if($result[$i]['postID'] == $cValue['postID']){
                        $result[$i]['commentNum'] = (int)$cValue['num'];
                    }
                }
            }
            return $result;
        }
    }
}

function getPostListDate($data){
    if(!isDiaryUser($data['id'], $data['pathVar'])) return 102;
    $pdo = pdoSqlConnect();
    $query =
        "select Diary.ID as diaryID, User.name as name, Post.ID as postID, Post.title as title,
               case
           when TIMESTAMPDIFF(SECOND, Post.updateAt, now()) < 60
               then concat(floor(TIMESTAMPDIFF(SECOND, Post.updateAt, now())),'초 전')
           when TIMESTAMPDIFF(SECOND, Post.updateAt, now()) < 60*60
               then concat(floor(TIMESTAMPDIFF(SECOND, Post.updateAt, now())/60),'분 전')
           when TIMESTAMPDIFF(SECOND, Post.updateAt, now()) < 60*60*24
               then concat(floor(TIMESTAMPDIFF(SECOND, Post.updateAt, now())/(60*60)),'시간 전')
           when TIMESTAMPDIFF(SECOND, Post.updateAt, now()) < 60*60*24*30
               then concat(floor(TIMESTAMPDIFF(SECOND, Post.updateAt, now())/(60*60*24)),'일 전')
           when TIMESTAMPDIFF(SECOND, Post.updateAt, now()) < 60*60*24*30*365
               then concat(floor(TIMESTAMPDIFF(SECOND, Post.updateAt, now())/(60*60*24*30)),'달 전')
           else concat(floor(TIMESTAMPDIFF(SECOND, Post.updateAt, now())/(60*60*60*24*30)),'년 전')
        end as date,
        Post.status as mood, Post.status as icon, '' AS isMyLike,
        (select count(*) as num from Heart where status not like 0 and Heart.postID = Post.ID) as likeNum,
        (select count(*) as num from Comment where status not like 0 and Comment.postID = Post.ID) as commentNum from Post
        left join Diary on Diary.ID = Post.diaryID
        left join User on User.ID = Post.userID
        where Post.diaryID = ? and date(Post.createAt) = ? and Post.status not like 0 and Post.status%10 not like 0 order by Post.createAt desc limit ".$data['num'].";";
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar'], $data['date']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    foreach($result as $i=>$value){
        $isMyLike = isValidLike($data['id'], $result[$i]['postID']);
        if($isMyLike == 0) $result[$i]['isMyLike'] = FALSE;
        else if($isMyLike == 1) $result[$i]['isMyLike'] = TRUE;
        $result[$i]['diaryID'] = (int)$result[$i]['diaryID'];
        $result[$i]['postID'] = (int)$result[$i]['postID'];
        $result[$i]['likeNum'] = (int)$result[$i]['likeNum'];
        $result[$i]['commentNum'] = (int)$result[$i]['commentNum'];
        $result[$i]['title'] = (string)$result[$i]['title'];
    }

    $none = Array();
    if(empty($result)) return $none;
    else{
        foreach($result as $i=>$value){
            $result[$i]['mood'] = codeToMood($result[$i]['mood']);
            $result[$i]['icon'] = codeToIcon($result[$i]['icon']);
        }
        return $result;
    }
}

function getPostNumByDate($data){
    if(!isDiaryUser($data['id'], $data['pathVar'])){
        $res['isSuccess'] = false;
        $res['code'] = 102;
        $res['message'] = '다이어리에 등록된 유저가 아닙니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    $query = 'select date(createAt) as date, count(*) as num from Post where Post.diaryID = ? and Post.status not like 0 and Post.status%10 not like 0 
            group by date(createAt) order by date(createAt) desc limit '.$data['page'].',4;';
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    foreach($result as $i=>$value) $result[$i]['num'] = (int)$result[$i]['num'];

    $none = Array();
    if(empty($result)) {
        $res['result'] = $none;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '등록된 게시물이 없습니다.';
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

function getPostDetailVer2($data){
    $res = (object)Array();
    if(!isValidPostDiary($data['id'], $data['pathVar'])){
        $res->isSuccess = FALSE;
        $res->code = 102;
        $res->message = '게시물을 볼 수 있는 권한이 없습니다.';
        echo json_encode($res);
        return;
    }
    $pdo = pdoSqlConnect();
    $query =
        'select 
       EXISTS(select * from Post where userID = ? and ID = ?) as isMyPost,
       Diary.ID as diaryID, 
       User.name as name, 
       Post.ID as postID,
       TIMESTAMPDIFF(SECOND, Post.updateAt, now()) as date,
       Post.createAt as dateFull,
       Post.title as title, 
       PostText.text as text,
       truncate(Post.status, -1)/100 as background, 
       Post.status as mood, 
       Post.status as icon, 
       PostText.aligned as aligned, 
       PostText.aligned as font, 
       \'\' as image,
       0 as isMyLike,
       0 as likeNum,
       0 as commentNum
from Post
left join Diary on Diary.ID = Post.diaryID
left join User on User.ID = Post.userID
left join PostText on PostText.postID = Post.ID
where Post.ID = ?;';
    $st = $pdo->prepare($query);
    $st->execute([$data['id'], $data['pathVar'],$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    $pdo = pdoSqlConnect();
    $query =
        'select 
       Heart.postID as postID, 
       if(Heart.userID = ?,1,0) as isMyLike,
       count(*) as num 
from Heart where status not like 0 and postID = ? group by Heart.postID;';
    $st = $pdo->prepare($query);
    $st->execute([$data['id'], $data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $heart = $st->fetchAll();
    $st = null;
    $pdo = null;


    $pdo = pdoSqlConnect();
    $query =
        'select 
       Comment.postID as postID, 
       count(*) as num 
from Comment where status not like 0 and postID = ? group by Comment.postID;';
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $comment = $st->fetchAll();
    $st = null;
    $pdo = null;

    if($result[0]['isMyPost'] == 0) $result[0]['isMyPost'] = FALSE;
    else if($result[0]['isMyPost'] == 1) $result[0]['isMyPost'] = TRUE;
    else{
        $res->isSuccess = FALSE;
        $res->code = 103;
        $res->message = '잘못된 쿼리';
        echo json_encode($res);
        return;
    }

    if(!empty($heart)){
        if($heart[0]['isMyLike'] == 0) $result[0]['isMyLike'] = FALSE;
        else if($heart[0]['isMyLike'] == 1) $result[0]['isMyLike'] = TRUE;
        $result[0]['likeNum'] = (int)$heart[0]['num'];
    }
    else{
        $result[0]['isMyLike'] = FALSE;
        $result[0]['likeNum'] = 0;
    }

    if(!empty($comment)) $result[0]['commentNum'] = (int)$comment[0]['num'];
    else $result[0]['commentNum'] = 0;

    $result[0]['background'] = codeToBackground($result[0]['background']);
    $result[0]['mood'] = codeToMood($result[0]['mood']);
    $result[0]['icon'] = codeToIcon($result[0]['icon']);

    $result[0]['diaryID'] = (int)$result[0]['diaryID'];
    $result[0]['postID'] = (int)$result[0]['postID'];
    $result[0]['background'] = (int)$result[0]['background'];

    if($result[0]['aligned'] > 3){
        $aligned = (int)($result[0]['aligned']/100);
        $result[0]['font'] = (int)($result[0]['aligned'] - $aligned*100);
        $result[0]['aligned'] = $aligned;
    }
    else{
        $result[0]['aligned'] = (int)$result[0]['aligned'];
        $result[0]['font'] = 1;
    }

    $pdo = pdoSqlConnect();
    $query = 'select URL from PostImage where postID = ? and status not like 0;';
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $image = $st->fetchAll();
    $st = null;
    $pdo = null;

    $result[0]['image'] = $image;
    $result[0]['date'] = convertDate((int)$result[0]['date']);

    $res->result = $result[0];
    $res->isSuccess = TRUE;
    $res->code = 100;
    $res->message = '성공적으로 조회되었습니다.';
    echo json_encode($res);
}