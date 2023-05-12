<?php
use \Monolog\Logger as Logger;

// 기본 세팅
requiresSetting();
initSetting();

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
setLoggerChannel($accessLogs,$errorLogs);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    apis($r);
});

// Controller 연결
$routeInfo = getRouteInfo($dispatcher);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND: // ... 404 Not Found
        echo json_encode((object)Array(
            'isSuccess'=>false,
            'code'=>404,
            'message'=>"No URL"
        ));
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED: // ... 405 Method Not Allowed
        $allowedMethods = $routeInfo[1];
        echo json_encode((object)Array(
            'isSuccess'=>false,
            'code'=>405,
            'message'=>"Wrong Method"
        ));
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'LoginController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/LoginController.php';
                break;
            case 'UserController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/UserController.php';
                break;
            case 'NotificationController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/NotificationController.php';
                break;
            case 'DiaryController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/DiaryController.php';
                break;
            case 'PostController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/PostController.php';
                break;
            case 'LikeCommentController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/LikeCommentController.php';
                break;
            case 'StickerController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/StickerController.php';
                break;
            case 'NoticeController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/NoticeController.php';
                break;
        }
        break;
}

function requiresSetting(){
    require './vendor/autoload.php';
    require './urls.php';

    require './utils/system.php';
    require './utils/jwt.php';
    require './utils/fcm.php';
    require './utils/mail.php';
    require './env.php';
    require './utils/redis.php';
    require './utils/db.php';

    require './functions/convert.php';
    require './functions/system.php';
    require './functions/user.php';
    require './functions/diary.php';
    require './functions/post.php';
    require './functions/comment.php';
    require './functions/sticker.php';

    require './req/ValidBody.php';
    require './req/ValidHeader.php';
    require './req/ValidPathVar.php';
    require './req/ValidQS.php';
}