<?php
use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;
use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

function setLoggerChannel($accessLogs,$errorLogs){
    // log/your.log 파일에 로그 생성. 로그 레벨은 Info
    $accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
    $errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
    // add records to the log
    //$log->addInfo('Info log');
    // Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
    //$log->addDebug('Debug log');
    //$log->addError('Error log');
}

function initSetting(){
    date_default_timezone_set('Asia/Seoul');
//    ini_set('default_charset', 'utf8mb4');

    //에러출력하게 하는 코드
    error_reporting(E_ALL); ini_set("display_errors", 1);
}

function getRouteInfo($dispatcher){
    // Fetch method and URI from somewhere
    $httpMethod = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];

    // Strip query string (?foo=bar) and decode URI
    if (false !== $pos = strpos($uri, '?')) {
        $uri = substr($uri, 0, $pos);
    }
    $uri = rawurldecode($uri);

    return $dispatcher->dispatch($httpMethod, $uri);
}

function addAccessLogs($accessLogs, $body)
{
    if (isset($_SERVER['HTTP_X_ACCESS_TOKEN']))
        $logData["JWT"] = getDataByJWToken($_SERVER['HTTP_X_ACCESS_TOKEN'], JWT_SECRET_KEY);
    $logData["GET"] = $_GET;
    $logData["BODY"] = $body;
    $logData["REQUEST_METHOD"] = $_SERVER["REQUEST_METHOD"];
    $logData["REQUEST_URI"] = $_SERVER["REQUEST_URI"];
//    $logData["SERVER_SOFTWARE"] = $_SERVER["SERVER_SOFTWARE"];
    $logData["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
    $logData["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];
    $accessLogs->addInfo(json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

}

function addErrorLogs($errorLogs, $res, $body)
{
    if (isset($_SERVER['HTTP_X_ACCESS_TOKEN']))
        $req["JWT"] = getDataByJWToken($_SERVER['HTTP_X_ACCESS_TOKEN'], JWT_SECRET_KEY);
    $req["GET"] = $_GET;
    $req["BODY"] = $body;
    $req["REQUEST_METHOD"] = $_SERVER["REQUEST_METHOD"];
    $req["REQUEST_URI"] = $_SERVER["REQUEST_URI"];
//    $req["SERVER_SOFTWARE"] = $_SERVER["SERVER_SOFTWARE"];
    $req["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
    $req["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];

    $logData["REQUEST"] = $req;
    $logData["RESPONSE"] = $res;

    $errorLogs->addError(json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

//        sendDebugEmail("Error : " . $req["REQUEST_METHOD"] . " " . $req["REQUEST_URI"] , "<pre>" . json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>");
}


function getLogs($path)
{
    $fp = fopen($path, "r", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$fp) echo "error";

    while (!feof($fp)) {
        $str = fgets($fp, 10000);
        $arr[] = $str;
    }
    for ($i = sizeof($arr) - 1; $i >= 0; $i--) {
        echo $arr[$i] . "<br>";
    }
//        fpassthru($fp);
    fclose($fp);
}

function checkAndroidBillingReceipt($credentialsPath, $token, $pid)
{

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope("https://www.googleapis.com/auth/androidpublisher");
    $client->setSubject("USER_ID.iam.gserviceaccount.com");


    $service = new Google_Service_AndroidPublisher($client);
    $optParams = array('token' => $token);

    return $service->purchases_products->get("PACKAGE_NAME", $pid, $token);
}