<?php

// Sticker Method
function addStickerMethod($code,$id,$newData){
    $listKey = getListKey($code,$id,1);
    $listValue = getRedis($listKey);

    // 아무 스티커 없는 경우
    if($listValue==0){
        $newSize = sizeof($newData);

        // 사이즈가 20보다 작으면 그대로 삽입
        if($newSize<=20) setRedis($listKey,json_encode($newData));

        // 사이즈가 20보다 크다면 잘라서 삽입
        else{
            $listData = json_decode($listValue,true);
            $firstStickerData = array_slice($newData,0,20);
            foreach ($firstStickerData as $i=>$result) array_push($listData,$firstStickerData[$i]);
            setRedis($listKey,json_encode($listData));
            $remainStickerData = array_slice($newData,20);
            $lastPage = 1;

            while(true){
                $lastPage++;
                $listKey = getListKey($code,$id,$lastPage);

                $newData = $remainStickerData;
                $tmpDataSize = sizeof($newData);

                if($tmpDataSize<20){
                    setRedis($listKey,json_encode($newData));
                    break;
                }
                else{
                    $remainStickerData = array_slice($newData,0,20);
                    setRedis($listKey,json_encode($remainStickerData));
                    $remainStickerData = array_slice($newData,20);
                }
            }
        }
    }

    // 기존 페이지일 경우 : 제일 뒤에 넣기
    else{
        $lastPage = getFinalPageSticker($id);
        $listKey = getListKey($code,$id,$lastPage);
        $listValue = getRedis($listKey);
        $listData = json_decode($listValue,true);

        // 마지막 페이지 사이즈가 꽉 찼을 경우 : 다음 페이지에 삽입
        $size = sizeof($listData);
        if($size>=20){
            $lastPage++;
            $listKey = getListKey($code,$id,$lastPage);
            $listValue = getRedis($listKey);
            $listData = json_decode($listValue,true);
            $size = 0;
        }

        $newSize = sizeof($newData);
        $totalSize = $size + $newSize;

        // 추가한 스티커가 마지막 페이지를 넘지 않을 경우 : 그대로 추가
        if($totalSize<20){
            foreach ($newData as $i=>$result) array_push($listData,$newData[$i]);
            setRedis($listKey,json_encode($listData));
        }
        // 추가한 스티커가 마지막 페이지를 넘을 경우 : 스티커 찢어서 계속 추가
        else{
            $firstStickerData = array_slice($newData,0,20 - $size);
            foreach ($firstStickerData as $i=>$result) array_push($listData,$firstStickerData[$i]);
            setRedis($listKey,json_encode($listData));
            $remainStickerData = array_slice($newData,20 - $size);

            while(true){
                $lastPage++;
                $listKey = getListKey($code,$id,$lastPage);

                $newData = $remainStickerData;
                $tmpDataSize = sizeof($newData);

                if($tmpDataSize<20){
                    setRedis($listKey,json_encode($newData));
                    break;
                }
                else{
                    $remainStickerData = array_slice($newData,0,20);
                    setRedis($listKey,json_encode($remainStickerData));
                    $remainStickerData = array_slice($newData,20);
                }
            }
        }
    }
}