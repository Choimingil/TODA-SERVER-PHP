<?php

function getPostDetail($data){
    $res = (object)Array();
    $listKey = getListKey('post_detail',$data['pathVar'],0);
    $listValue = getRedis($listKey);
    $dateKey= getDateKey('post_detail',$data['pathVar']);

    if($listValue!=0){
        $result = json_decode($listValue,true);
        $now = new DateTime();
        $before = getRedis($dateKey);
        $dateDiff = $now->getTimestamp() - $before;

        if(!is_array($result)) setRedis($listKey,0);
        else{
            $standardDate = getPostDate($data['pathVar']);

            // 날짜가 이미 변환되었을 경우 다시 쿼리 실행
            if(substr($result['date'],-1)=='전'){
                setRedis($listKey,0);
            }
            // 날짜가 음수일 경우 다시 쿼리 실행
            else if( (int)$result['date']<0 ){
                $result['date'] = convertDate(0);
                setRedis($listKey,0);
            }
//            // 날짜가 실제 날짜와 다를 경우
//            else if((int)$result['date'] > $standardDate+10 or (int)$result['date'] < $standardDate-10){
//                setRedis($listKey,0);
//            }
            // 내 게시글이 맞는데 잘못 들어가 있을 경우
            else if(isMyPost($data['id'],$data['pathVar']) != $result['isMyPost']){
                setRedis($listKey,0);
            }
            else $result['date'] = convertDate((int)($result['date']+$dateDiff));
        }

        $res->result = $result;
        $res->isSuccess = TRUE;
        $res->code = 100;
        $res->message = '성공적으로 조회되었습니다.';
        echo json_encode($res);
    }
    else{
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
       \'\' as image,
       EXISTS(select * from Heart where userID = ? and postID = Post.ID and status not like 0) as isMyLike, 
       (select count(*) as num from Heart where status not like 0 and Heart.postID = Post.ID) as likeNum,
       (select count(*) as num from Comment where status not like 0 and Comment.postID = Post.ID) as commentNum 
from Post
left join Diary on Diary.ID = Post.diaryID
left join User on User.ID = Post.userID
left join PostText on PostText.postID = Post.ID
where Post.ID = ?;';
        $st = $pdo->prepare($query);
        $st->execute([$data['id'], $data['pathVar'], $data['id'], $data['pathVar']]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $result = $st->fetchAll();
        $st = null;

        if($result[0]['isMyPost'] == 0) $result[0]['isMyPost'] = FALSE;
        else if($result[0]['isMyPost'] == 1) $result[0]['isMyPost'] = TRUE;
        else{
            $res->isSuccess = FALSE;
            $res->code = 103;
            $res->message = '잘못된 쿼리';
            echo json_encode($res);
            return;
        }

        if($result[0]['isMyLike'] == 0) $result[0]['isMyLike'] = FALSE;
        else if($result[0]['isMyLike'] == 1) $result[0]['isMyLike'] = TRUE;

        $result[0]['background'] = codeToBackground($result[0]['background']);
        $result[0]['mood'] = codeToMood($result[0]['mood']);
        $result[0]['icon'] = codeToIcon($result[0]['icon']);

        $result[0]['diaryID'] = (int)$result[0]['diaryID'];
        $result[0]['postID'] = (int)$result[0]['postID'];
        $result[0]['background'] = (int)$result[0]['background'];
        if($result[0]['aligned'] > 3) $result[0]['aligned'] = (int)($result[0]['aligned']/100);
        else $result[0]['aligned'] = (int)$result[0]['aligned'];
        $result[0]['likeNum'] = (int)$result[0]['likeNum'];
        $result[0]['commentNum'] = (int)$result[0]['commentNum'];

        $query = 'select URL from PostImage where postID = ? and status not like 0;';
        $st = $pdo->prepare($query);
        $st->execute([$data['pathVar']]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $image = $st->fetchAll();
        $st = null;
        $pdo = null;

        $result[0]['image'] = $image;
        
        $now = new DateTime();
        setRedis($dateKey,$now->getTimestamp());
        setRedis($listKey,json_encode($result[0]));

        $result[0]['date'] = convertDate((int)$result[0]['date']);

        $res->result = $result[0];
        $res->isSuccess = TRUE;
        $res->code = 100;
        $res->message = '성공적으로 조회되었습니다.';
        echo json_encode($res);
    }
}

function updatePost($data){
    if(!isPostUser($data['id'],$data['post'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 103;
        $res['message'] = '자신이 작성한 게시물이 아닙니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    if(isset($data['date'])){
        $query = 'UPDATE Post SET title = ?, status = ?, createAt = ? WHERE ID = ?;
              UPDATE PostText SET text = ?, aligned = ? WHERE postID = ?;
              UPDATE PostImage SET status = 0 WHERE postID = ? and status not like 0;';
        $st = $pdo->prepare($query);
        $st->execute([$data['title'], $data['status'], $data['date'], $data['post'],$data['text'], $data['aligned'], $data['post'],$data['post']]);
    }
    else{
        $query = 'UPDATE Post SET title = ?, status = ? WHERE ID = ?;
              UPDATE PostText SET text = ?, aligned = ? WHERE postID = ?;
              UPDATE PostImage SET status = 0 WHERE postID = ? and status not like 0;';
        $st = $pdo->prepare($query);
        $st->execute([$data['title'], $data['status'], $data['post'],$data['text'], $data['aligned'], $data['post'],$data['post']]);
    }
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
//                        $executeQuery.array_push($query);
//                        $executeKey.array_push($remainURL[$j]['url']);
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

    // setting redis
    $diaryID = postToDiary($data['post']);
    $lastPage = getFinalPagePost($diaryID);
    $sendData = Array(
        'diary' => (int)$diaryID,
        'postID' => (int)$data['post'],
        'user' => $data['id']
    );
    $newData = getPostListSpecificValue($sendData);
    updateMethod('post',$diaryID,$lastPage,'postID',$data['post'],$newData);
    $newDetailData = getPostDetailSpecificValue($sendData);
    updateHeartMethod($data['post'],$newDetailData);

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '게시물 수정이 완료되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function addPost($data){
    // Post 추가
    $pdo = pdoSqlConnect();
    if(isset($data['date'])){
        $query = 'INSERT INTO Post (userID, diaryID, title, status, createAt) VALUES (?, ?, ?, ?,?);';
        $st = $pdo->prepare($query);
        $st->execute([$data['id'], $data['diary'], $data['title'], $data['status'], $data['date']]);
        $st = null;
    }
    else{
        $query = 'INSERT INTO Post (userID, diaryID, title, status) VALUES (?, ?, ?, ?);';
        $st = $pdo->prepare($query);
        $st->execute([$data['id'], $data['diary'], $data['title'], $data['status']]);
        $st = null;
    }
    $postID = $pdo->lastInsertId();
    $pdo = null;

    // 본문 저장
    $pdo = pdoSqlConnect();
    $query = 'INSERT INTO PostText (postID, text, aligned) VALUES (?,?,?);';
    $st = $pdo->prepare($query);
    $st->execute([$postID,$data['text'],$data['aligned']]);
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

    // setting redis
    $newData = getPostListSpecificValue($sendData);
    addMethod('post',$data['diary'],$newData);

    //알림 받을 사람 데이터 셀렉
    $pdo = pdoSqlConnect();
    $diaryData = implode(',',createCodeArray(1,12,1,4));
    $query =
"select
       User.ID as userID,
       ifnull(Notification.token,'none') as token,
       UserDiary.diaryName as diaryName,
       Notification.status as status,
       if(l.receiveID=User.ID,1,0) as exist
from User
    left join UserDiary on UserDiary.userID=User.ID
    left join Notification on Notification.userID=User.ID
        and Notification.status not like 0
        and Notification.isAllowed like 'Y'
    left join
    (select receiveID from Log where type = 3 and typeID = ? and sendID = ? group by receiveID)
        l on l.receiveID = User.ID
where UserDiary.diaryID=?
  and User.status not like 99999
  and User.ID not like ?
  and UserDiary.status in (".$diaryData.");";
    $st = $pdo->prepare($query);
    $st->execute([$sendData['postID'],$sendData['user'],$sendData['diary'],$sendData['user']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $receive = $st->fetchAll();
    $st = null;
    $pdo = null;

    // 알림 전송 및 로그 추가
    sendPostAlarm('post',$sendData,$receive);

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '게시물 작성이 완료되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function getLogVer1($data){
    $res = (object)Array();
    $listKey = getListKey('user',$data['id'],$data['page']/20+1);
    $listValue = getRedis($listKey);
    $dateKey= getDateKey('user',$data['id']);

    // listValue 값은 있을 때 : 시간값만 변경해서 저장하기

    if($listValue!=0){
        $result = json_decode($listValue,true);
        $now = new DateTime();
        $before = getRedis($dateKey);
        $dateDiff = $now->getTimestamp() - $before;

        foreach($result as $i=>$value){
            // 날짜가 이미 변환되었을 경우 다시 쿼리 실행
            if(substr($result[$i]['date'],-1)=='전'){
                setRedis($listKey,0);
            }
            // 날짜가 음수일 경우 다시 쿼리 실행
            else if( (int)$result[$i]['date']<0 ){
                $result[$i]['date'] = convertDate(0);
                setRedis($listKey,0);
            }
            else $result[$i]['date'] = convertDate((int)($result[$i]['date'] + $dateDiff));
        }

        $res->result = $result;
        $res->isSuccess = TRUE;
        $res->code = 100;
        $res->message = '성공적으로 조회되었습니다.';
        echo json_encode($res);
    }
    // listValue 값도 없을 떄 : 쿼리 실행하기
    else{
        $pdo = pdoSqlConnect();
        $query =
//임시방편으로 distinct 박아놈
            "select distinct
                Log.sendID as userID,
                Log.type as type,
                Log.typeID as ID,
                Send.name as name,
                '' as selfie,
                '' as image,
                TIMESTAMPDIFF(SECOND, Log.updateAt, now()) as date,
                true as isReplied,
                Log.status as status
from Log
left join User as Send on Send.ID = Log.sendID and Send.status not like 99999
where Log.receiveID=?
order by Log.updateAt desc limit ".$data['page'].", 20;";
        $st = $pdo->prepare($query);
        $st->execute([$data['id']]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $result = $st->fetchAll();
        $st = null;
        $pdo = null;

        $id = array(); $key = array();
        $userIDArray = Array();
        foreach($result as $i => $value){
            $result[$i]['type'] = (int)$result[$i]['type'];
            $result[$i]['ID'] = (int)$result[$i]['ID'];
            $result[$i]['isReplied'] = (bool)$result[$i]['isReplied'];
            array_push($userIDArray,(int)$result[$i]['userID']);
            if($result[$i]['type']==1 && $result[$i]['status']=='100') $result[$i]['isReplied']=false;
            else if($result[$i]['type']==2){
                unset($result[$i]['status']);
                continue;
            }
            else{
                array_push($key,$i);
                array_push($id,$result[$i]['ID']);
            }
            unset($result[$i]['status']);
        }

        // 프사 넣기
        $userIDQuery = implode(',', $userIDArray);
        $pdo = pdoSqlConnect();
        $query = "select userID as userID, URL from UserImage where userID in (".$userIDQuery.") and status not like 0;";
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $selfie = $st->fetchAll();
        $st = null;
        $pdo = null;

        foreach ($result as $i=>$value){
            foreach ($selfie as $j=>$userID){
                if(isset($result[$i]['userID'])){
                    if($result[$i]['userID'] == $selfie[$j]['userID']){
                        $result[$i]['selfie'] = $selfie[$j]['URL'];
                        unset($result[$i]['userID']);
                    }
                }
            }
        }


        // 이미지 넣기
        $idArray = implode(',', $id);
        if(!empty($id)){
            $pdo = pdoSqlConnect();
            $query = "select postID as postID, URL from PostImage where postID in (".$idArray.") and status not like 0;";
            $st = $pdo->prepare($query);
            $st->execute();
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $image = $st->fetchAll();
            $st = null;
            $pdo = null;

            foreach($image as $i=>$value){
                foreach($id as $j=>$imagePostID){
                    if($id[$j]==$image[$i]['postID']){
                        $idx = $key[$j];
                        if($result[$idx]['image']=='') $result[$idx]['image'] = $image[$i]['URL'];
                    }
                }
            }
        }

        $none = Array();
        $now = new DateTime();
        if(empty($result)){
            setRedis($dateKey,$now->getTimestamp());
            setRedis($listKey,json_encode($none));

            $res->result = $none;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = '알림이 존재하지 않습니다.';
            echo json_encode($res, JSON_NUMERIC_CHECK);
        }
        else{
            setRedis($dateKey,$now->getTimestamp());
            setRedis($listKey,json_encode($result));

            foreach($result as $i => $value)
                $result[$i]['date'] = convertDate((int)$result[$i]['date']);

            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = '성공적으로 조회되었습니다.';
            echo json_encode($res);
        }
    }
}

// 알림 부분 쿼리 백업
//            $query =
//'select
//       Diary.ID as diaryID,
//       User.name as name,
//       Post.ID as postID,
//       Post.title as title,
//       TIMESTAMPDIFF(SECOND, Post.updateAt, now()) as date,
//       Post.status as mood,
//       Post.status as icon,
//       EXISTS(select * from Heart where userID = ? and postID = Post.ID and status not like 0) AS isMyLike,
//       (select count(*) as num from Heart where status not like 0 and Heart.postID = Post.ID) as likeNum,
//       (select count(*) as num from Comment where status not like 0 and Comment.postID = Post.ID) as commentNum
//from Post
//left join Diary on Diary.ID = Post.diaryID
//left join User on User.ID = Post.userID
//where Diary.ID = ?
//    and Post.status not like 0
//    and Post.status%10 not like 0
//order by Post.createAt desc limit '.$data['page'].', 20;';


function getPostListSpecificValue($data){
    $pdo = pdoSqlConnect();
    $query =
        'select
       Diary.ID as diaryID,
       User.name as name,
       Post.ID as postID,
       Post.title as title,
       TIMESTAMPDIFF(SECOND, Post.updateAt, now()) as date,
       Post.status as mood,
       Post.status as icon,
       if(h.userID = ?,1,0) as isMyLike,
       ifnull(h.num,0) as likeNum,
       ifnull(c.num,0) as commentNum,
       Post.createAt as dateFull
from Post
left join Diary on Diary.ID = Post.diaryID
left join User on User.ID = Post.userID
left join (select Heart.postID as postID, Heart.userID as userID, count(*) as num from Heart where status not like 0 group by Heart.postID)
h on h.postID = Post.ID
left join (select Comment.postID as postID, count(*) as num from Comment where status not like 0 group by Comment.postID)
c on c.postID = Post.ID
where Diary.ID = ?
    and Post.status not like 0
    and Post.status%10 not like 0
    and Post.ID in ('.$data['postID'].');';

    $st = $pdo->prepare($query);
    $st->execute([$data['user'],$data['diary']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    foreach($result as $i=>$value){
        if($result[$i]['isMyLike'] == 0) $result[$i]['isMyLike'] = FALSE;
        else if($result[$i]['isMyLike'] == 1) $result[$i]['isMyLike'] = TRUE;
//        $result[$i]['date'] = convertDate((int)$result[$i]['date']);
        $result[$i]['diaryID'] = (int)$result[$i]['diaryID'];
        $result[$i]['postID'] = (int)$result[$i]['postID'];
        $result[$i]['likeNum'] = (int)$result[$i]['likeNum'];
        $result[$i]['commentNum'] = (int)$result[$i]['commentNum'];
        $result[$i]['title'] = (string)$result[$i]['title'];
    }

    return $result[0];
}


function getPostDetailSpecificValue($data){
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
       EXISTS(select * from Heart where userID = ? and postID = Post.ID and status not like 0) as isMyLike, 
       (select count(*) as num from Heart where status not like 0 and Heart.postID = Post.ID) as likeNum,
       (select count(*) as num from Comment where status not like 0 and Comment.postID = Post.ID) as commentNum 
from Post
left join Diary on Diary.ID = Post.diaryID
left join User on User.ID = Post.userID
left join PostText on PostText.postID = Post.ID
where Post.ID = ?;';
    $st = $pdo->prepare($query);
    $st->execute([$data['user'], $data['postID'], $data['user'], $data['postID']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;

    if($result[0]['isMyPost'] == 0) $result[0]['isMyPost'] = FALSE;
    else if($result[0]['isMyPost'] == 1) $result[0]['isMyPost'] = TRUE;

    if($result[0]['isMyLike'] == 0) $result[0]['isMyLike'] = FALSE;
    else if($result[0]['isMyLike'] == 1) $result[0]['isMyLike'] = TRUE;

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
    $result[0]['likeNum'] = (int)$result[0]['likeNum'];
    $result[0]['commentNum'] = (int)$result[0]['commentNum'];

    $query = 'select URL from PostImage where postID = ? and status not like 0;';
    $st = $pdo->prepare($query);
    $st->execute([$data['postID']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $image = $st->fetchAll();
    $st = null;
    $pdo = null;

    $result[0]['image'] = $image;
    return $result[0];
}

function getPostListRaw($data){
    if(!isDiaryUser($data['id'], $data['pathVar'])){
        return 102;
    }
    else{
        $pdo = pdoSqlConnect();
        $query =
            'select
       Diary.ID as diaryID,
       User.name as name,
       Post.ID as postID,
       Post.title as title,
       TIMESTAMPDIFF(SECOND, Post.updateAt, now()) as date,
       Post.status as mood,
       Post.status as icon,
       if(h.userID = ?,1,0) as isMyLike,
       ifnull(h.num,0) as likeNum,
       ifnull(c.num,0) as commentNum,
       Post.createAt as dateFull
from Post
left join Diary on Diary.ID = Post.diaryID
left join User on User.ID = Post.userID
left join (select Heart.postID as postID, Heart.userID as userID, count(*) as num from Heart where status not like 0 group by Heart.postID)
h on h.postID = Post.ID
left join (select Comment.postID as postID, count(*) as num from Comment where status not like 0 group by Comment.postID)
c on c.postID = Post.ID
where Diary.ID = ?
    and Post.status not like 0
    and Post.status%10 not like 0
order by Post.createAt desc limit '.$data['page'].', 20;';

        $st = $pdo->prepare($query);
        $st->execute([$data['id'],$data['pathVar']]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $result = $st->fetchAll();
        $st = null;
        $pdo = null;

        foreach($result as $i=>$value){
            if($result[$i]['isMyLike'] == 0) $result[$i]['isMyLike'] = FALSE;
            else if($result[$i]['isMyLike'] == 1) $result[$i]['isMyLike'] = TRUE;
            $result[$i]['diaryID'] = (int)$result[$i]['diaryID'];
            $result[$i]['postID'] = (int)$result[$i]['postID'];
            $result[$i]['likeNum'] = (int)$result[$i]['likeNum'];
            $result[$i]['commentNum'] = (int)$result[$i]['commentNum'];
            $result[$i]['title'] = (string)$result[$i]['title'];
            $result[$i]['mood'] = (int)$result[$i]['mood'];
            $result[$i]['icon'] = (int)$result[$i]['icon'];
            $result[$i]['date'] = (int)$result[$i]['date'];
        }

        $none = Array();
        if(empty($result)){
            return $none;
        }
        else{
            return $result;
        }
    }
}