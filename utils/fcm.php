<?php

use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

function sendToCacheServer($url,$data){
    $url = CACHE_URL.$url;
    setCurl($url,true,false,$data);
}

function sendToFcmServer($data){
    $tokenIOS = array();
    $tokenAOS = array();

    $tokenSize = sizeof($data['tokenList']['token']);
    $deviceSize = sizeof($data['tokenList']['device']);

    if($tokenSize != $deviceSize) new DefaultResponse(false,404,'token과 device 크기 다름');

    for($i=0;$i<$tokenSize;$i++){
        $token = $data['tokenList']['token'];
        $device = $data['tokenList']['device'];

        $key = $token[$i];
        $value = $device[$i];

        if($value == 100) array_push($tokenIOS,$key);
        else if($value == 200) array_push($tokenAOS,$key);
        else{
            new DefaultResponse(false,404,'실패');
            break;
        }
    }

    switch ($data['type']){
        // 다이어리 초대 전송
        case 1:
            $NotificationArray= array(
                'body' => $data['userName']."님(".$data['userCode'].")이 ".$data['diaryName']."에 초대합니다:)",
                'title' => "To. ".$data['receiveName']."님",
                'sound' => 'default',
                'type' => 'addDiaryFriend'
            );
            $dataArray = array(
                'data' => $data['diaryID']
            );
            break;
        // 다이어리 초대 승낙
        case 2:
            $NotificationArray= array(
                'body' => $data['userName']."님(".$data['userCode'].")이 ".$data['diaryName']."초대에 수락하셨습니다:)",
                'title' => "To. ".$data['receiveName']."님",
                'sound' => 'default',
                'type' => 'addDiaryFriend'
            );
            $dataArray = array(
                'data' => ''
            );
            break;
        // 게시글 작성
        case 3:
            $NotificationArray= array(
                'body' => $data['userName']."님이 일기를 남겼습니다:)",
                'title' => "투다에서 알림이 왔어요!",
                'sound' => 'default',
                'type' => 'addPost'
            );
            $dataArray = array(
                'data' => array(
                    'diaryID' => $data['diaryID'],
                    'postID' => $data['postID']
                )
            );
            break;
        // 좋아요 등록
        case 4:
            $NotificationArray= array(
                'body' => $data['userName']."님이 ".$data['receiveName']."님의 일기를 좋아합니다:)",
                'title' => "To. ".$data['receiveName']."님",
                'sound' => 'default',
                'type' => 'postLike'
            );
            $dataArray = array(
                'data' => array(
                    'diaryID' => $data['diaryID'],
                    'postID' => $data['postID']
                )
            );
            break;
        // 댓글 작성
        case 5:
            $NotificationArray= array(
                'body' => $data['reply'],
                'title' => $data['userName']."님이 댓글을 남겼습니다:)",
                'sound' => 'default',
                'type' => 'postComment'
            );
            $dataArray = array(
                'data' => $data['postID']
            );
            break;
        // 대댓글 작성
        case 6:
            $NotificationArray= array(
                'body' => $data['reply'],
                'title' => $data['userName']."님이 대댓글을 남겼습니다:)",
                'sound' => 'default',
                'type' => 'postComment'
            );
            $dataArray = array(
                'data' => $data['postID']
            );
            break;
        default:
            new DefaultResponse(false,404,'실패');
            return;
    }

    sendFcm($tokenIOS, $NotificationArray,$dataArray,"IOS");
    sendFcm($tokenAOS, $NotificationArray,$dataArray,"Android");

    $NotificationArray = null;
    $dataArray = null;
}

function sendFcm($registration_ids,$notification,$data,$device_type) {
    // Your Firebase Server API Key and URL
    $url = 'https://fcm.googleapis.com/fcm/send';
    $headers = array(FCM_TOKEN,FCM_CONTENT_TYPE);

    if($device_type == "Android"){
        $data['type'] = $notification['type'];
        unset($notification['type']);
        $fields = array(
            'registration_ids' => $registration_ids,
            'notification' => $notification,
            'data' => $data
        );
    } else {
        $fields = array(
            'registration_ids' => $registration_ids,
            'notification' => $notification,
            'data' => $data
        );
    }

    setCurl($url,true,$headers,$fields);
}

function setCurl($url, $isPost, $headers, $fields){
    // Open curl connection
    $ch = curl_init();
    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, $isPost);
    if($headers != false) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
//    echo json_encode($fields);
//    echo $result;
    if ($result === FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }
    curl_close($ch);
}

function sendToAlarmServer($url,$data){
    $url = FCM_ALARM_SERVER.$url; // 서버 주소 여기에 넣기

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
//    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);
    if ($result === FALSE) {
        die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
//    echo json_encode($result);
}

function remindAlarmText(){
//* 리마인드 알림 디폴트 :
//- 오늘 하루를 투다에 기록해보세요 ( ˘ ³˘)♥
//- ((속닥속닥)) 우리 투다에서 비밀얘기해요!
//- 우리만의 이야기로 하루를 채워나가요 :)
//- 수고 많았어요 오늘도 :) 오늘 하루를 투다에 기록해보세요
//
//* 특정 시간에 보내지는 알림 : 일기 쓰기 좋은 시간…오전/오후 00시…☆
//* 밤 : 굿나잇♪♬ 오늘 하루는 어땠나요?
//
//* 일정 기간동안 안들어왔을때 보내는 알림 :
//- 이번주에 어떤 일이 있었나요? 투다한테 알려주세요!
//- 똑똑똑….! 일기 쓸 시간이에요
//- 이번 일기는 작심삼일로 끝나지 않기로 했잖아요ㅠㅠ

    $text1 = '오늘 하루를 투다에 기록해보세요 ( ˘ ³˘)♥';
    $text2 = '((속닥속닥)) 우리 투다에서 비밀얘기해요!';
    $text3 = '우리만의 이야기로 하루를 채워나가요 :)';
    $text4 = '수고 많았어요 오늘도 :) 오늘 하루를 투다에 기록해보세요';

    $text = array();
    array_push($text,$text1);
    array_push($text,$text2);
    array_push($text,$text3);
    array_push($text,$text4);

    $num = mt_rand(0,3);
    return $text[$num];
}