<?php

function sendRemindAlarm($body){
    $NotificationArray= array(
        // 'body' => remindAlarmText(),
        'title' => 'TODA',
        'sound' => 'default',
        'type' => 'getRemindAlarm'
    );
    $dataArray = array('data' => 'getRemindAlarm');
    $tokenArray = array($body['token']);
    if($body['device']==100) $deviceType = "IOS";
    else if($body['device']==200) $deviceType = "AOS";
    else $deviceType = "NONE";
    // sendFcm($tokenArray,$NotificationArray,$dataArray,$deviceType);
}

function sendEventAlarm(){
    $query = 'select distinct token from Notification where isEventAllowed=\'Y\' and status not like 0;';
    $res = execute($query,[]);

    $NotificationArray= array(
        'body' =>  "안녕하세요 투다입니다. 현재 버그로 인해 이미지가 올라가지 않는 현상이 발생했습니다. 최대한 빨리 오류를 해결하겠습니다. 불편을 끼쳐 드려 정말 죄송합니다ㅠㅠㅠ",
        'title' => 'TODA',
        'sound' => 'default',
        'type' => 'getEventAlarm'
    );
    $dataArray = array('data' => 'getEventAlarm');

    $sizePage = sizeof($res)/1000;
    for($j=0;$j<=(int)$sizePage;$j++){
        $tokenArray = Array();
        for($i=1000*$j;$i<1000*($j+1);$i++){
            if(empty($res[$i])) break;
            array_push($tokenArray,$res[$i]['token']);
        }
        // sendFcm($tokenArray,$NotificationArray,$dataArray,"event");
    }

}