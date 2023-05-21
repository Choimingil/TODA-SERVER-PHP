<?php
require './vendor/autoload.php';

use Firebase\JWT\JWT;

function createJwt($data, $secretKey){
    if(!isValidUser($data['id'], $data['pw'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 103;
        $res['message'] = '비밀번호가 잘못되었습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $token = array(
        'date' => (string)getTodayByTimeStamp(),
        'id' => (string)$data['id'],
        'pw' => (string)$data['pw'],
        'appPW' => (string)getUserStatus($data['id'])
    );

    $jwt = JWT::encode($token, $secretKey);
    $token = null;

    if(isUpdating()['isUpdating']=='Y'){
        $res['result'] = $jwt;
        $res['isUpdating'] = true;
        $res['startTime'] = isUpdating()['startTime'];
        $res['finishTime'] = isUpdating()['finishTime'];
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '성공적으로 로그인되었습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
    else{
        $res['result'] = $jwt;
        $res['isUpdating'] = false;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '성공적으로 로그인되었습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
//    return;
}

function getJwtCache($data){
    $url = "\'".SERVER_URL.'/uploads/user/default.png'+"\'";
    $pdo = pdoSqlConnect();
    if(isExistSelfie($data['id'])){
        $query =
            "select User.id as userID, User.code as userCode, User.status as appPW, ifnull(User.email,'카카오 로그인') as email, User.name as name,
        concat(year(User.birth),'-',if(month(User.birth)<10, concat(0,month(User.birth)),month(User.birth)),'-',day(User.birth)) as birth,
        ifnull(UserImage.URL,"+$url+") as selfie
        from User left join UserImage on User.id = UserImage.userID where User.ID = ? and UserImage.status not like 0;";
    }
    else{
        $query =
            "select User.id as userID, User.code as userCode, User.status as appPW, ifnull(User.email,'카카오 로그인') as email, User.name as name,
        concat(year(User.birth),'-',if(month(User.birth)<10, concat(0,month(User.birth)),month(User.birth)),'-',day(User.birth)) as birth,
        "+$url+" as selfie
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

    $jwt = getJWTokenCache($result[0],JWT_SECRET_KEY);
    
    $res['result'] = $jwt;
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '성공적으로 로그인되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function createUser($data){
    $code = createCode();
    $url = "\'".SERVER_URL.'/uploads/user/default.png'+"\'";

    while(isValidUserCode($code) == 1){
        $code = createCode();
    }

    $pdo = pdoSqlConnect();
    $query = "INSERT INTO User (email, password, name, birth, code) VALUES (?, ?, ?, ?, ?);";
    $st = $pdo->prepare($query);
    $st->execute([$data['email'], $data['password'], $data['name'], "2020-08-30", $code]);
    $st = null;
    $userID = $pdo->lastInsertId();
    $pdo = null;

    $pdo = pdoSqlConnect();
    $query = "INSERT INTO UserImage (userID, URL, size) VALUES (?,$url,100);";
    $st = $pdo->prepare($query);
    $st->execute([$userID]);
    $st = null;
    $pdo = null;
    $userID = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '회원가입이 완료되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function createUserKakao($id){
    $code = createCode();
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO User (kakao, code) VALUES (?,?);";
    $st = $pdo->prepare($query);
    $st->execute([$id, $code]);
    $st = null;
    $pdo = null;
}

function postUserInfo($data){
    if(isAlreadyFinish($data['id'])){
        $res['isSuccess'] = FALSE;
        $res['code'] = 503;
        $res['message'] = '이미 이름과 생년월일이 기입된 상태입니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $pdo = pdoSqlConnect();
    $query = "UPDATE User SET name = ?, birth = ? WHERE ID = ?;
              INSERT INTO UserImage (userID) VALUES (?);";
    $st = $pdo->prepare($query);
    $st->execute([$data['name'], $data['birth'], $data['id'],$data['id']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '간편 회원가입이 완료되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
//    return;
}

function postLock($data){
    $pdo = pdoSqlConnect();
    $query = "UPDATE User SET status = ? WHERE ID = ?";
    $st = $pdo->prepare($query);
    $st->execute([$data['appPW'], $data['id']]);
    $st = null;
    $pdo = null;

    $res['token'] = getJWTokenNew($data, JWT_SECRET_KEY);
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '앱 비밀번호가 설정되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);

    // redis 키값 : 테섭 본섭에 맞춰서 변경
    $redisKey = DB_NAME.IDToEmail($data['id']);

    // redis에 유저 데이터 존재한다면 통과
    $userRedis = json_decode(getRedis($redisKey),true);

    // 구한 정보 redis에 저장
    $dataArray = Array(
        'email' => $userRedis['email'],
        'id' => (int)$userRedis['id'],
        'pw' => $userRedis['pw'],
        'appPW' => $data['appPW']
    );
    setRedis($redisKey,json_encode($dataArray));
//    return;
}

function deleteLock($data){
    $pdo = pdoSqlConnect();
    $query = "UPDATE User SET status = 10000 WHERE ID = ?";
    $st = $pdo->prepare($query);
    $st->execute([$data['id']]);
    $st = null;
    $pdo = null;

    $res['token'] = getJWTokenNew($data, JWT_SECRET_KEY);
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '앱 잠금이 해제되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);

    // redis 키값 : 테섭 본섭에 맞춰서 변경
    $redisKey = DB_NAME.IDToEmail($data['id']);

    // redis에 유저 데이터 존재한다면 통과
    $userRedis = json_decode(getRedis($redisKey),true);

    // 구한 정보 redis에 저장
    $dataArray = Array(
        'email' => $userRedis['email'],
        'id' => (int)$userRedis['id'],
        'pw' => $userRedis['pw'],
        'appPW' => 10000
    );
    setRedis($redisKey,json_encode($dataArray));
//    return;
}

function getPopupRead($data){
    $pdo = pdoSqlConnect();
    $query = 'select EXISTS(select * from PopUp where userID = ? and version = ?) AS exist;';
    $st = $pdo->prepare($query);
    $st->execute([$data['id'],$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    if(intval($result[0]['exist'])==0) $res['result'] = FALSE;
    else $res['result'] = TRUE;
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '성공적으로 조회되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
//    return;
}

function updatePopupRead($data){
    $pdo = pdoSqlConnect();
    $query = 'select EXISTS(select * from PopUp where userID = ?) AS exist;';
    $st = $pdo->prepare($query);
    $st->execute([$data['id']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;

    if(intval($result[0]['exist'])==0) $query = "INSERT INTO PopUp (version,userID) VALUES (?,?);";
    else $query = "UPDATE PopUp SET version = ? WHERE userID = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar'],$data['id']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '팝업을 읽었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
//    return;
}

function getAnnouncement($data){
    $pdo = pdoSqlConnect();
    $query = "select ID as announcementID, title as title, date_format(createAt, '%Y.%m.%d') as date, null as isRead
        from Announcement where status not like 0 order by Announcement.createAt desc limit ".$data['page'].", 20;";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    foreach($result as $i=>$value){
        $result[$i]['announcementID'] = (int)$result[$i]['announcementID'];
        $result[$i]['isRead'] = isReadAnnouncement($data['id'],$result[$i]['announcementID']);
    }

    $res['result'] = $result;
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '성공적으로 조회되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
//    return;
}

function getAnnouncementDetail($data){
    $pdo = pdoSqlConnect();
    if(isAlreadyReadAnnouncement($data)){
        $query = 'select title as title, date_format(createAt, \'%Y.%m.%d\') as date, image as image, text as text from Announcement where status not like 0 and ID = ?;';
        $st = $pdo->prepare($query);
        $st->execute([$data['pathVar']]);
    }
    else{
        $query = 'select title as title, date_format(createAt, \'%Y.%m.%d\') as date, image as image, text as text from Announcement where status not like 0 and ID = ?;
        INSERT INTO UserAnnouncement (userID,announcementID) values (?,?);';
        $st = $pdo->prepare($query);
        $st->execute([$data['pathVar'],$data['id'],$data['pathVar']]);
    }
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    $res['result'] = $result[0];
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '성공적으로 조회되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
//    return;
}

function getAnnouncementCheck($data){
    $pdo = pdoSqlConnect();
    $query = 'select if(count(distinct announcementID) = (select count(ID) from Announcement),true,false) as exist from UserAnnouncement UA where UA.userID = ?;';
    $st = $pdo->prepare($query);
    $st->execute([$data['id']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    if(intval($result[0]['exist']) == 0) $res['result'] = false;
    else $res['result'] = true;
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = '성공적으로 조회되었습니다.';
    echo json_encode($res, JSON_NUMERIC_CHECK);
}