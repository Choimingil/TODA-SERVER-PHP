<?php

function postLike($data){
    if(!isValidPostDiary($data['id'], $data['pathVar'])){
        $res['isSuccess'] = false;
        $res['code'] = 102;
        $res['message'] = '게시물을 볼 수 있는 권한이 없습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    if(isExistLike($data['id'], $data['pathVar'])){
        if($data['status'] == getMood($data['id'], $data['pathVar'])){
            $pdo = pdoSqlConnect();
            $query = 'UPDATE Heart SET status = 0 WHERE userID = ? and postID = ?';
            $st = $pdo->prepare($query);
            $st->execute([$data['id'], $data['pathVar']]);
            $st = null;
            $pdo = null;

            $res['isSuccess'] = TRUE;
            $res['code'] = 200;
            $res['message'] = '좋아요가 취소되었습니다.';
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;
        }
        else{
            $pdo = pdoSqlConnect();
            $query = 'UPDATE Heart SET status = ? WHERE userID = ? and postID = ?';
            $st = $pdo->prepare($query);
            $st->execute([$data['status'], $data['id'], $data['pathVar']]);
            $st = null;
            $pdo = null;

            $res['isSuccess'] = TRUE;
            $res['code'] = 100;
            $res['message'] = '좋아요가 재등록되었습니다.';
            echo json_encode($res, JSON_NUMERIC_CHECK);
        }
    }
    else{
        //좋아요 누를 게시글 정보 확인
        $pdo = pdoSqlConnect();
        $query =
'select 
       User.ID as userID, 
       User.name as name, 
       ifnull(Notification.token,\'none\') as token, 
       Diary.ID as diaryID, 
       Notification.status as status 
from User 
    left join Notification on Notification.userID=User.ID 
        and Notification.status not like 0
        and Notification.isAllowed like \'Y\'
    left join Post on Post.userID = User.ID  
    left join Diary on Diary.ID = Post.diaryID 
where Post.ID = ?;';
        $st = $pdo->prepare($query);
        $st->execute([$data['pathVar']]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $receive = $st->fetchAll();
        $st = null;

        if(empty($receive)){
            $query = 'INSERT INTO Heart (userID, postID, status) VALUES (?, ?, ?);';
            $st = $pdo->prepare($query);
            $st->execute([$data['id'], $data['pathVar'], $data['status']]);
            $st = null;
            $pdo = null;

            $res['isSuccess'] = TRUE;
            $res['code'] = 100;
            $res['message'] = '좋아요가 등록되었습니다.';
            echo json_encode($res, JSON_NUMERIC_CHECK);
        }
        else if($data['id']!=$receive[0]['userID']){
            //좋아요 추가 및 로그 추가
            $logData = array(
                'receiveID' => $receive[0]['userID'],
                'type' => 4,
                'typeID' => $data['pathVar'],
                'sendID' => $data['id']
            );
            $query = 'INSERT INTO Heart (userID, postID, status) VALUES (?, ?, ?);
                  INSERT INTO Log (receiveID, type, typeID, sendID) VALUES (?, ?, ?, ?);';
            $st = $pdo->prepare($query);
            $st->execute([$data['id'], $data['pathVar'], $data['status'],$logData['receiveID'], $logData['type'], $logData['typeID'], $logData['sendID']]);
            $st = null;
            $pdo = null;

            $sendData = Array(
                'sendname' => IDToName($data['id']),
                'receive' => $receive,
                'pathVar' => (int)$data['pathVar']
            );
            sendToAlarmServer('/push/like',$sendData);

            $res['isSuccess'] = TRUE;
            $res['code'] = 100;
            $res['message'] = '좋아요가 등록되었습니다.';
            echo json_encode($res, JSON_NUMERIC_CHECK);
        }
        else{
            $query = 'INSERT INTO Heart (userID, postID, status) VALUES (?, ?, ?);';
            $st = $pdo->prepare($query);
            $st->execute([$data['id'], $data['pathVar'], $data['status']]);
            $st = null;
            $pdo = null;

            $res['isSuccess'] = TRUE;
            $res['code'] = 100;
            $res['message'] = '좋아요가 등록되었습니다.';
            echo json_encode($res, JSON_NUMERIC_CHECK);
        }
    }
}

function postComment($data){
    if(!isValidPostDiary($data['id'], $data['post'])){
        $res['isSuccess'] = false;
        $res['code'] = 102;
        $res['message'] = '게시물을 볼 수 있는 권한이 없습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $pdo = pdoSqlConnect();
    $query =
"select 
       User.ID as userID, 
       ifnull(Notification.token,'none') as token, 
       Notification.status as status 
from User 
    left join Notification on Notification.userID=User.ID
        and Notification.status not like 0 
        and Notification.isAllowed like 'Y'
    left join Post on Post.userID = User.ID 
where Post.ID = ? and User.ID not like ?;";
    $st = $pdo->prepare($query);
    $st->execute([$data['post'],$data['id']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    $pdo = pdoSqlConnect();
    $query = 'INSERT INTO Comment (userID, postID, text) VALUES (?, ?, ?);';
    $st = $pdo->prepare($query);
    $st->execute([$data['id'], $data['post'], $data['reply']]);
    $st = null;
    $commentID = $pdo->lastInsertId();
    $pdo = null;

    if(empty($result)){
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '댓글이 성공적으로 작성되었습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
    else{
        $logData = array(
            'receiveID' => $result[0]['userID'],
            'type' => 5,
            'typeID' => $data['post'],
            'sendID' => $data['id']
        );
        $pdo = pdoSqlConnect();
        $query = 'INSERT INTO Log (receiveID, type, typeID, sendID) VALUES (?, ?, ?, ?);';
        $st = $pdo->prepare($query);
        $st->execute([$logData['receiveID'], $logData['type'], $logData['typeID'], $logData['sendID']]);
        $st = null;
        $pdo = null;

        $sendData = Array(
            'reply' => $data['reply'],
            'sendname' => IDtoName($data['id']),
            'result' => $result,
            'post' => (int)$data['post']
        );
        sendToAlarmServer('/push/comment',$sendData);

        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '댓글이 성공적으로 작성되었습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
}

function postReComment($data){
    //validation
    if(!isValidPostDiary($data['id'], $data['post'])){
        $res['isSuccess'] = false;
        $res['code'] = 102;
        $res['message'] = '게시물을 볼 수 있는 권한이 없습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    if(!isValidCommentPostDiary($data['id'], $data['post'], $data['queryString'])){
        $res['isSuccess'] = false;
        $res['code'] = 102;
        $res['message'] = '대댓글을 달 권한이 없습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    // 대댓글 작성
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Comment (userID, postID, text, parent) VALUES (?, ?, ?, ?);";
    $st = $pdo->prepare($query);
    $st->execute([$data['id'], $data['post'], $data['reply'], $data['queryString']]);
    $st = null;
    $pdo = null;

//    //댓글 작성자 + 대댓글에 참여한 사람들 알림 받을 사람들 명단 뽑기
//    $pdo = pdoSqlConnect();
//    $query =
//"select distinct
//                User.ID as userID,
//                Notification.token as token,
//                Notification.status as status
//from User
//    left join Notification on Notification.userID=User.ID
//        and Notification.status not like 0
//        and Notification.isAllowed like 'Y'
//    left join Comment on Comment.userID = User.ID
//    left join Log on Log.receiveID = User.ID
//where Log.type = 6 and Log.typeID = ? and Log.sendID = ?
//    and length(ifnull(Notification.token,'none')) > 30
//    and ((Comment.ID = ? and Comment.userID not like ?)
//   or
//    (Comment.parent = ?
//        and Comment.status not like 0
//        and Comment.userID not like ?));";
//    $st = $pdo->prepare($query);
//    $st->execute([
//        $data['post'],
//        $data['id'],
//        $data['queryString'],
//        $data['id'],
//        $data['queryString'],
//        $data['id']]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $receive = $st->fetchAll();
//    $st = null;
//    $pdo = null;

    //댓글 작성자 + 대댓글에 참여한 사람들 알림 받을 사람들 명단 뽑기
    $pdo = pdoSqlConnect();
    $query =
        "select distinct
                User.ID as userID,
                Notification.token as token,
                Notification.status as status
from User
    left join Notification on Notification.userID=User.ID
        and Notification.status not like 0
        and Notification.isAllowed like 'Y'
    left join Comment on Comment.userID = User.ID
where length(ifnull(Notification.token,'none')) > 30
    and ((Comment.ID = ? and Comment.userID not like ?)
   or
    (Comment.parent = ?
        and Comment.status not like 0
        and Comment.userID not like ?));";
    $st = $pdo->prepare($query);
    $st->execute([
        $data['queryString'],
        $data['id'],
        $data['queryString'],
        $data['id']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $receive = $st->fetchAll();
    $st = null;
    $pdo = null;

    // 알림 전송
    $sendAlarmData = Array(
        'reply' => $data['reply'],
        'sendname' => IDtoName($data['id']),
        'result' => $receive,
        'post' => (int)$data['post']
    );
    sendToAlarmServer('/push/recomment',$sendAlarmData);

    // 로그 추가
    $logArray = array();
    $logExecute = Array();
    $typeID = 6;

    foreach($receive as $value) {
        $logData = array(
            'receiveID' => $value['userID'],
            'type' => $typeID,
            'typeID' => $data['post'],
            'sendID' => $data['id']
        );

        array_push($logArray, '(?,?,?,?)');

        array_push($logExecute, $logData['sendID']);
        array_push($logExecute, $logData['receiveID']);
        array_push($logExecute, $logData['type']);
        array_push($logExecute, $logData['typeID']);
    }

    if(!empty($receive)){
        if(!empty($logArray && !empty($logExecute))){
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
    $res['message'] = '대댓글이 성공적으로 작성되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function deleteComment($data){
    if(!isCommentUser($data['id'],$data['pathVar'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '자신이 작성한 댓글이 아닙니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $pdo = pdoSqlConnect();
    $query = 'UPDATE Comment SET status = 0 WHERE ID = ? or parent = ?';
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar'],$data['pathVar']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '댓글이 삭제되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function updateComment($data){
    if(!isCommentUser($data['id'],$data['comment'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 102;
        $res['message'] = '자신이 작성한 댓글이 아닙니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    $query = 'UPDATE Comment SET text = ? WHERE ID = ?';
    $st = $pdo->prepare($query);
    $st->execute([$data['reply'], $data['comment']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '댓글이 수정되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function getComment($data){
    if(!isValidPostDiary($data['id'], $data['pathVar'])) return 103;

    $pdo = pdoSqlConnect();
    $query =
        "select
       Post.ID as postID,
       Comment.userID as userID,
       User.name as userName,
       UserImage.URL as userSelfie,
       Comment.ID as commentID,
       Comment.text as comment,
       TIMESTAMPDIFF(SECOND, Comment.createAt, now()) as time,
       EXISTS(select * from Comment where userID = ? and ID = commentID and status not like 0) as isMyComment
from Comment
left join User on User.ID = Comment.userID
left join UserImage on User.ID = UserImage.userID and UserImage.status not like 0
left join Post on Post.ID = Comment.postID
where Post.ID = ? and Comment.parent = 0 and Comment.status not like 0
order by Comment.createAt limit ".$data['page'].", 20;";
    $st = $pdo->prepare($query);
    $st->execute([$data['id'],$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    $none = Array(
        'totalCommentNum' => 0,
        'Comment' => Array()
    );
    if(empty($result)){
        return $none;
    }

    $id = Array();
    foreach($result as $i=>$value){
        if($result[$i]['isMyComment'] == 0) $result[$i]['isMyComment'] = FALSE;
        else if($result[$i]['isMyComment'] == 1) $result[$i]['isMyComment'] = TRUE;
        $result[$i]['postID'] = (int)$result[$i]['postID'];
        $result[$i]['userID'] = (int)$result[$i]['userID'];
        $result[$i]['commentID'] = (int)$result[$i]['commentID'];
        array_push($id,$result[$i]['commentID']);
        $result[$i]['reComment'] = Array();
    }
    $idArray = implode(',', $id);

    $pdo = pdoSqlConnect();
    $query =
        "select 
       Comment.parent as parent, 
       Comment.userID as userID, 
       User.name as userName,
       UserImage.URL as userSelfie,
       Comment.ID as commentID, 
       Comment.text as comment,
       TIMESTAMPDIFF(SECOND, Comment.createAt, now()) as time,  
       EXISTS(select * from Comment where userID = ? and ID = commentID and status not like 0) as isMyComment 
from Comment
left join User on User.ID = Comment.userID
left join UserImage on User.ID = UserImage.userID and UserImage.status not like 0
where Comment.parent in (".$idArray.") 
    and Comment.postID = ? 
    and Comment.status not like 0 
order by Comment.createAt;";
    $st = $pdo->prepare($query);
    $st->execute([$data['id'],$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $re_res = $st->fetchAll();
    $st = null;
    $pdo = null;

    foreach($re_res as $i=>$value){
        if($re_res[$i]['isMyComment'] == 0) $re_res[$i]['isMyComment'] = FALSE;
        else if($re_res[$i]['isMyComment'] == 1) $re_res[$i]['isMyComment'] = TRUE;
        $re_res[$i]['parent'] = (int)$re_res[$i]['parent'];
        $re_res[$i]['userID'] = (int)$re_res[$i]['userID'];
        $re_res[$i]['commentID'] = (int)$re_res[$i]['commentID'];
        $parent = $re_res[$i]['parent'];
        $id_key = array_keys($id,$parent);
        $key = array_pop($id_key);
        array_push($result[$key]['reComment'], $re_res[$i]);
    }

    $pdo = pdoSqlConnect();
    $query = "select count(ID) as num from Comment where postID=? and status not like 0;";
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $resultCommentNum = $st->fetchAll();
    $resultFinal['totalCommentNum'] = (int)$resultCommentNum[0]['num'];
    $resultFinal['Comment'] = $result;
    $st = null;
    $pdo = null;

    if(empty($resultFinal)){
        return $none;
    }
    else{
        if(isset($data['addMethod'])) return $resultFinal;

        $resultForTime = $resultFinal['Comment'];
        foreach($resultForTime as $i=>$value){
            $resultForTime[$i]['time'] = convertDate((int)$resultForTime[$i]['time']);
            $reResForTime = $resultForTime[$i]['reComment'];
            foreach($reResForTime as $j=>$value2)
                $reResForTime[$j]['time'] = convertDate((int)$reResForTime[$j]['time']);
            $resultForTime[$i]['reComment'] = $reResForTime;
        }
        $resultFinal['Comment'] = $resultForTime;
        return $resultFinal;
    }
}