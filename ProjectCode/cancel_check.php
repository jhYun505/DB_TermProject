<?php
session_start();        // 세션 시작
include_once('session_chk.php');
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['cname'];
?>
<?php
$tns = "
    (DESCRIPTION=
        (ADDRESS_LIST = (ADDRESS=(PROTOCOL=TCP)(HOST=localhost)(PORT=1521)))
        (CONNECT_DATA=(SERVICE_NAME=XE))
        )
";
$dsn = "oci:dbname=".$tns."; charset=utf8";
$username = 'USERID';   # Change When You use your database
$password = 'PASSWORD'; # Change When You use your database
$tid = $_GET['tid'];

try {
    $conn = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    echo("에러 내용: ".$e -> getMessage());
}
// TICKETING 테이블의 STATUS를 'R'에서 'C'로 바꾸면 취소가 완료 된다. 기존의 예매한 날짜를 취소한 날짜인 현재 날짜로 바꿔준다.
$stmt = $conn -> prepare("UPDATE TICKETING SET RC_DATE = SYSDATE, STATUS = 'C' WHERE T_ID = $tid AND STATUS = 'R'");
$stmt -> execute();
// script의 alert를 통해 취소가 완료되었음을 알리고
echo "<script> alert('취소가 완료되었습니다.');";
// 다시 예약 리스트로 replace를 해서 새로 고침 효과를 낸다.
echo "window.location.replace('reserved_list.php');</script>";
?>

