<?php
/* 
그래픽 변경 없이 로그인 과정을 수행
로그인 화면의 form이 제출 되면 여기로 와서 정확히 맞는지 확인한다. 
DB의 CUSTOMER TABLE에서 확인해야 하므로  DB와 연동해준다. */
$tns = "
    (DESCRIPTION=
        (ADDRESS_LIST= (ADDRESS=(PROTOCOL=TCP)(HOST=localhost)(PORT=1521)))
        (CONNECT_DATA= (SERVICE_NAME=XE))
  )
";
$dsn = "oci:dbname=".$tns.";charset=utf8";
$username = 'USERID';   # Change When You use your database
$password = 'PASSWORD'; # Change When You use your database
try {
    $conn = new PDO($dsn, $username, $password);
}   catch (PDOException $e) {
    echo "에러 내용: ".$e->getMessage();
}

//id와 password를 받아와서 변수에 저장한다. POST 방식으로 전달 받는다.
$user_id = $_POST['user_id'];
$user_pw = $_POST['user_pw'];


# DB에서 회원 번호를 기준으로 검색한다. CID와 PASSWD, 이름을 가져온다.
$stmt = $conn -> prepare("SELECT CID, CNAME, PASSWD FROM CUSTOMER WHERE CID = $user_id");
$stmt -> execute();
$row = $stmt -> fetch(PDO::FETCH_ASSOC);

// 회원 번호가 없거나 비밀번호가 틀림
if ( !isset($row['CID']) || $row['PASSWD'] != $user_pw ) {
    //alert를 이용해 입력 정보가 잘못되었다는 것을 알려준다.
    echo "<script>alert('회원번호 또는 패스워드가 잘못되었습니다.');";
  // 다시 로그인 할 수 있도록 login.php로 replace해준다.
    echo "window.location.replace('login.php');</script>";
}
// 비밀번호가 일치 -> 로그인 성공
else{
    if ( $row['PASSWD'] == $user_pw ) {
        session_start();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['cname'] = $row['CNAME'];

        if ($user_id == 0) {
            // 관리자 로그인 : 초기 화면은 통계
            echo "<script>window.location.replace('statistics.php');</script>";
        } else {
            // 일반 고객 로그인 : 초기 화면은 영화 검색
            echo "<script>location.href='movielist.php'</script>";
        }
    }
}

?>
