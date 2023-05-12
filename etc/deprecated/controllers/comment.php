<?php
require './pdos/comment.php';

header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"), true);

try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
        * API URL. POST /comment/ver2
        * API Name :댓글 작성 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'postCommentVer2':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
//            $body = getBody($req, array('commentID','post','userName','reply','userList','tokenList','deviceList'));
            $body = getBody($req, array('commentID','post','userName','reply','userList'));

            if(!isValidPostDiary($userID, $body['post']))
                new DefaultResponse(false,103,'게시물을 볼 수 있는 권한이 없습니다.');
            else{
                if(isset($_GET['comment'])){
                    $commentID = getQS('comment');
                    if(!isValidCommentPostDiary($userID,$body['post'],$commentID))
                        new DefaultResponse(false,103,'대댓글을 달 권한이 없습니다.');
                    else{
                        $res = postReCommentVer2($userID,$body,$commentID);
                        new ResultResponse($res,true,100,'대댓글이 성공적으로 작성되었습니다.');
                    }
                }
                else{
                    $res = postCommentVer2($userID,$body);
                    new ResultResponse($res,true,100,'댓글이 성공적으로 작성되었습니다.');
                }
            }
            break;

        /*
        * API URL. DELETE /comment/{commentID:\d+}
        * API Name : 댓글 삭제 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'deleteComment':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $commentID = getPathVar($vars,'commentID');

            if(!isCommentUser($userID,$commentID))
                new DefaultResponse(FALSE,103,'자신이 작성한 댓글이 아닙니다.');
            else{
                deleteComment($userID,$commentID);
                new DefaultResponse(true,100,'댓글이 삭제되었습니다');
            }
            break;

        /*
        * API URL. PATCH /comment
        * API Name : 댓글 수정 API
        * 마지막 수정 날짜 : 21.11.30
        */
        case 'updateComment':
            http_response_code(200);
            $userID = getUserID('HTTP_X_ACCESS_TOKEN', JWT_SECRET_KEY);
            $body = getBody($req, array('comment','reply'));

            if(!isCommentUser($userID,$body['comment']))
                new DefaultResponse(FALSE,103,'자신이 작성한 댓글이 아닙니다.');
            else{
                updateComment($userID,$body);
                new DefaultResponse(true,100,'댓글이 수정되었습니다');
            }
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
