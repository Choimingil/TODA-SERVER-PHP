<?php

function addStickerVer2($userID,$postID,$body,$stickerArray,$stickerDataArray){
    $query = 'UPDATE PostSticker SET status = 0 WHERE postID = ? and userID = ?;';
    execute($query,[$postID,$userID]);
    
    if(!empty($stickerDataArray)){
        // 스티커 1차 저장
        $stickerQuery = implode(',',$stickerArray);
        $query = 'INSERT INTO PostSticker (userID, postID, stickerID, device, x, y, status) VALUES'.$stickerQuery.';';
        $usedStickerID = lastInsertID($query,$stickerDataArray);

        // 2차 정보 배열에 넣기
        $stickerRotateArray = Array(); // 쿼리 추가용 배열
        $stickerScaleArray = Array(); // 쿼리 추가용 배열
        $stickerDataArray = Array(); // 데이터 추가용 배열
        $usedStickerIDArray = Array(); // 추가된 스티커 아이디 배열

        foreach ($body['stickerArr'] as $i=>$value){
            array_push($stickerRotateArray,'(?,?,?,?,?,?,?)');
            array_push($usedStickerIDArray,$usedStickerID+$i);
            array_push($stickerDataArray,$usedStickerID+$i);
            array_push($stickerDataArray,$value['rotate']['a']);
            array_push($stickerDataArray,$value['rotate']['b']);
            array_push($stickerDataArray,$value['rotate']['c']);
            array_push($stickerDataArray,$value['rotate']['d']);
            array_push($stickerDataArray,$value['rotate']['tx']);
            array_push($stickerDataArray,$value['rotate']['ty']);
        }

        foreach ($body['stickerArr'] as $i=>$value){
            array_push($stickerScaleArray,'(?,?,?,?,?)');
            array_push($stickerDataArray,$usedStickerID+$i);
            array_push($stickerDataArray,$value['scale']['x']);
            array_push($stickerDataArray,$value['scale']['y']);
            array_push($stickerDataArray,$value['scale']['width']);
            array_push($stickerDataArray,$value['scale']['height']);
        }

        $stickerRotateQuery = implode(',',$stickerRotateArray);
        $stickerScaleQuery = implode(',',$stickerScaleArray);

        $query =
            "INSERT INTO PostStickerRotate (usedStickerID, a, b, c, d, tx, ty) VALUES ".$stickerRotateQuery.";".
            "INSERT INTO PostStickerScale (usedStickerID, x, y, width, height) VALUES ".$stickerScaleQuery.";";
        execute($query,$stickerDataArray);
    }
}