<?php
// 예매 과정을 담당하느 부분
session_start();
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
$scheduleID = $_POST['schedule'];
$seats = $_POST['seats'];
if(!isset($scheduleID) || !isset($seats)) {
    //만약에 스케줄을 선택하지 않았다면 alert을 통해 알리고 처음부터 다시 예매를 시작하도록 창을 닫는다.
    echo "<script>alert('스케줄 혹은 자릿수가 선택이 되지 않았습니다. 처음부터 다시 선택해주세요.');";
    echo "window.close();</script>";
}

try {
    $conn = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    echo("에러 내용: ".$e -> getMessage());
}
// 선택한 스케줄의 남은 좌석 수와 예매하려는 좌석수를 비교하는 과정이다.
$stmt = $conn -> prepare("SELECT (SELECT SEATS FROM THEATER WHERE TNAME = MSCHEDULE.TNAME) - 
(SELECT NVL(SUM(NVL(SEATS, 0)), 0) FROM TICKETING WHERE S_ID=$scheduleID AND STATUS = 'R') RSEATS
from MSCHEDULE
WHERE S_ID = $scheduleID");
$stmt -> execute();
$row = $stmt -> fetch(PDO::FETCH_ASSOC);
// 남은 좌석 수 보다 예매하고자하는 좌석 수가 더 많다면 alert 기능을 이용해 알리고 처음부터 다시 예매하도록 예매창을 닫는다.
if($row['RSEATS'] < $seats) {
    echo "<script> alert('예매 가능 자리수를 초과합니다. 처음부터 다시 예매해주세요.');";
    echo "window.close();</script>";
}
else if($seats > 10) {
    echo "<script> alert('최대로 예매 가능한 자릿수는 10자리 입니다. 처음부터 다시 예매해주세요.');";
    echo "window.close();</script>";
}
// 정상적으로 예매가 가능한 경우
else {
    // INSERT 를 이용해 TICKETING 테이블에 데이터를 추가해준다.
    $stmt = $conn -> prepare("INSERT INTO TICKETING(RC_DATE, SEATS, STATUS, CID, S_ID) VALUES(SYSDATE, $seats, 'R', $user_id, $scheduleID)");
    $stmt -> execute();
    // reservation_mail에 GET 방식으로 scheduleID와 좌석수 seats를 넘겨준다. 해당 변수들은 이메일을 보낼 때 상세 정보를 보내기 위해 사용된다.
    echo "<script>window.location.replace('reservation_mail.php?sid=$scheduleID&seats=$seats');</script>"
?>

<?php
}
?>