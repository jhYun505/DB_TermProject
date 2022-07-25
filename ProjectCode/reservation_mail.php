<?php

/**
 * PHPMailer를 통해 예매 완료시 회원 이메일 주소로 예매 내역을 보내준다
 * 전달하는 정보는 회원이 예매한 영화의 제목, 상영관 이름, 관람 일자, 좌석수이다.
 */

//Import PHPMailer classes into the global namespace
include "./PHPMailer/src/PHPMailer.php";
include "./PHPMailer/src/Exception.php";
include "./PHPMailer/src/SMTP.php";

session_start();
date_default_timezone_set('Asia/Seoul');
include_once('session_chk.php');
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['cname'];
$tns = "
    (DESCRIPTION=
        (ADDRESS_LIST = (ADDRESS=(PROTOCOL=TCP)(HOST=localhost)(PORT=1521)))
        (CONNECT_DATA=(SERVICE_NAME=XE))
        )
";
$dsn = "oci:dbname=".$tns."; charset=utf8";
$username = 'USERID';   # Change When You use your database
$password = 'PASSWORD'; # Change When You use your database
$sid = $_GET['sid'];
$seats = $_GET['seats'];
try {
    $conn = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    echo("에러 내용: ".$e -> getMessage());
}

$stmt = $conn -> prepare("SELECT EMAIL FROM CUSTOMER WHERE CID = $user_id");
$stmt -> execute();
$row = $stmt -> fetch(PDO::FETCH_ASSOC);
$email = $row['EMAIL']; //회원의 이메일 주소를 저장

$stmt = $conn -> prepare("SELECT S.TNAME TNAME, TO_CHAR(S.SDATETIME, 'YYYY-MM-DD HH24:MI') VIEW_DATE, M.TITLE TITLE
                        FROM MSCHEDULE S, MOVIE M
                        WHERE S.S_ID = $sid AND M.MID = S.MID");
$stmt -> execute();
$row = $stmt -> fetch(PDO::FETCH_ASSOC);
$tname = $row['TNAME'];     //상영관 이름
$viewDate = $row['VIEW_DATE'];  //관람 일자
$title = $row['TITLE'];     //영화 제목

//Create a new PHPMailer instance
$mail = new PHPMailer();

//Tell PHPMailer to use SMTP
$mail->isSMTP();

//Enable SMTP debugging
//SMTP::DEBUG_OFF = off (for production use)
//SMTP::DEBUG_CLIENT = client messages
//SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = SMTP::DEBUG_OFF;     //디버깅 과정을 유저에게 보여주지 않도록 꺼준다.

//Set the hostname of the mail server
$mail->Host = 'smtp.naver.com';     //네이버 메일로 연결하였다.
//Use `$mail->Host = gethostbyname('smtp.gmail.com');`
//if your network does not support SMTP over IPv6,
//though this may cause issues with TLS

//Set the SMTP port number:
// - 465 for SMTP with implicit TLS, a.k.a. RFC8314 SMTPS or
// - 587 for SMTP+STARTTLS
$mail->Port = 465;

//Set the encryption mechanism to use:
// - SMTPS (implicit TLS on port 465) or
// - STARTTLS (explicit TLS on port 587)
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

//Whether to use SMTP authentication
$mail->SMTPAuth = true;

//Username to use for SMTP authentication - use full email address for gmail
$mail->Username = 'NaverID';

//Password to use for SMTP authentication
$mail->Password = 'NaverPassWord';       //비밀번호는 시연시에만 입력 하도록 한다.

//Set who the message is to be sent from
//Note that with gmail you can only use your account address (same as `Username`)
//or predefined aliases that you have configured within your account.
//Do not use user-submitted addresses in here
$mail->setFrom('YourEmail', 'CNU CINEMA'); //보내는 사람의 이름을 CNU CINEMA로 설정하였다.

//Set an alternative reply-to address
//This is a good place to put user-submitted addresses
$mail->addReplyTo('YourEmail', 'CNU CINEMA');  //답장을 받을 이메일 주소

//Set who the message is to be sent to
$mail->addAddress($email);      //변수로 저장해 두었던 회원 이메일 주소를 추가한다.

$mail -> CharSet = "utf-8";     // 글자가 깨지는 것을 막기 위해서 utf-8로 설정해준다.

//Set the subject line
$mail->Subject = $user_name."님 예매 확인 메일입니다.";     //메일 제목에 회원 이름을 추가하였다.

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//$mail->msgHTML(file_get_contents('mail_contents.html'), __DIR__);

//메일 본문 -> html 문서로 작성
$mail ->Body = 
"<!DOCTYPE html>
<html lang='ko'>
    <head>
        <meta charset = 'utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale='1'>
        <style>
            table {
                width: 100%;
                border: 1px solid #444444;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #444444;
                text-align: center;
            }
        </style>
    </head>

    <h1>".$user_name."님 예매 확인 내역입니다.</h1>
    <table class='table table-bordered text-center'>
        <tr>
            <td>영화 제목</td>
            <td>".$title."</td>
        </tr>
        <tr>
            <td>관람 일시</td>
            <td>".$viewDate."</td>
        </tr>
        <tr>
            <td>상영관 명</td>
            <td>".$tname."</td>
        </tr>
        <tr>
            <td>예매 좌석 수</td>
            <td>".$seats."</td>
        </tr>
    </table>
    <div style = 'padding: 10px;'>
        즐거운 관람 되시기를 바랍니다.
    </div>

</html>
    ";
//Replace the plain text body with one created manually
$mail->AltBody = '예매 내역 확인입니다.';

//Attach an image file
//$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors
if (!$mail->send()) {
    // 이메일 발송에 실패한 경우 : 에러 메세지를 출력
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    // 이메일 발송 성공시 예매 완료 알림 메세지를 띄워주고 확인하면 예매 창을 닫도록 한다.
    echo "<script>alert('예매 완료! 자세한 내용은 메일이나 예매 목록을 참고하세요!');";
    echo "window.close();</script>";
}

