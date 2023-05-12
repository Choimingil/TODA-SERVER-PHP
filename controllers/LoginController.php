<?php
requires();

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents('php://input'), true);

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
         * API No. 1
         * API Name : 자체 로그인 API
         * 마지막 수정 날짜 : 19.04.25
         */
        case 'createJwt':
            // jwt 유효성 검사
            http_response_code(200);
            $key = array('id', 'pw');
            $body = isValidBody($req, $key);

            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            createJwt($body, JWT_SECRET_KEY);
            break;

        /*
        * API No. 1
        * API Name : 캐시 서버 토큰 조회 API
        * 마지막 수정 날짜 : 19.04.25
        */
        case 'getJwtCache':
            // jwt 유효성 검사
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            getJwtCache($header);
            break;

        /*
        * API No. 1-1
        * API Name : 간편 로그인 API
        * 마지막 수정 날짜 : 20.09.04
        */

        case 'createSimpleJwt':
            http_response_code(200);

            $pathVar = isValidPathVar($vars['code'], 'code');
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('token');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($pathVar, $body);

            createSimpleJwt($data['token'], $data['pathVar']);
            break;

        /*
        * API No. 1-2
        * API Name : 이메일 중복확인 API
        * 마지막 수정 날짜 : 20.09.10
        */
        case 'isValidEmail':
            http_response_code(200);

            $key = array('email');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = '사용 가능한 이메일입니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        /*
        * API No. 1-3
        * API Name : 토큰 데이터 추출 API
        * 마지막 수정 날짜 : 20.09.10
        */
        case 'decodeToken':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if(!isStickerSet($header['id']) && isExistUserID($header['id'])) setBasicStickers($header['id']);
            echo json_encode($header, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 1-4
        * API Name : 토큰 암호 유효성 검사 API
        * 마지막 수정 날짜 : 20.09.10
        */
        case 'checkToken':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            if($header['appPW'] == 10000){
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = '유효한 유저입니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('appPW');
            $body = isValidBody($req, $key);

            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }
            if((string)$header['appPW'] == (string)$body['appPW']){
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = '유효한 유저입니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $res->isSuccess = FALSE;
                $res->code = 401;
                $res->message = "비밀번호가 잘못되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            break;

        /*
        * API No. 1-6
        * API Name : 강제 업데이트 API
        * 마지막 수정 날짜 : 20.12.27
        */
        case 'checkUpdate':
            http_response_code(200);
            $queryString = isValidQueryString('type');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $header['type'] = $queryString['queryString'];

            $queryString = isValidQueryString('version');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $header['version'] = $queryString['queryString'];

            switch ($header['type']){
                case 1:
                    if(IOSversion==$header['version'] || IOSversionOld==$header['version']){
                        $code=100;
                        $message='최신 버전입니다.';
                    }
                    else{
                        $code=200;
                        $message='최신 버전이 아닙니다.';
                    }
                    break;
                case 2:
                    if(AOSversion==$header['version'] || AOSversionOld==$header['version']){
                        $code=100;
                        $message='최신 버전입니다.';
                    }
                    else{
                        $code=200;
                        $message='최신 버전이 아닙니다.';
                    }
                    break;
                default:
                    $res->isSuccess = FALSE;
                    $res->code=103;
                    $res->message='잘못된 type 값입니다.';
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
            }
            $res->isSuccess = TRUE;
            $res->code=$code;
            $res->message=$message;
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 1-9
        * API Name : 알림 공지 읽었는지 확인 API
        * 마지막 수정 날짜 : 21.01.30
        */
        case 'getPopupRead':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }
            $pathVar = isValidPathVar($vars['version'], 'version');
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $pathVar);
            getPopupRead($data);
            break;

        /*
        * API No. 1-10
        * API Name : 알림 공지 읽기 API
        * 마지막 수정 날짜 : 21.01.30
        */
        case 'updatePopupRead':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }
            $pathVar = isValidPathVar($vars['version'], 'version');
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $pathVar);
            updatePopupRead($data);
            break;

        /*
        * API No. 1-11
        * API Name : 자신의 이메일인지 확인 API
        * 마지막 수정 날짜 : 22.12.23
        */
        case 'isMyEmail':
            http_response_code(200);

            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }
            $key = array('email');
            $body = isValidBody($req, $key);
            if($body['code'] == 104){
                // 이미 존재하는 메일 존재, isMyEmail을 통해 체크 필요
                $isMyEmail = isMyEmail($req['email'],$header['id']);

                if($isMyEmail){
                    $res->result = TRUE;
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = '성공';
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else{
                    $res->result = FALSE;
                    $res->isSuccess = TRUE;
                    $res->code = 200;
                    $res->message = '자신의 이메일이 아닙니다.';
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

            }
            else if($body['code'] == 103){
                $res->result = FALSE;
                $res->isSuccess = FALSE;
                $res->code = 103;
                $res->message = '잘못된 이메일 형식입니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $res->result = FALSE;
                $res->isSuccess = FALSE;
                $res->code = 103;
                $res->message = '존재하지 않는 메일입니다.';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        /*
        * API No. 1-12
        * API Name : 자체 로그인 API
        * 마지막 수정 날짜 : 19.04.25
        */
        case 'getTerms':
            $terms =
                '
1. 개인정보의 처리 목적
‘TODA’(이하 ‘TODA’) 은(는) 다음의 목적을 위하여 개인정보를 처리하고 있으며, 다음의 목적 이외의 용도로는 이용하지 않습니다.
- 고객 가입의사 확인, 고객에 대한 서비스 제공에 따른 본인 식별.인증, 회원자격 유지.관리 등

2. 개인정보의 처리 및 보유 기간
① TODA 는 정보주체로부터 개인정보를 수집할 때 동의 받은 개인정보 보유․이용기간 또는 법령에 따른 개인정보 보유․이용기간 내에서 개인정보를 처리․보유합니다.
② 구체적인 개인정보 처리 및 보유 기간은 다음과 같습니다.
- 고객 가입 및 관리 : 서비스 이용계약 또는 회원가입 해지시까지

3. 정보주체와 법정대리인의 권리·의무 및 그 행사방법
이용자는 개인정보주체로써 다음과 같은 권리를 행사할 수 있습니다.
정보주체는 TODA에 대해 언제든지 다음 각 호의 개인정보 보호 관련 권리를 행사할 수 있습니다.
1. 개인정보 열람요구
2. 오류 등이 있을 경우 정정 요구
3. 삭제요구
4. 처리정지 요구
제1항, 2항, 3항, 4항에 따른 권리 행사는 이메일 withtoda@gmail.com을 통하여 하실 수 있으며 담당자는 이에 대해 지체 없이 조치하겠습니다.
제1항과 관련하여 서비스를 이용하기 위한 자신의 이메일을 찾거나 제2항과 관련하여 서비스 내 이메일 수정이 필요한 경우 위의 이메일을 통하여 하실 수 있으며 추후 이메일 찾기 및 수정 기능을 개발하여 편리하게 서비스를 이용할 수 있도록 조치하겠습니다.
이용자가 개인정보의 오류 등에 대한 정정 또는 삭제를 요구한 경우에는 회사는 정정 또는 삭제를 완료할 때까지 당해 개인정보를 이용하거나 제공하지 않습니다.
제1항에 따른 권리 행사는 이용자의 법정대리인이나 위임을 받은 자 등 대리인을 통하여 하실 수 있습니다. 이 경우 개인정보 보호법 시행규칙 별지 제11호 서식에 따른 위임장을 제출하셔야 합니다.
이용자는 개인정보 보호법 등 관계법령을 위반하여 회사가 처리하고 있는 이용자 본인이나 타인의 개인정보 및 사생활을 침해하여서는 아니 됩니다.

4. 처리하는 개인정보의 항목 작성
TODA는 다음의 개인정보 항목을 처리하고 있습니다.
필수항목 : 이메일, 비밀번호, 닉네임

5. 개인정보의 파기
TODA은(는) 원칙적으로 개인정보 처리목적이 달성된 경우에는 지체없이 해당 개인정보를 파기합니다. 파기의 절차, 기한 및 방법은 다음과 같습니다.
-파기절차
이용자가 입력한 정보는 목적 달성 후 별도의 DB에 옮겨져 내부 방침 및 기타 관련 법령에 따라 일정기간 저장된 후 파기됩니다. 이 때, DB로 옮겨진 개인정보는 법률에 의한 경우가 아니고서는 다른 목적으로 이용되지 않습니다.
-파기기한
이용자의 개인정보는 개인정보의 보유기간이 경과된 경우에는 보유기간의 종료일로부터 30일 이내에, 개인정보의 처리 목적 달성, 해당 서비스의 폐지, 사업의 종료 등 그 개인정보가 불필요하게 되었을 때에는 개인정보의 처리가 불필요한 것으로 인정되는 날로부터 30일 이내에 그 개인정보를 파기합니다.
-파기방법
본 서비스의 경우 AWS RDS에 데이터를 기록하고 있으며, DB에 저장된 유저들 중 탈퇴 후 30일이 넘은 유저의 이메일과 비밀번호, 그리고 닉네임을 개인을 특정할 수 없는 값으로 변경하는 이벤트 스케줄러를 통해 매일 주기적으로 탈퇴 유저의 데이터를 파기합니다.

6. 개인정보 자동 수집 장치의 설치•운영 및 거부에 관한 사항
TODA는 정보주체의 이용정보를 저장하고 수시로 불러오는 ‘쿠키’를 사용하지 않습니다.

7. 개인정보 보호책임자 작성
① TODA는 개인정보 처리에 관한 업무를 총괄해서 책임지고, 개인정보 처리와 관련한 정보주체의 불만처리 및 피해구제 등을 위하여 아래와 같이 개인정보 보호책임자를 지정하고 있습니다.
▶ 개인정보 보호책임자
성명 :조예은
직위 :대표
연락처 :withtoda@gmail.com
※ 개인정보 보호 담당부서로 연결됩니다.
▶ 개인정보 보호 담당부서
부서명 :관리부
담당자 :최민길
연락처 :withtoda@gmail.com
② 정보주체께서는 TODA의 서비스(또는 사업)을 이용하시면서 발생한 모든 개인정보 보호 관련 문의, 불만처리, 피해구제 등에 관한 사항을 개인정보 보호책임자 및 담당부서로 문의하실 수 있습니다. TODA는 정보주체의 문의에 대해 지체 없이 답변 및 처리해드릴 것입니다.

8. 개인정보 처리방침 변경
이 개인정보처리방침은 시행일로부터 적용되며, 법령 및 방침에 따른 변경내용의 추가, 삭제 및 정정이 있는 경우에는 변경사항의 시행 7일 전부터 공지사항을 통하여 고지할 것입니다.

9. 개인정보의 안전성 확보 조치
TODA는 개인정보보호법 제29조에 따라 다음과 같이 안전성 확보에 필요한 기술적/관리적 및 물리적 조치를 하고 있습니다.
1. 개인정보 취급 직원의 최소화 및 교육
개인정보를 취급하는 직원을 지정하고 담당자에 한정시켜 최소화 하여 개인정보를 관리하는 대책을 시행하고 있습니다.
2. 개인정보에 대한 접근 제한
개인정보를 처리하는 데이터베이스시스템에 대한 접근권한의 부여,변경,말소를 통하여 개인정보에 대한 접근통제를 위하여 필요한 조치를 하고 있으며 침입차단시스템을 이용하여 외부로부터의 무단 접근을 통제하고 있습니다.
                ';
            $res->result = $terms;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = '성공';
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 2
         * API Name : 자체 회원가입 API
         * 마지막 수정 날짜 : 20.09.04
         */
        case 'createUser':
            http_response_code(200);

            $key = array('email', 'password', 'name', 'birth');
            $body = isValidBody($req, $key);

            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            createUser($body);
            break;

        /*
        * API No. 2-1
        * API Name : 간편 회원가입 API
        * 마지막 수정 날짜 : 20.09.04
        */
        case 'postUserInfo':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('name', 'birth');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $pathVar = isValidPathVar($vars['code'], 'code');
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $body, $pathVar);
            postUserInfo($data);
            break;

        /*
        * API No. 8
        * API Name : 앱 잠금 비밀번호 설정 API API
        * 마지막 수정 날짜 : 20.09.10
        */
        case 'postLock':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            $key = array('appPW');
            $body = isValidBody($req, $key);
            if($body['isSuccess'] == false){
                echo json_encode($body, JSON_NUMERIC_CHECK);
                break;
            }

            $data = array_merge($header, $body);
            postLock($data);

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
            break;

        /*
        * API No. 9
        * API Name : 앱 잠금 해제 API
        * 마지막 수정 날짜 : 20.08.21
        */
        case 'deleteLock':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }

            deleteLock($header);

            // redis 키값 : 테섭 본섭에 맞춰서 변경
            $redisKey = DB_NAME.IDToEmail($header['id']);

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
            break;

        /*
        * API No. 38
        * API Name : 공지사항 리스트 조회 API
        * 마지막 수정 날짜 : 20.08.21
        */
        case 'getAnnouncement':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }
            $queryString = isValidQueryString('page');
            if($queryString['isSuccess'] == false){
                echo json_encode($queryString, JSON_NUMERIC_CHECK);
                return;
            }
            $data['page'] = ($queryString['queryString'] - 1)*20;
            $final = array_merge($data,$header);
            getAnnouncement($final);
            break;

        /*
        * API No. 39
        * API Name : 공지사항 상세 조회 API
        * 마지막 수정 날짜 : 20.08.21
        */
        case 'getAnnouncementDetail':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }
            $pathVar = isValidPathVar($vars['announcementID'], 'announcement');
            if($pathVar['isSuccess'] == false){
                echo json_encode($pathVar, JSON_NUMERIC_CHECK);
                break;
            }
            $data = array_merge($header, $pathVar);
            getAnnouncementDetail($data);
            break;

        /*
        * API No. 40
        * API Name : 최신 공지사항 읽었는지 안 읽었는지 확인 API
        * 마지막 수정 날짜 : 21.05.12
        */
        case 'getAnnouncementCheck':
            http_response_code(200);
            $header = isValidHeader('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            if($header['isSuccess'] == false){
                echo json_encode($header, JSON_NUMERIC_CHECK);
                break;
            }
            getAnnouncementCheck($header);
            break;

        case 'clearRedis':
            clearRedis();
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
