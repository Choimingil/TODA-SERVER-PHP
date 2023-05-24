<?php
requires();

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API No. 26
        * API Name : 포인트 사용 API
        * 마지막 수정 날짜 : 20.09.12
        */
        case "postPoint":
            http_response_code(200);

            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);

            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('point');
            $body = isValidBody($req, $key);

            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($header, $body);

            postPoint($data);
            break;

        /*
        * API No. 27
        * API Name : 포인트 조회 API
        * 마지막 수정 날짜 : 20.09.12
        */
        case "getPoint":
            http_response_code(200);

            $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);

            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            getPoint($header);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
