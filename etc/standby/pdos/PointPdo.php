<?php

function postPoint($data){
    if($data['point']<0){
        $res['isSuccess'] = FALSE;
        $res['code'] = 319;
        $res['message'] = "잘못된 포인트 사용입니다.";
        echo json_encode($res, JSON_NUMERIC_CHECK);
        return;
    }

    $pdo = pdoSqlConnect();
    $query = "select point from User where ID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$data['id']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $value = $st->fetchAll();

    $st = null;

    $point = $data['point'] + $value[0]['point'];

    $query = "UPDATE User SET point = ? WHERE ID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$point, $data['id']]);

    $st = null;
    $pdo = null;

    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "포인트가 적립되었습니다.";
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}

function getPoint($data){

    $pdo = pdoSqlConnect();
    $query =
        "select point from User where ID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$data['id']]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();

    $st = null;
    $pdo = null;

    $res['result'] = $result[0]['point'];
    $res['isSuccess'] = TRUE;
    $res['code'] = 100;
    $res['message'] = "성공적으로 조회되었습니다.";
    echo json_encode($res, JSON_NUMERIC_CHECK);
    return;
}