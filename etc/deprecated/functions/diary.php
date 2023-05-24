<?php

// Diary Method
function addDiaryMethod($code,$id,$status,$newData){
    $listKey = getDiaryListKey($code,$id,$status,1);
    $listValue = getRedis($listKey);

    // 다음 페이지로 넘어갈 경우 : 그대로 삽입
    if($listValue==0) setRedis($listKey,json_encode($newData));

    // 기존 페이지일 경우 : 제일 앞에 넣기
    else{
        $listData = json_decode($listValue,true);
        array_unshift($listData,$newData);
        setRedis($listKey,json_encode($listData));
    }

    // 앞에서부터 맨 뒤의 값을 그 뒤 페이지로 전달
    $lastPage = getFinalPageDiary($id,$status);
    for($i=1;$i<$lastPage;$i++){
        $listKeyPrev = getDiaryListKey($code,$id,$status,$i);
        $listValuePrev = getRedis($listKeyPrev);
        $listDataPrev = json_decode($listValuePrev,true);

        $listKeyCurr = getDiaryListKey($code,$id,$status,$i+1);
        $listValueCurr = getRedis($listKeyCurr);
        $listDataCurr = json_decode($listValueCurr,true);

        // 뒷 페이지 부분은 따로 실행시켜 읽기
        if($listDataCurr == 0){
            $data = Array(
                'id'=>$id,
                'status'=>$status,
                'keyPage'=>$i+1,
                'page'=>$i*20
            );
            $listDataCurr = getDiaries($data);
        }

        if($listDataCurr == 0){
            $element = array_pop($listDataPrev);
            setRedis($listDataCurr,json_encode($element));
        }
        else if(sizeof($listDataPrev)>20){
            $element = array_pop($listDataPrev);
            array_unshift($listDataCurr,$element);

            setRedis($listKeyPrev,json_encode($listDataPrev));
            setRedis($listKeyCurr,json_encode($listDataCurr));
        }
    }
}

function updateDiaryMethod($code,$id,$status,$lastPage,$codeKey,$codeID,$newData){
    $isUpdated = false;

    // 업데이트할 부분 찾기
    for($i=1;$i<=$lastPage;$i++){
        $listKey = getDiaryListKey($code,$id,$status,$i);
        $listValue = getRedis($listKey);
        $listData = json_decode($listValue,true);

        // 뒷 페이지 부분은 따로 실행시켜 읽기
        if($listData == 0){
            $data = Array(
                'id'=>$id,
                'status'=>$status,
                'keyPage'=>$i+1,
                'page'=>($i-1)*20
            );
            $listData = getDiaries($data);
        }

        // 업데이트할 부분 찾았다면, 데이터 치환
        $size = sizeof($listData);
        for($j=0;$j<$size;$j++){
            if($listData[$j][$codeKey] == $codeID) {
                $listData[$j] = $newData;
                $isUpdated = true;
                break;
            }
        }

        // 변경사항 저장 후 종료
        if($isUpdated) {
            setRedis($listKey,json_encode($listData));
            break;
        }
    }
}

function deleteDiaryMethod($code,$id,$status,$lastPage,$codeKey,$codeID){
    $isDeleted = false;
    $index = null;

    // 삭제할 부분 찾기
    for($i=1;$i<=$lastPage;$i++){
        $listKey = getDiaryListKey($code,$id,$status,$i);
        $listValue = getRedis($listKey);
        $listData = json_decode($listValue,true);

        // 빈 페이지이면 스킵
        if($listData == 0) continue;

        // 삭제할 부분 찾았다면, 데이터 삭제 후 인덱스 값 반환
        $size = sizeof($listData);
        for($j=0;$j<$size;$j++){
            if($listData[$j][$codeKey] == $codeID) {
                array_splice($listData,$j,1);
                $isDeleted = true;
                $index = $i;
                break;
            }
        }

        // 변경사항 저장 후 종료
        if($isDeleted) {
            setRedis($listKey,json_encode($listData));
            break;
        }
    }

    // 빈 부분을 다음 배열에서 가져와 채우기
    for($i=$index;$i<$lastPage;$i++){
        $listKeyPrev = getDiaryListKey($code,$id,$status,$i);
        $listValuePrev = getRedis($listKeyPrev);
        $listDataPrev = json_decode($listValuePrev,true);

        $listKeyCurr = getDiaryListKey($code,$id,$status,$i+1);
        $listValueCurr = getRedis($listKeyCurr);
        $listDataCurr = json_decode($listValueCurr,true);

        // 뒷 페이지 부분은 따로 실행시켜 읽기
        if($listDataCurr == 0){
            $data = Array(
                'id'=>$id,
                'status'=>$status,
                'keyPage'=>$i+1,
                'page'=>$i*20
            );
            $listDataCurr = getDiaries($data);
        }

        $element = array_splice($listDataCurr,0,1);
        array_push($listDataPrev,$element[0]);

        setRedis($listKeyPrev,json_encode($listDataPrev));
        setRedis($listKeyCurr,json_encode($listDataCurr));
    }
}