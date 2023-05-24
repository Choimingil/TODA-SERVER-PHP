<?php
require './pdos/system.php';

header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API URL. POST /login/ver2
        * API Name : 자체 로그인 API
        * 마지막 수정 날짜 : 21.11.26
        */
        case 'createJwtVer2':
            http_response_code(200);
            $body = getBody($req, array('id', 'pw'));
            if (!isValidUser($body['id'], $body['pw']))
                new DefaultResponse(FALSE,103,'비밀번호가 잘못되었습니다.');
            else{
                $res = createJwt($body, JWT_SECRET_KEY);
                new ResultResponse($res,true,100,'성공적으로 로그인되었습니다.');
            }
            break;

        /*
        * API URL. POST /notification/ver2
        * API Name : 알림 토큰 저장 api
        * 마지막 수정 날짜 : 21.11.27
        */
        case 'checkNotificationVer2':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $body = getBody($req, array('token','isAllowed','type'));
            checkNotification($userID,$body);
            new DefaultResponse(true,100,'토큰이 저장되었습니다.');
            break;

        /*
        * API URL. PATCH /alarm/ver2
        * API Name : 알림 허용 변경 API
        * 마지막 수정 날짜 : 21.11.27
        */
        case 'updateAlarmVer2':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $body = getBody($req, array('alarmType','fcmToken'));
            if(!isExistToken($userID))
                new DefaultResponse(FALSE,102,'알림 토큰이 저장되어있지 않습니다.');
            else{
                $res = updateAlarmVer2($userID,$body);
                if($res == 100) new DefaultResponse(true,100,'알림이 허용되었습니다.');
                else if($res == 200) new DefaultResponse(true,200,'알림이 해제되었습니다.');
                else if($res == 102) new DefaultResponse(FALSE,102,'토큰이 올바르지 않습니다. 알림 토큰 저장 API 재실행 필요');
                else new DefaultResponse(FALSE,404,'실패');
            }
            break;

        /*
        * API URL. PATCH /alarm/time
        * API Name : 알림 시간 설정 API
        * 마지막 수정 날짜 : 21.11.27
        */
        case 'updateAlarmTime':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $body = getBody($req, array('time','fcmToken'));
            if(!isExistToken($userID))
                new DefaultResponse(FALSE,102,'알림 토큰이 저장되어있지 않습니다.');
            else if(!getRemindAllowedByDevice($userID,$body['fcmToken']))
                new DefaultResponse(FALSE,102,'리마인드 알림이 거절된 상태 혹은 토큰이 존재하지 않은 상태입니다.');
            else{
                updateAlarmTime($userID,$body);
                new DefaultResponse(true,100,'성공적으로 설정되었습니다.');
            }
            break;

        /*
        * API URL. GET /cache
        * API Name : 캐시 서버 토큰 조회 API
        * 마지막 수정 날짜 : 21.11.27
        */
        case 'getJwtCache':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $res = getJwtCache($userID);
            new ResultResponse($res,true,100,'성공적으로 설정되었습니다.');
            break;

        /*
        * API URL. GET /synchronization
        * API Name : 전체 데이터 동기화 API
        * 마지막 수정 날짜 : 21.11.27
        */
        case 'getSynchronization':
            require './pdos/synchronization.php';
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $page = getQS('page');
            $res = getSynchronization($userID,$page);
            if($res != false) new ResultResponse($res,true,100,'쿼리 조회가 완료되었습니다.');
            break;

        /*
        * API URL. GET /update/ver2
        * API Name : 강제업데이트 API
        * 마지막 수정 날짜 : 21.11.27
        */
        case 'checkUpdateVer2':
            http_response_code(200);
            $type = getQS('type');
            $version = getQS('version');
            $res = checkUpdateVer2($type,$version);
            if($res == 100) new DefaultResponse(true,100,'최신 버전입니다.');
            else if($res == 200) new DefaultResponse(true,200,'최신 버전이 아닙니다.');
            else if($res == 300) new DefaultResponse(true,300,'서버 업데이트 중입니다');
            else new DefaultResponse(false,102,'실패');
            break;

        /*
        * API URL. GET /token/ver2
        * API Name : 토큰 데이터 추출 API
        * 마지막 수정 날짜 : 21.12.05
        */
        case 'decodeTokenVer2':
            http_response_code(200);
            $res = decodeTokenVer2();
            new ResultResponse($res,true,100,'자체 로그인 헤더 성공');
            break;

        /*
        * API URL. GET /validation
        * API Name : 유효성 검사 API
        * 마지막 수정 날짜 : 21.12.08
        */
        case 'getValidation':
            http_response_code(200);

            // 이메일 중복 검사 부분
            if(isset($_GET['email'])){
                $email = getQS('email');
                $res = (boolean)isValidEmail($email);
                new ResultResponse(!$res,true,100,'성공');
                return;
            }
            break;

        /*
        * API URL. POST /announcement
        * API Name : 공지사항 읽기 API
        * 마지막 수정 날짜 : 22.1.5
        */
        case 'readAnnouncement':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $announcementID = getPathVar($vars,'announcement');
            readAnnouncement($userID,$announcementID);
            new DefaultResponse(true,100,'성공');
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
