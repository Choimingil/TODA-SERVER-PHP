<?php
require './pdos/sticker.php';

header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API URL. POST /posts/{postID:\d+}/stickers/ver2
        * API Name : 스티커 사용 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'addStickerVer2':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $body = getBody($req, array('post','stickerArr'));
            $postID = $body['post'];

            if(!isValidPostDiary($userID,$postID))
                new DefaultResponse(false,103,'게시물을 볼 수 있는 권한이 없습니다.');
            else{
                // 배열에 스티커 데이터 저장
                $stickerArray = Array(); // 쿼리 추가용 배열
                $stickerDataArray = Array(); // 데이터 추가용 배열

                // 추가된 스티커 정보 배열에 넣기
                foreach ($body['stickerArr'] as $i=>$value){
                    if(!isValidUserSticker($userID, $value['stickerID'])){
                        new DefaultResponse(false,103,'보유한 스티커가 아닙니다.');
                        return;
                    }
                    else{
                        array_push($stickerArray,'(?,?,?,?,?,?,?)');
                        array_push($stickerDataArray,$userID);
                        array_push($stickerDataArray,$postID);
                        array_push($stickerDataArray,$value['stickerID']);
                        array_push($stickerDataArray,$value['device']);
                        array_push($stickerDataArray,$value['x']);
                        array_push($stickerDataArray,$value['y']);
                        array_push($stickerDataArray,$value['inversion'] + $value['layerNum']*10);
                    }
                }
                addStickerVer2($userID,$postID,$body,$stickerArray,$stickerDataArray);
                new DefaultResponse(true,100,'스티커 사용이 완료되었습니다.');
            }
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
