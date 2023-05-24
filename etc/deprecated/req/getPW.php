<?php

function getPW($code, $key){
    try{
        if(!isset($_SERVER[$code])) return new DefaultResponse(FALSE,102,'헤더값이 인식되지 않습니다.');
        if(isUpdating()=='Y') return new DefaultResponse(FALSE,103,'현재 업데이트 중입니다.');
        $data = getDataByJWToken($_SERVER[$code], $key);

        if(isset($data->id) && isset($data->pw) && isset($data->appPW)){
            if(isValidUserStatus($data->id, $data->pw, $data->appPW)) return $data->pw;
            else return new DefaultResponse(FALSE,103,'잘못된 헤더값입니다.');
        }
        else if(isset($data->kakao)){
            if(isValidKakaoStatus($data->kakao, $data->appPW)) return $data->kakao;
            else return new DefaultResponse(FALSE,103,'잘못된 헤더값입니다.');
        }
        else return new DefaultResponse(FALSE,103,'토큰 값이 잘못되었습니다.');
    }
    catch(\Exception $e){
        return new DefaultResponse(FALSE,105,'알 수 없는 오류');
    }
}