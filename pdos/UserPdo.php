<?php
require './vendor/autoload.php';

use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function deleteUser($data){
    $pdo = pdoSqlConnect();
    $query = 'UPDATE User SET status = 99999 WHERE ID = ?';
    $st = $pdo->prepare($query);
    $st->execute([$data['id']]);
    $st = null;
    $pdo = null;
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '회원탈퇴가 완료되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function updateName($data){
    $pdo = pdoSqlConnect();
    $query = 'UPDATE User SET name = ? WHERE ID = ?';
    $st = $pdo->prepare($query);
    $st->execute([$data['name'], $data['id']]);
    $st = null;
    $pdo = null;
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '이름이 성공적으로 변경되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function updatePassword($data, $secretKey){
    $pdo = pdoSqlConnect();
    if(!isValidEmail(IDToEmail($data['id']))){
        $res['isSuccess'] = FALSE;
        $res['code'] = 103;
        $res['message'] = 'TODA 계정이 아닙니다.(카카오 로그인 등은 사용 불가)';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    if(isValidPassword($data['id'], $data['pw'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 104;
        $res['message'] = '이전의 비밀번호와 똑같습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    else{
        $query = 'UPDATE User SET password = ? WHERE ID = ?';
        $st = $pdo->prepare($query);
        $st->execute([$data['pw'], $data['id']]);
        $st = null;
        $pdo = null;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '비밀번호가 성공적으로 변경되었습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
}

function updateBirth($data){
    $pdo = pdoSqlConnect();
    $query = 'UPDATE User SET birth = ? WHERE ID = ?';
    $st = $pdo->prepare($query);
    $st->execute([$data['birth'], $data['id']]);
    $st = null;
    $pdo = null;
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '생년월일이 성공적으로 변경되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}

function deleteSelfie($data){
    $pdo = pdoSqlConnect();
    $url = SERVER_URL.'/uploads/user/default.png';
    $query = 'UPDATE UserImage SET status = 0 WHERE userID = ?;
              UPDATE UserImage SET status = 100 WHERE userID = ? and URL = ?;';
    $st = $pdo->prepare($query);
    $st->execute([$data['id'],$data['id'],$url]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '프로필 사진이 성공적으로 삭제되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}

function updateUser($data){
    $pdo = pdoSqlConnect();
    if(isExistImage($data['id'],$data['image'])){
        $query = "UPDATE User SET name = ? WHERE ID = ?;
                  UPDATE UserImage SET status = 0 WHERE userID = ?;
                  UPDATE UserImage SET status = 100 WHERE userID=? and URL=?;";
        $st = $pdo->prepare($query);
        $st->execute([$data['name'], $data['id'],$data['id'],$data['id'],$data['image']]);
        $st = null;
    }
    else{
        $query = "UPDATE User SET name = ? WHERE ID = ?;
                  UPDATE UserImage SET status = 0 WHERE userID = ?;
                  INSERT INTO UserImage (userID, URL, size) VALUES (?, ?, ?);";
        $st = $pdo->prepare($query);
        $st->execute([$data['name'], $data['id'],$data['id'],$data['id'], $data['image'], 100]);
        $st = null;
    }
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '유저 정보가 성공적으로 변경되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}

function getUser($data){
    $pdo = pdoSqlConnect();
    $url = "\'".SERVER_URL.'/uploads/user/default.png'."\'";
    if(isExistSelfie($data['id'])){
        $query =
            "select User.id as userID, User.code as userCode, User.status as appPW, ifnull(User.email,'카카오 로그인') as email, User.name as name,
        concat(year(User.birth),'-',if(month(User.birth)<10, concat(0,month(User.birth)),month(User.birth)),'-',day(User.birth)) as birth,
        ifnull(UserImage.URL,?) as selfie
        from User left join UserImage on User.id = UserImage.userID where User.ID = ? and UserImage.status not like 0;";
    }
    else{
        $query =
            "select User.id as userID, User.code as userCode, User.status as appPW, ifnull(User.email,'카카오 로그인') as email, User.name as name,
        concat(year(User.birth),'-',if(month(User.birth)<10, concat(0,month(User.birth)),month(User.birth)),'-',day(User.birth)) as birth,
        ? as selfie
        from User where User.ID = ?;";
    }

    $st = $pdo->prepare($query);
    $st->execute([$data['id']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    $result[0]['userID'] = (int)$result[0]['userID'];
    $result[0]['appPW'] = (int)$result[0]['appPW'];

    $res['result'] = $result[0];
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '성공적으로 조회되었습니다.';
    echo json_encode($res);
    return;
}

function getUserByUserCode($data){
    $url = "\'".SERVER_URL.'/uploads/user/default.png'."\'";
    $pdo = pdoSqlConnect();
    if(isExistSelfie(userCodeToID($data['pathVar']))){
        $query =
"select 
       User.id as userID,  
       User.code as userCode, 
       ifnull(User.email,'카카오 로그인') as email, 
       User.name as name,
       concat(year(User.birth),'-',if(month(User.birth)<10, concat(0,month(User.birth)),month(User.birth)),'-',day(User.birth)) as birth,
       UserImage.URL as selfie
from User 
    left join UserImage on User.id = UserImage.userID 
where User.code = ? and UserImage.status not like 0;";
    }
    else{
        $query =
            "select User.id as userID,  User.code as userCode, ifnull(User.email,'카카오 로그인') as email, User.name as name,
        concat(year(User.birth),'-',if(month(User.birth)<10, concat(0,month(User.birth)),month(User.birth)),'-',day(User.birth)) as birth,
        "+$url+" as selfie
        from User where User.code = ?;";
    }

    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    $result[0]['userID'] = (int)$result[0]['userID'];

    $res['result'] = $result[0];
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '성공적으로 조회되었습니다.';
    echo json_encode($res);
    return;
}

function getTmpPw($data){
    $tmpPW = createCode();
    $email = $data['id'];

    if(!isValidEmail($email)){
        $res['isSuccess'] = FALSE;
        $res['code'] = 103;
        $res['message'] = 'TODA 계정이 아닙니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $pdo = pdoSqlConnect();
    $query = 'UPDATE User SET password = ? WHERE ID = ?';
    $st = $pdo->prepare($query);
    $st->execute([$tmpPW, emailToID($email)]);
    $st = null;
    $pdo = null;

    $sendData = Array(
        'tmpPW' => $tmpPW,
        'email' => $email
    );
    sendToAlarmServer('/mail/pw',$sendData);

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '임시 비밀번호가 발급되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function getLog($data){
    $res = (object)Array();
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
inner join User as Send on Send.ID = Log.sendID and Send.status not like 99999
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
    if(!empty($userIDArray)){
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
    if(empty($result)){
        $res->result = $none;
        $res->isSuccess = TRUE;
        $res->code = 100;
        $res->message = '알림이 존재하지 않습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
    else{
        foreach($result as $i => $value)
            $result[$i]['date'] = convertDate((int)$result[$i]['date']);

        $res->result = $result;
        $res->isSuccess = TRUE;
        $res->code = 100;
        $res->message = '성공적으로 조회되었습니다.';
        echo json_encode($res);
    }
}