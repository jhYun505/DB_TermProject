<?php
// DB와 연결하는 부분
$tns = "
(DESCRIPTION=
    (ADDRESS_LIST = (ADDRESS=(PROTOCOL=TCP)(HOST=localhost)(PORT=1521)))
    (CONNECT_DATA=(SERVICE_NAME=XE))
    )
";
$dsn = "oci:dbname=".$tns."; charset=utf8";
$username = 'USERID';   # Change When You use your database
$password = 'PASSWORD'; # Change When You use your database

try {
$conn = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
echo("에러 내용: ".$e -> getMessage());
}
// 페이지를 이동하는 과정에서 주로 Session check를 하는데, 이 과정에서 관람 완료된 항목들에 대해 UPDATE를 진행하도록 했다.
$stmt = $conn -> prepare("UPDATE TICKETING SET STATUS = 'W' WHERE (STATUS = 'R' AND S_ID IN (SELECT S_ID FROM MSCHEDULE WHERE SDATETIME < SYSDATE))");
$stmt -> execute();
// 세션이 끊긴 경우 (로그아웃 상태에서 다른 페이지들에 접근하는 경우) : 로그인할 수 있도록 돌려보낸다.
if(!isset($_SESSION['user_id']) || !isset($_SESSION['cname'])) {
  echo "<script>alert('로그인이 필요한 페이지입니다. 재로그인을 해주세요.');";
  echo "window.location.replace('login.php');</script>";
  exit;
}
?>