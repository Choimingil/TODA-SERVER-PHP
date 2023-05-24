<?php

// Comment Method
function addCommentMethod($code,$id,$lastPage,$newData,$isReComment){
    // 댓글 달기
    if($isReComment == 0){
        for($i=1;$i<=$lastPage;$i++){
            $listKey = getListKey($code,$id,$i);
            $listValue = getRedis($listKey);
            $listData = json_decode($listValue,true);

            if($i==$lastPage){
                // 시간을 초기화시키기
                $dateKey = getDateKey($code,$id);
                $now = new DateTime();
                $before = getRedis($dateKey);
                $dateDiff = $now->getTimestamp() - $before;
                $newData['Comment'][0]['time'] = (int)$newData['Comment'][0]['time'] - $dateDiff;

                // 새롭게 추가된 데이터 추가
                if($listData == 0){
                    $listData = Array(
                        'totalCommentNum' => 0,
                        'Comment' => Array()
                    );
                }
                array_push($listData['Comment'],$newData['Comment'][0]);
            }

            // 모든 페이지에서 총 댓글 개수 최신화
            $listData['totalCommentNum']++;

            // 데이터 추가
            setRedis($listKey,json_encode($listData));
        }
    }

    // 대댓글 달기
    else{
        $isUpdate = false;
        for($i=1;$i<=$lastPage;$i++){
            $listKey = getListKey($code,$id,$i);
            $listValue = getRedis($listKey);
            $listData = json_decode($listValue,true);

            if($listData==0){
                $data = Array(
                    'id'=>getPostToUser($id),
                    'pathVar'=>$id,
                    'page'=>($i-1)*20
                );
                getComment($data);

                $listKey = getListKey($code,$id,$i);
                $listValue = getRedis($listKey);
                $listData = json_decode($listValue,true);
            }

            $size = sizeof($listData['Comment']);
            $commentID = $isReComment;
            for($j=0;$j<$size;$j++){
                if($listData['Comment'][$j]['commentID'] == $commentID){

                    // 시간을 초기화시키기
                    $dateKey = getDateKey($code,$id);
                    $now = new DateTime();
                    $before = getRedis($dateKey);
                    $dateDiff = $now->getTimestamp() - $before;

                    $reCommentSize = sizeof($newData['Comment'][0]['reComment']);
                    if($reCommentSize>0)
                        $newData['Comment'][0]['reComment'][$reCommentSize-1]['time'] =
                            (int)$newData['Comment'][0]['reComment'][$reCommentSize-1]['time'] - $dateDiff;

                    // 시간값 초기화된 데이터 치환
                    $listData['Comment'][$j] = $newData['Comment'][0];
                    $isUpdate = true;

                    break;
                }
            }

            // 모든 페이지에서 총 댓글 개수 최신화
            $listData['totalCommentNum'] = (int)$newData['totalCommentNum'];

            // 데이터 추가
            setRedis($listKey,json_encode($listData));

            if($isUpdate) break;
        }
    }
}

function updateCommentMethod($code,$id,$lastPage,$commentID,$newData){
    $isUpdated = false;

    // 업데이트할 부분 찾기(댓글)
    for($i=1;$i<=$lastPage;$i++){
        $listKey = getListKey($code,$id,$i);
        $listValue = getRedis($listKey);
        $listData = json_decode($listValue,true);

        // 뒷 페이지 부분은 따로 실행시켜 읽기
        if($listData == 0){
            $data = Array(
                'id'=>diaryToUser(postToDiary($id)),
                'pathVar'=>$id,
                'page'=>($i-1)*20
            );
            $listData = getComment($data);
        }

        // 업데이트할 부분 찾았다면, 데이터 치환
        $size = sizeof($listData['Comment']);
        for($j=0;$j<$size;$j++){
            if($listData['Comment'][$j]['commentID'] == $commentID){
                $listData['Comment'][$j] = $newData['Comment'][0];
                $isUpdated = true;
                break;
            }
            else{
                // 대댓글 업데이트 부분;
                $reCommentSize = sizeof($listData['Comment'][$j]['reComment']);
                for($k=0;$k<$reCommentSize;$k++){
                    if($listData['Comment'][$j]['reComment'][$k]['commentID'] == $commentID){
                        $listData['Comment'][$j] = $newData['Comment'][0];
                        $isUpdated = true;
                        break;
                    }
                }
                if($isUpdated) break;
            }
        }

        // 변경사항 저장 후 종료
        if($isUpdated) {
            setRedis($listKey,json_encode($listData));
            break;
        }
    }
}

function deleteCommentMethod($code,$id,$lastPage,$commentID){
    $isDeleted = false;
    $isReComment = false;
    $index = null;

    // 삭제할 부분 찾기(댓글)
    for($i=1;$i<=$lastPage;$i++){
        $listKey = getListKey($code,$id,$i);
        $listValue = getRedis($listKey);
        $listData = json_decode($listValue,true);

        // 삭제할 부분 찾았다면, 데이터 삭제 후 인덱스 값 반환
        $size = sizeof($listData['Comment']);
        for($j=0;$j<$size;$j++){
            if($listData['Comment'][$j]['commentID'] == $commentID){
                array_splice($listData['Comment'],$j,1);
                $isDeleted = true;
                $index = $i;
                break;
            }
            else{
                // 대댓글 삭제 부분;
                $reCommentSize = sizeof($listData['Comment'][$j]['reComment']);
                for($k=0;$k<$reCommentSize;$k++){
                    if($listData['Comment'][$j]['reComment'][$k]['commentID'] == $commentID){
                        array_splice($listData['Comment'][$j]['reComment'],$k,1);
                        $isDeleted = true;
                        $isReComment = true;
                        break;
                    }
                }
                if($isDeleted) break;
            }
        }

        // 변경사항 저장 후 종료
        if($isDeleted) {
            $listData['totalCommentNum']--;
            setRedis($listKey,json_encode($listData));
            break;
        }
    }

    // 빈 부분을 다음 배열에서 가져와 채우기(댓글)
    if(!$isReComment){
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
                    'id'=>diaryToUser(postToDiary($id)),
                    'pathVar'=>$id,
                    'page'=>$i*20
                );
                $listDataCurr = getComment($data);
            }

            $element = array_splice($listDataCurr['Comment'],0,1);
            array_push($listDataPrev['Comment'],$element[0]);

            setRedis($listKeyPrev,json_encode($listDataPrev));
            setRedis($listKeyCurr,json_encode($listDataCurr));
        }
    }
}