<?php

function buySticker($data){
    if(isValidUserSticker($data['id'], $data['sticker'])){
        $res['isSuccess'] = false;
        $res['code'] = 520;
        $res['message'] = "이미 구매한 스티커입니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $pdo = pdoSqlConnect();
    $query = "select point from User where ID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$data['id']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $userPoint = $st->fetchAll();
    $st = null;

    $query = "select point from Sticker where ID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$data['sticker']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $stickerPoint = $st->fetchAll();
    $st = null;

    if($userPoint[0]['point'] < $stickerPoint[0]['point']){
        $res['isSuccess'] = false;
        $res['code'] = 105;
        $res['message'] = "보유 포인트가 부족합니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }
    $remainPoint = $userPoint[0]['point'] - $stickerPoint[0]['point'];

    $query = "INSERT INTO UserSticker (userID, stickerID) VALUES (?, ?);";
    $st = $pdo->prepare($query);
    $st->execute([$data['id'], $data['sticker']]);
    $st = null;

    $query = "UPDATE User SET point = ? WHERE ID = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$remainPoint, $data['id']]);
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "스티커 구매가 완료되었습니다.";
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function addSticker($data){
    if(!isValidPostDiary($data['id'], $data['pathVar'])){
        $res['isSuccess'] = false;
        $res['code'] = 103;
        $res['message'] = "게시물을 볼 수 있는 권한이 없습니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    // 배열에 스티커 데이터 저장
    $stickerArray = Array(); // 쿼리 추가용 배열
    $stickerDataArray = Array(); // 데이터 추가용 배열

    // 추가된 스티커 정보 배열에 넣기
    foreach ($data['stickerArr'] as $i=>$value){
        if(!isValidUserSticker($data['id'], $data['stickerArr'][$i]['stickerID'])){
            $res['isSuccess'] = false;
            $res['code'] = 102;
            $res['message'] = "보유한 스티커가 아닙니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;
        }

        array_push($stickerArray,'(?,?,?,?,?,?,?)');

        array_push($stickerDataArray,$data['id']);
        array_push($stickerDataArray,$data['pathVar']);
        array_push($stickerDataArray,$data['stickerArr'][$i]['stickerID']);
        array_push($stickerDataArray,$data['stickerArr'][$i]['device']);
        array_push($stickerDataArray,$data['stickerArr'][$i]['x']);
        array_push($stickerDataArray,$data['stickerArr'][$i]['y']);
        array_push($stickerDataArray,$data['stickerArr'][$i]['inversion'] + $data['stickerArr'][$i]['layerNum']*10);
    }

    // 스티커 배열이 비어있는 경우 뒤에 쿼리 실행시키지 않고 끝내기
    if(!empty($stickerDataArray)){
        // 스티커 1차 저장
        $stickerQuery = implode(',',$stickerArray);
        $pdo = pdoSqlConnect();
        $tmpQuery = "INSERT INTO PostSticker (userID, postID, stickerID, device, x, y, status) VALUES".$stickerQuery.";";
        $st = $pdo->prepare($tmpQuery);
        $st->execute($stickerDataArray);
        $st = null;
        $usedStickerID = $pdo->lastInsertId();
        $stickerArray = null;
        $stickerDataArray = null;

        // 2차 정보 배열에 넣기
        $stickerRotateArray = Array(); // 쿼리 추가용 배열
        $stickerScaleArray = Array(); // 쿼리 추가용 배열
        $stickerDataArray = Array(); // 데이터 추가용 배열
        $usedStickerIDArray = Array(); // 추가된 스티커 아이디 배열

        foreach ($data['stickerArr'] as $i=>$value){
            array_push($stickerRotateArray,'(?,?,?,?,?,?,?)');

            array_push($usedStickerIDArray,$usedStickerID+$i);
            array_push($stickerDataArray,$usedStickerID+$i);
            array_push($stickerDataArray,$data['stickerArr'][$i]['rotate']['a']);
            array_push($stickerDataArray,$data['stickerArr'][$i]['rotate']['b']);
            array_push($stickerDataArray,$data['stickerArr'][$i]['rotate']['c']);
            array_push($stickerDataArray,$data['stickerArr'][$i]['rotate']['d']);
            array_push($stickerDataArray,$data['stickerArr'][$i]['rotate']['tx']);
            array_push($stickerDataArray,$data['stickerArr'][$i]['rotate']['ty']);
        }

        foreach ($data['stickerArr'] as $i=>$value){
            array_push($stickerScaleArray,'(?,?,?,?,?)');

            array_push($stickerDataArray,$usedStickerID+$i);
            array_push($stickerDataArray,$data['stickerArr'][$i]['scale']['x']);
            array_push($stickerDataArray,$data['stickerArr'][$i]['scale']['y']);
            array_push($stickerDataArray,$data['stickerArr'][$i]['scale']['width']);
            array_push($stickerDataArray,$data['stickerArr'][$i]['scale']['height']);
        }

        $stickerRotateQuery = implode(',',$stickerRotateArray);
        $stickerScaleQuery = implode(',',$stickerScaleArray);

        $query =
            "INSERT INTO PostStickerRotate (usedStickerID, a, b, c, d, tx, ty) VALUES ".$stickerRotateQuery.";".
            "INSERT INTO PostStickerScale (usedStickerID, x, y, width, height) VALUES ".$stickerScaleQuery.";";
        $st = $pdo->prepare($query);
        $st->execute($stickerDataArray);
        $st = null;
        $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "스티커 사용이 완료되었습니다.";
    echo json_encode($res, JSON_NUMERIC_CHECK);
    }
}

function updateSticker($data){
    $query = '';
    $usedStickerIDArray = Array();
    foreach($data['usedStickerArr'] as $i => $value){
        if(!isValidUserStickerView($data['id'], $data['usedStickerArr'][$i]['usedStickerID'])){
            $res['isSuccess'] = false;
            $res['code'] = 103;
            $res['message'] = "자신이 등록한 스티커가 아닙니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;
        }

        // 디비에 스티커 수정 반영
        $statusValue = $data['usedStickerArr'][$i]['inversion'] + $data['usedStickerArr'][$i]['layerNum']*10;
        $tmpQuery = 'UPDATE PostSticker SET
        userID = '.$data['id'].',
        postID = '.$data['pathVar'].",
        device = '".$data['usedStickerArr'][$i]['device']."',
        x = ".$data['usedStickerArr'][$i]['x'].',
        y = '.$data['usedStickerArr'][$i]['y'].',
        status = '.$statusValue.'
        where ID = '.$data['usedStickerArr'][$i]['usedStickerID'].';';

        $tmpQueryRotate = 'UPDATE PostStickerRotate SET
        a = '.$data['usedStickerArr'][$i]['rotate']['a'].',
        b = '.$data['usedStickerArr'][$i]['rotate']['b'].',
        c = '.$data['usedStickerArr'][$i]['rotate']['c'].',
        d = '.$data['usedStickerArr'][$i]['rotate']['d'].',
        tx = '.$data['usedStickerArr'][$i]['rotate']['tx'].',
        ty = '.$data['usedStickerArr'][$i]['rotate']['ty'].'
        where usedStickerID = '.$data['usedStickerArr'][$i]['usedStickerID'].';';

        $tmpQueryScale = 'UPDATE PostStickerScale SET
        x = '.$data['usedStickerArr'][$i]['scale']['x'].',
        y = '.$data['usedStickerArr'][$i]['scale']['y'].',
        width = '.$data['usedStickerArr'][$i]['scale']['width'].',
        height = '.$data['usedStickerArr'][$i]['scale']['height'].'
        where usedStickerID = '.$data['usedStickerArr'][$i]['usedStickerID'].';';

        $query = $query.$tmpQuery.$tmpQueryRotate.$tmpQueryScale;
        array_push($usedStickerIDArray,$data['usedStickerArr'][$i]['usedStickerID']);
    }

    $query = 'UPDATE PostSticker SET status = 0 WHERE postID = '.$data['pathVar'].' and userID = '.$data['id'].';'.$query;
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare($query);
    $st->execute();
    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "스티커가 수정되었습니다.";
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function getUserStickers($data){
    $pdo = pdoSqlConnect();
    $query =
        "select SP.ID as stickerPackID, SP.miniticon from StickerPack SP 
    left join UserSticker US on SP.ID = US.stickerPackID where US.userID = ? and SP.status not like 0 limit ".$data['page'].", 10;";
    $st = $pdo->prepare($query);
    $st->execute([$data['id']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    $none = Array();
    if(empty($result)){
        $res['result'] = $none;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '등록된 스티커가 없습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
    else{
        $res['result'] = $result;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '성공적으로 조회되었습니다.';
        echo json_encode($res,JSON_NUMERIC_CHECK);
    }
}

function getStickerDetail($data){
    $pdo = pdoSqlConnect();
    $query = "select ID as stickerPackID,name,point,'' as stickerArr from StickerPack where ID = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $parent = $st->fetchAll();
    $st = null;

    $query = "select ID as stickerID, URL as image from Sticker where stickerPackID =?;";
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $child = $st->fetchAll();
    $st = null;
    $pdo = null;

    $imageArr = Array();
    foreach ($child as $i=>$j) array_push($imageArr,$child[$i]);
    $parent[0]['stickerArr'] = $imageArr;

    $res['result'] = $parent[0];
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "성공적으로 조회되었습니다.";
    echo json_encode($res,JSON_NUMERIC_CHECK);
    return;
}

function getStore($data){
    $pdo = pdoSqlConnect();
    $query = "select SP.ID as stickerPackID,SP.name as name, SP.point as point, S.URL as thumbnail from StickerPack SP left join Sticker S on SP.ID = S.stickerPackID group by SP.ID limit ".$data['page'].", 20;";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    $none = Array();
    if(empty($result)){
        $res['result'] = $none;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '등록된 스티커가 없습니다.';
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
    else{
        $res['result'] = $result;
        $res['isSuccess'] = TRUE;
        $res['code'] = 100;
        $res['message'] = '성공적으로 조회되었습니다.';
        echo json_encode($res,JSON_NUMERIC_CHECK);
    }
}

function getStickerView($data){
    if(!isValidPostDiary($data['id'], $data['pathVar'])) return 103;

    $pdo = pdoSqlConnect();
    $query =
        "select 
       PS.postID as postID, 
       PS.ID as usedStickerID, 
       PS.userID as userID, 
       PS.stickerID as stickerID, 
       S.URL as image, 
       PS.device as device,
       PS.x as x, 
       PS.y as y, 
       '' as rotate, 
       '' as scale,
       PS.status%10 as inversion, 
       round(PS.status/10,0) as layerNum, 
       '' as isMySticker
from PostSticker PS 
    left join Sticker S on PS.stickerID = S.ID
where PS.postID = ? 
  and PS.status not like 0 
  limit ".$data['page'].", 20;";
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $st = null;
    $pdo = null;

    $pdo = pdoSqlConnect();
    $query =
        "select 
       PostSticker.ID as usedStickerID, 
       PostStickerRotate.a as a, 
       PostStickerRotate.b as b, 
       PostStickerRotate.c as c, 
       PostStickerRotate.d as d, 
       PostStickerRotate.tx as tx, 
       PostStickerRotate.ty as ty
from PostStickerRotate 
    left join PostSticker on 
        PostSticker.ID = PostStickerRotate.usedStickerID 
        and PostSticker.status not like 0
where PostSticker.postID = ?  
  limit ".$data['page'].", 20;";
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $rotate = $st->fetchAll();
    $st = null;
    $pdo = null;

    $pdo = pdoSqlConnect();
    $query =
        "select 
       PostSticker.ID as usedStickerID, 
       PostStickerScale.x as x, 
       PostStickerScale.y as y, 
       PostStickerScale.width as width, 
       PostStickerScale.height as height
from PostStickerScale 
    left join PostSticker on 
        PostSticker.ID = PostStickerScale.usedStickerID
        and PostSticker.status not like 0
where PostSticker.postID = ?
 limit ".$data['page'].", 20;";
    $st = $pdo->prepare($query);
    $st->execute([$data['pathVar']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $scale = $st->fetchAll();
    $st = null;
    $pdo = null;

    foreach ($result as $i=>$value){
        $result[$i]['isMySticker'] = isMySticker($data['id'],$result[$i]['usedStickerID']);
        foreach($rotate as $j=>$value2){
            if($result[$i]['usedStickerID'] == $rotate[$j]['usedStickerID']){
                $result[$i]['rotate'] = $rotate[$j];
                unset($result[$i]['rotate']['usedStickerID']);
            }
        }
        foreach($rotate as $j=>$value2){
            if($result[$i]['usedStickerID'] == $scale[$j]['usedStickerID']){
                $result[$i]['scale'] = $scale[$j];
                unset($result[$i]['scale']['usedStickerID']);
            }
        }
    }

    $none = Array();
    if(empty($result)) return $none;
    else return $result;
}