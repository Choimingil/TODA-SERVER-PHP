<?php

use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendMail($tmpPW,$email){
    $title = 'TODA에서 편지왔어요 :)';
    $content = '<html lang=\'ko\'>
                <head> <meta charset=\'utf-8\'/> </head>
                <body>
                    <h3>임시 비밀번호를 발급했어요! 이 비밀번호로 로그인하시고 마이페이지 -> 비밀번호 변경 에 들어가셔서 비밀번호를 변경해주세요!<br></h3>
                    <h1>'.$tmpPW.'</h1>
                </body>
            </html>';
    $altBody = '임시 비밀번호를 발급했어요! 이 비밀번호로 로그인하시고 마이페이지 -> 비밀번호 변경 에 들어가셔서 비밀번호를 변경해주세요!     '.$tmpPW;
    mailFunction(MAIL_USER,MAIL_PW,$email,$title,$content,$altBody);
}

function getSQLErrorException($errorLogs, $e, $req)
{
    $res = (Object)Array();
    http_response_code(500);
    $header = isValidHeader("HTTP_X_ACCESS_TOKEN", JWT_SECRET_KEY);
    $res->code = 500;
    $res->SQLException = "SQL Exception -> " . $e->getTraceAsString();
//     $res->header = "header -> " . json_encode($header['id']);
//     $res->req = "req -> " . json_encode($req);
//     $res->pathvar = "pathvar -> ". $vars["postID"];
    addErrorLogs($errorLogs, $res, $req);
    echo json_encode($res);

    $title = '투다 오류 발송';
    $content = json_encode($res);
    mailFunction(MAIL_USER,MAIL_PW,MY_EMAIL,$title,$content,$content);
}

function mailFunction($sendUser,$sendPW,$receiveUser,$title,$content,$altBody){
    // PHPMailer 선언
    $mail = new PHPMailer(true);
// 디버그 모드(production 환경에서는 주석 처리한다.)
//    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
// SMTP 서버 세팅
    $mail->isSMTP();
    try {
// 구글 smtp 설정
        $mail->Host = 'smtp.naver.com';
// SMTP 암호화 여부
        $mail->SMTPAuth = true;
// SMTP 포트
        $mail->Port = 465;
// SMTP 보안 프로토콜
        $mail->SMTPSecure = 'ssl';
// gmail 유저 아이디
        $mail->Username = 'withtoda';
//// gmail 패스워드
        $mail->Password = $sendPW;
// 인코딩 셋
        $mail->CharSet = 'utf-8';
        $mail->Encoding = "base64";
// 보내는 사람
        $mail->setFrom($sendUser, 'TODA');
// 받는 사람
        $mail->AddAddress($receiveUser);
// 본문 html 타입 설정
        $mail->isHTML(true);
// 제목
        $mail->Subject = $title;
// 본문 (HTML 전용)
        $mail->Body = $content;
// 본문 (non-HTML 전용)
        $mail->AltBody = $altBody;
        $mail->Send();
        return;
    } catch (phpmailerException $e) {
        echo $e->errorMessage();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}