<?php
require './pdos/diary.php';

header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API URL. POST /diary/ver2
        * API Name : 다이어리 추가 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'addDiaryVer2':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $body = getBody($req, array('diaryID', 'status', 'title', 'color'));
            addDiaryVer2($userID,$body);
            new DefaultResponse(true,100,'다이어리가 추가되었습니다.');
            break;

        /*
        * API URL. POST /diaries/{diaryID:\d+}/user/ver2
        * API Name : 다이어리 유저 추가 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'addDiaryFriendVer2':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $diaryID = getPathVar($vars,'diaryID');
//            $body = getBody($req, array('userCode', 'userName', 'diaryName', 'diaryStatus', 'tokenList', 'deviceList'));
            $body = getBody($req, array('userCode', 'userName', 'diaryName', 'diaryStatus'));

            if($userID == userCodeToID($body['userCode']))
                new DefaultResponse(false,103,'자기 자신을 등록할 수 없습니다.');
            else{
                $receiveUser = getUserByCode($body['userCode']);
                if(isSendRequest($userID,$diaryID)){
                    acceptDiaryFriend($userID,$diaryID,$body,$receiveUser);
                    new DefaultResponse(true,100,'다이어리 초대 요청을 승낙하였습니다.(100)');
                }
                else{
                    if(isSendRequest($receiveUser['userID'],$diaryID))
                        new DefaultResponse(false,104,'이미 초대한 사용자입니다.');
                    else if(!isDiaryUser($userID,$diaryID))
                        new DefaultResponse(false,103,'다이어리에 등록되지 않은 사용자입니다.');
                    else if(isDiaryUser($receiveUser['userID'],$diaryID))
                        new DefaultResponse(false,104,'이미 다이어리에 등록된 사용자입니다.');
                    else if(isAloneDiary($diaryID))
                        new DefaultResponse(false,103,'혼자 쓰는 다이어리에 친구를 초대할 수 없습니다.');
                    else{
                        sendDiaryFriend($userID,$diaryID,$body,$receiveUser);
                        new DefaultResponse(true,200,'다이어리 초대 요청이 발송되었습니다.(200)');
                    }
                }
            }
            break;

        /*
        * API URL. DELETE /diary/{diaryID:\d+}
        * API Name : 다이어리 퇴장 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'deleteDiary':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $diaryID = getPathVar($vars,'diaryID');
            if(isSendRequest($userID,$diaryID)){
                rejectDiary($userID,$diaryID);
                new DefaultResponse(true,100,'다이어리 초대가 거절되었습니다.');
            }
            else{
                if(!isDiaryUser($userID,$diaryID))
                    new DefaultResponse(FALSE,103,'다이어리에 등록되지 않은 사용자입니다.');
                else{
                    deleteDiary($userID,$diaryID);
                    new DefaultResponse(true,100,'다이어리 탈퇴가 완료되었습니다.');
                }
            }
            break;

        /*
        * API URL. PATCH /diary
        * API Name : 다이어리 수정 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'updateDiary':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $body = getBody($req, array('diary', 'status', 'title', 'color'));
            if(!isDiaryUser($userID,$body['diary']))
                new DefaultResponse(FALSE,103,'다이어리에 등록되지 않은 사용자입니다.');
            else{
                $prevStatus = substr(getDiaryStatus($userID,$body['diary']),-1,1);
                $currStatus = $body['status'] - $body['color']*100;
                if( ($prevStatus == 3 && $currStatus == 2) or // 함께 쓰는 다이어리(북마크) --> 혼자 쓰는 다이어리
                    ($prevStatus == 4 && $currStatus == 1) or // 혼자 쓰는 다이어리(북마크) --> 함께 쓰는 다이어리
                    ($prevStatus == 3 && $currStatus == 4))   // 함께 쓰는 다이어리(북마크) --> 혼자 쓰는 다이어리(북마크)
                    new DefaultResponse(FALSE,103,'잘못된 다이어리 변경입니다.');
                else{
                    updateDiary($userID,$body,$prevStatus,$currStatus);
                    new DefaultResponse(true,100,'다이어리 수정이 완료되었습니다.');
                }
            }
            break;

        /*
        * API URL. POST /notice
        * API Name : 다이어리 공지 등록 및 수정 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'postNotice':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $body = getBody($req, array('diary', 'notice'));
            if(!isDiaryUser($userID,$body['diary']))
                new DefaultResponse(FALSE,103,'다이어리에 등록되지 않은 사용자입니다.');
            else{
                postNotice($userID,$body);
                new DefaultResponse(true,100,'다이어리 공지가 등록되었습니다.');
            }
            break;

        /*
        * API URL. DELETE /notice/{diaryID:\d+}
        * API Name : 다이어리 공지 삭제 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'deleteNotice':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $diaryID = getPathVar($vars,'diaryID');
            if(!isDiaryUser($userID,$diaryID))
                new DefaultResponse(FALSE,103,'다이어리에 등록되지 않은 사용자입니다.');
            else if(!isExistNotice($diaryID))
                new DefaultResponse(FALSE,103,'존재하지 않는 공지입니다.');
            else{
                deleteNotice($userID,$diaryID);
                new DefaultResponse(true,100,'다이어리 공지가 삭제되었습니다.');
            }
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
