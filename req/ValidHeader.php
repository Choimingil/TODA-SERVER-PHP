<?php

function isValidHeader($code, $key){
    try{
        if(!isset($_SERVER[$code])){
            $res['isSuccess'] = false;
            $res['code'] = 102;
            $res['message'] = '헤더값이 인식되지 않습니다.';
            return $res;
        }

        // redis에 업데이트 여부 넣기
        if(isUpdating()=='Y'){
            $res['isSuccess'] = false;
            $res['code'] = 103;
            $res['message'] = '현재 업데이트 중입니다.';
            return $res;
        }
        $data = getDataByJWToken($_SERVER[$code], $key);

        if(isset($data->id) && isset($data->pw) && isset($data->appPW)){
            // redis 키값 : 테섭 본섭에 맞춰서 변경
            $key = DB_NAME.$data->id;

            // redis에 토큰 존재하지 않는다면 쿼리 실행 후 유저 데이터 redis에 저장
            if(getRedis($key) == 0){
                if(isValidUserStatus($data->id, $data->pw, $data->appPW)){
                    $id = emailToID($data->id);
                    $pw = $data->pw;
                    $appPW = $data->appPW;

                    // 구한 정보 redis에 저장
                    $dataArray = Array(
                        'email' => $data->id,
                        'id' => (int)$id,
                        'pw' => $pw,
                        'appPW' => $appPW
                    );
                    setRedis($key,json_encode($dataArray));

                    $res['id'] = $id;
                    $res['pw'] = $pw;
                    $res['appPW'] = $appPW;
                    $res['isSuccess'] = TRUE;
                    $res['code'] = 100;
                    $res['message'] = '자체 로그인 헤더 성공';
                    return $res;
                }
                else{
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 헤더값입니다.';
                    return $res;
                }
            }
            else {
                // redis에 유저 데이터 존재한다면 통과
                $userRedis = json_decode(getRedis($key),true); // 여기서 id = 이메일

                if($userRedis['email'] == $data->id){
                    $res['id'] = $userRedis['id'];
                    $res['pw'] = $userRedis['pw'];
                    $res['appPW'] = $userRedis['appPW'];
                    $res['isSuccess'] = TRUE;
                    $res['code'] = 100;
                    $res['message'] = '자체 로그인 헤더 성공';
                    return $res;
                }
                else{
                    $res['isSuccess'] = FALSE;
                    $res['code'] = 103;
                    $res['message'] = '잘못된 헤더값입니다.';
                    return $res;
                }
            }
        }
        else if(isset($data->kakao)){
            if(isValidKakaoStatus($data->kakao, $data->appPW)){
                $res['id'] = kakaoToID($data->kakao);
                $res['pw'] = $data->pw;
                $res['appPW'] = $data->appPW;
                $res['isSuccess'] = TRUE;
                $res['code'] = 100;
                $res['message'] = '카카오 로그인 헤더 성공';
                return $res;
            }
            else{
                $res['isSuccess'] = FALSE;
                $res['code'] = 103;
                $res['message'] = '잘못된 헤더값입니다.';
                return $res;
            }
        }
        else{
            $res['isSuccess'] = false;
            $res['code'] = 103;
            $res['message'] = '토큰 값이 잘못되었습니다.';
            return $res;
        }
    }
    catch(\Exception $e){
        $res['isSuccess'] = false;
        $res['code'] = 105;
        $res['message'] = '알 수 없는 오류';
        return $res;
    }
}