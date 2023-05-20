<?php

function createUser($body){
    $code = createCode();
    $url = "\'"+SERVER_URL.'/uploads/user/default.png'+"\'";
    $query = "INSERT INTO User (email, password, name, birth, code) VALUES (?, ?, ?, ?, ?);";
    $userID = lastInsertID($query,[$body['email'], $body['password'], $body['name'], "2020-08-30", $code]);
    $queryImage = "INSERT INTO UserImage (userID, URL, size) VALUES (?,$url,100);";
    execute($queryImage,[$userID]);

    return $userID;
}

function deleteUser($userID){
    $query = 'UPDATE User SET status = 99999 WHERE ID = ?';
    return execute($query,[$userID]);
}

function updateUser($userID,$body){
    $query = "UPDATE User SET name = ? WHERE ID = ?;
              UPDATE UserImage SET status = 0 WHERE userID = ?;
              INSERT INTO UserImage (userID, URL, size) VALUES (?, ?, ?);";
    return execute($query,[$body['name'],$userID,$userID,$userID,$body['image'],100]);
}

function updateName($userID,$body){
    $query = "UPDATE User SET name = ? WHERE ID = ?;";
    return execute($query,[$body['name'],$userID]);
}

function updateSelfie($userID,$body){
    $query = "UPDATE UserImage SET status = 0 WHERE userID = ?;
              INSERT INTO UserImage (userID, URL, size) VALUES (?, ?, ?);";
    return execute($query,[$userID,$userID,$body['image'],100]);
}

function postLock($userID,$pw,$body): object
{
    $query = "UPDATE User SET status = ? WHERE ID = ?";
    execute($query,[$body['appPW'], $userID]);

    $data = array(
        'id' => IDToEmail($userID),
        'pw' => $pw
    );
    return getToken($data);
}

function deleteLock($userID,$pw): object
{
    $query = "UPDATE User SET status = 10000 WHERE ID = ?";
    execute($query,[$userID]);

    $data = array(
        'id' => IDToEmail($userID),
        'pw' => $pw
    );
    return  getToken($data);
}

function updatePasswordVer2($userID, $body): object
{
    $query = 'UPDATE User SET password = ? WHERE ID = ?';
    execute($query,[$body['pw'], $userID]);

    $data = array(
        'id' => IDToEmail($userID),
        'pw' => $body['pw']
    );
    return getToken($data);
}

function getUserByUserCodeVer2($userID,$userCode): array
{
    $query =
    "select
       User.ID as userID,
       User.code as userCode,
       User.email as email,
       User.name as name,
       UserImage.url as selfie,
      concat(
          '[',
          group_concat(
              json_object(
                  'token',ifnull(Notification.token,''),
                  'device',ifnull(Notification.status,'')
                  )
              ),
           ']'
       ) as token
from User
inner join UserImage on UserImage.userID = User.ID and UserImage.status not like 0
inner join Notification on Notification.userID = User.ID and Notification.status not like 0 and Notification.isAllowed = 'Y'
where User.code = ?;";
    $res = execute($query,[$userCode]);
    $res[0]['userID'] = (int)$res[0]['userID'];
    $res[0]['token'] = json_decode($res[0]['token']);
    return $res[0];
}

function getTmpPW($body){
    $tmpPW = createCode();
    $email = $body['id'];

    // sendMail($tmpPW,$email);

    $query = 'UPDATE User SET password = ? WHERE ID = ?';
    return execute($query,[$tmpPW,emailToID($email)]);
}

function getToken($data): object
{
    $jwt = getJWToken($data,JWT_SECRET_KEY);

    if (isUpdating()['isUpdating'] == 'Y') {
        $res = (object)Array(
            'jwt' => $jwt,
            'isUpdating' => true,
            'startTime' => isUpdating()['startTime'],
            'finishTime' => isUpdating()['finishTime']
        );
    } else {
        $res = (object)Array(
            'jwt' => $jwt,
            'isUpdating' => false,
            'startTime' => false,
            'finishTime' => false
        );
    }
    return $res;
}