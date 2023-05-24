<?php
require './pdos/user.php';

header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API URL. POST /user
        * API Name : 자체 회원가입 API
        * 마지막 수정 날짜 : 21.11.27
        */
        case 'createUserVer2':
            http_response_code(200);
            $body = getBody($req, array('email', 'password', 'name', 'birth'));
            $res = createUser($body);
            new ResultResponse($res,true,100,'회원가입이 완료되었습니다.');
            break;

        /*
        * API URL. DELETE /user
        * API Name : 회원탈퇴 API
        * 마지막 수정 날짜 : 21.11.27
        */
        case 'deleteUser':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            deleteUser($userID);
            new DefaultResponse(true,100,'회원탈퇴가 완료되었습니다');
            break;

        /*
        * API URL. PATCH /user
        * API Name : 유저 정보 변경 API(프사&닉네임)
        * 마지막 수정 날짜 : 21.11.27
        */
        case 'updateUser':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);

            if(array_keys($req) == array('name')){
                $body = getBody($req, array('name'));
                updateName($userID,$body);
                new DefaultResponse(true,100,'유저 정보가 성공적으로 변경되었습니다');
            }
            else if (array_keys($req) == array('image')){
                $body = getBody($req, array('image'));
                updateSelfie($userID,$body);
                new DefaultResponse(true,100,'유저 정보가 성공적으로 변경되었습니다');
            }
            else{
                $body = getBody($req, array('name', 'image'));
                updateUser($userID,$body);
                new DefaultResponse(true,100,'유저 정보가 성공적으로 변경되었습니다');
            }
            break;

        /*
        * API URL. POST /lock
        * API Name : 앱 잠금 비밀번호 설정 및 변경 api
        * 마지막 수정 날짜 : 21.11.27
        */
        case 'postLockVer2':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $pw = getPW('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $body = getBody($req, array('appPW'));
            $res = postLock($userID,$pw,$body);
            new ResultResponse($res,true,100,'앱 비밀번호가 설정되었습니다.');
            break;

        /*
        * API URL. DELETE /lock
        * API Name : 앱 잠금 비밀번호 설정 및 변경 api
        * 마지막 수정 날짜 : 21.11.27
        */
        case 'deleteLockVer2':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $pw = getPW('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $res = deleteLock($userID,$pw);
            new ResultResponse($res,true,100,'앱 잠금이 해제되었습니다');
            break;

        /*
        * API URL. PATCH /password/ver2
        * API Name : 회원 비번 변경 API
        * 마지막 수정 날짜 : 21.12.05
        */
        case 'updatePasswordVer2':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $body = getBody($req, array('pw'));

            if(!isValidEmail(IDToEmail($userID)))
                new DefaultResponse(FALSE,103,'TODA 계정이 아닙니다.(카카오 로그인 등은 사용 불가)');
            else if(isValidPassword($userID, $body['pw']))
                new DefaultResponse(FALSE,104,'이전의 비밀번호와 똑같습니다.');
            else{
                $res = updatePasswordVer2($userID,$body);
                new ResultResponse($res, true,100,'비밀번호가 성공적으로 변경되었습니다.');
            }
            break;

        /*
        * API URL. GET /usercode/{userCode}/user/ver2
        * API Name : 유저코드를 통한 회원정보 조회 API
        * 마지막 수정 날짜 : 21.11.27
        */
        case 'getUserByUserCodeVer2':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $userCode = getPathVar($vars,'userCode');
            $res = getUserByUserCodeVer2($userID,$userCode);
            new ResultResponse($res,true,100,'성공적으로 조회되었습니다');
            break;

        /*
        * API URL. POST /user/searchPW
        * API Name : 비밀번호 찾기 API
        * 마지막 수정 날짜 : 21.11.27
        */
        case 'getTmpPw':
            http_response_code(200);
            $body = getBody($req, array('id'));
            if(!isValidEmail($body['id']))
                new DefaultResponse(FALSE,103,'TODA 계정이 아닙니다.');
            else{
                getTmpPw($body);
                new DefaultResponse(true,100,'임시 비밀번호가 발급되었습니다.');
            }
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
