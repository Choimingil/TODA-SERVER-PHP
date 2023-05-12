<?php

// Post Method
function addMethod($code,$id,$newData){
    $listKey = getListKey($code,$id,1);
    $listValue = getRedis($listKey);

    // 아무 게시글이 없는 경우 : 그대로 삽입
    if($listValue==0){
        setRedis($listKey,json_encode($newData));
    }

    // 기존 페이지일 경우 :
    else{
        // 시간을 초기화시키기
        $dateKey = getDateKey($code,$id);
        $now = new DateTime();
        $before = getRedis($dateKey);
        $dateDiff = $now->getTimestamp() - $before;
        $newData['date'] = $newData['date'] - $dateDiff;

        // 해당 날짜값 위치에서 게시글 추가하기
        $isOverStacked = false;
        $isUpdated = false;
        $lastPage = getFinalPagePost($id);
        $targetDate = getDateCode($newData['dateFull']);

        for($i=$lastPage;$i>=1;$i--){
            $listKey = getListKey($code,$id,$i);
            $listValue = getRedis($listKey);
            $listData = json_decode($listValue,true);

            // 뒷 페이지 부분은 따로 실행시켜 읽기
            if($listData == 0){
                $data = Array(
                    'id'=>diaryToUser($id),
                    'pathVar'=>$id,
                    'page'=>($i-1)*20,
                    'addMethod'=>true
                );
                $listData = getPostListRaw($data);
            }
            $size = sizeof($listData);

            for($j=$size-1;$j>=0;$j--){
                $sampleDate = getDateCode($listData[$j]['dateFull']);

                if($sampleDate > $targetDate){
                    $index = $j+1;
                    $arrFront = array_slice($listData,0,$index);
                    array_push($arrFront,$newData);
                    $arrEnd = array_slice($listData,$index);

                    $arrData = array_merge($arrFront,$arrEnd);
                    setRedis($listKey,json_encode($arrData));

                    if(sizeof($arrData)>20) $isOverStacked=true;
                    $isUpdated = true;
                    break;
                }
            }
            if($isUpdated) break;
        }

        if($isUpdated == false){
            $listData = json_decode($listValue,true);

            // 값이 비었을 때 채워주기
            if($listData == 0){
                $data = Array(
                    'id'=>diaryToUser($id),
                    'pathVar'=>$id,
                    'page'=>($i-1)*20,
                    'addMethod'=>true
                );
                $listData = getPostListRaw($data);
            }

            array_unshift($listData,$newData);
            setRedis($listKey,json_encode($listData));
            if(sizeof($listData)>20) $isOverStacked=true;
        }

        if($isOverStacked){
            for($i=1;$i<$lastPage;$i++){
                $isNewAdded = false;
                $listKeyPrev = getListKey($code,$id,$i);
                $listValuePrev = getRedis($listKeyPrev);
                $listDataPrev = json_decode($listValuePrev,true);

                $listKeyCurr = getListKey($code,$id,$i+1);
                $listValueCurr = getRedis($listKeyCurr);
                $listDataCurr = json_decode($listValueCurr,true);

                // 뒷 페이지 부분은 따로 실행시켜 읽기
                if($listDataCurr == 0){
                    $data = Array(
                        'id'=>diaryToUser($id),
                        'pathVar'=>$id,
                        'page'=>$i*20,
                        'addMethod'=>true
                    );
                    $listDataCurr = getPostListRaw($data);
                    setRedis($listKeyCurr,json_encode($listDataCurr));
                    $isNewAdded = true;
                }

                if($listDataCurr == 0){
                    $element = array_pop($listDataPrev);
                    setRedis($listDataCurr,json_encode($element));
                }
                else if(sizeof($listDataPrev)>20){
                    $element = array_pop($listDataPrev);
                    if(!$isNewAdded or sizeof($listDataCurr)!=1) array_unshift($listDataCurr,$element);

                    setRedis($listKeyPrev,json_encode($listDataPrev));
                    setRedis($listKeyCurr,json_encode($listDataCurr));
                }
            }
        }
    }
}

function updateMethod($code,$id,$lastPage,$codeKey,$codeID,$newData){
    $isUpdated = false;

    // 업데이트할 부분 찾기
    for($i=1;$i<=$lastPage;$i++){
        $listKey = getListKey($code,$id,$i);
        $listValue = getRedis($listKey);
        $listData = json_decode($listValue,true);

        // 뒷 페이지 부분은 따로 실행시켜 읽기
        if($listData == 0){
            $data = Array(
                'id'=>diaryToUser($id),
                'pathVar'=>$id,
                'page'=>($i-1)*20
            );
            $listData = getPostList($data);
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

function deleteMethod($code,$id,$lastPage,$codeKey,$codeID){
    $isDeleted = false;
    $index = null;

    // 삭제할 부분 찾기
    for($i=1;$i<=$lastPage;$i++){
        $listKey = getListKey($code,$id,$i);
        $listValue = getRedis($listKey);
        $listData = json_decode($listValue,true);

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
        $listKeyPrev = getListKey($code,$id,$i);
        $listValuePrev = getRedis($listKeyPrev);
        $listDataPrev = json_decode($listValuePrev,true);

        $listKeyCurr = getListKey($code,$id,$i+1);
        $listValueCurr = getRedis($listKeyCurr);
        $listDataCurr = json_decode($listValueCurr,true);

        // 뒷 페이지 부분은 따로 실행시켜 읽기
        if($listDataCurr == 0){
            $data = Array(
                'id'=>diaryToUser($id),
                'pathVar'=>$id,
                'page'=>$i*20
            );
            $listDataCurr = getPostListRaw($data);
        }

        $element = array_splice($listDataCurr,0,1);
        array_push($listDataPrev,$element[0]);

        setRedis($listKeyPrev,json_encode($listDataPrev));
        setRedis($listKeyCurr,json_encode($listDataCurr));
    }
}

function updateHeartMethod($id,$newData){
    $listKey = getListKey('post_detail',$id,0);
    setRedis($listKey,json_encode($newData));
}