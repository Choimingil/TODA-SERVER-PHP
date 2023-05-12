<?php
require './pdos/post.php';

header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API URL. POST /post/ver4
        * API Name : 게시글 작성 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'addPostVer4':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
//            $body = getBody($req, array('userName','diary','postID','date','title','text','mood','background','aligned','font','imageList','userList','tokenList','deviceList'));
            $body = getBody($req, array('userName','diary','postID','date','title','text','mood','background','aligned','font','imageList','userList'));
            $body['status'] = $body['background']*100 + $body['mood'];
            $body['statusText'] = $body['aligned']*100 + $body['font'];

            if(!isDiaryUser($userID, $body['diary']))
                new DefaultResponse(false,103,'다이어리에 등록된 유저가 아닙니다.');
            else{
                $res = addPostVer4($userID,$body);
                new ResultResponse($res,true,100,'게시물 작성이 완료되었습니다.');
            }
            break;

        /*
        * API URL. DELETE /post/{postID:\d+}
        * API Name : 게시글 삭제 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'deletePostVer2':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $postID = getPathVar($vars,'postID');
            if(!isPostUser($userID,$postID))
                new DefaultResponse(FALSE,102,'자신이 작성한 게시물이 아닙니다.');
            else{
                deletePost($userID,$postID);
                new DefaultResponse(true,100,'게시물 삭제가 완료되었습니다.');
            }
            break;

        /*
        * API URL. PATCH /post/ver3
        * API Name : 게시글 수정 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'updatePostVer4':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $body = getBody($req, array('post','date','title','text','mood','background','aligned','font','imageList'));
            $body['status'] = $body['background']*100 + $body['mood'];
            $body['statusText'] = $body['aligned']*100 + $body['font'];

            if(!isPostUser($userID, $body['post']))
                new DefaultResponse(false,103,'자신이 작성한 게시물이 아닙니다.');
            else{
                updatePostVer3($userID,$body);
                new DefaultResponse(true,100,'게시물 수정이 완료되었습니다.');
            }
            break;

        /*
        * API URL. POST /like/ver2
        * API Name : 좋아요 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'postLikeVer2':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
//            $body = getBody($req, array('post','userName','mood','tokenList'));
            $body = getBody($req, array('post','userName','mood'));

            if(!isValidPostDiary($userID, $body['post']))
                new DefaultResponse(false,103,'게시물을 볼 수 있는 권한이 없습니다.');
            else{
                if(isExistLike($userID, $body['post'])){
                    if(isSameMood($userID, $body['post'], $body['mood'])){
                        deleteLike($userID,$body);
                        new DefaultResponse(true,200,'좋아요가 취소되었습니다.');
                    }
                    else{
                        repostLike($userID,$body);
                        new DefaultResponse(true,100,'좋아요가 재등록되었습니다.');
                    }
                }
                else{
                    postLikeVer2($userID,$body);
                    new DefaultResponse(true,100,'좋아요가 등록되었습니다.');
                }
            }
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
