<?php

//DB 정보
function pdoSqlConnect()
{
    try {
        $DB_HOST = DB_HOST;
        $DB_NAME = DB_NAME;
        $DB_USER = DB_USER;
        $DB_PW = DB_PW;
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (\Exception $e) {
        // new DefaultResponse(FALSE,404,$e->getMessage());
        $res['isSuccess'] = FALSE;
        $res['code'] = 404;
        $res['message'] = $e->getMessage();
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
}

function execute(string $query, array $body): bool|array
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare($query);
    $st->execute($body);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res;
}

function lastInsertID(string $query, array $body)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare($query);
    $st->execute($body);
    $st = null;
    $id = $pdo->lastInsertId();
    $pdo = null;
    return $id;
}