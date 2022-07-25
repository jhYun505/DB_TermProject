<?php
/* 영화 상세 페이지 GET을 통해서 MOVIE 테이블의 MID를 받아와 상세 정보를 보여준다. */
session_start();
date_default_timezone_set('Asia/Seoul');
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
$username = 'c##d201802120';
$password = 'wlgusa96';
// MID를 GET 방식으로 받아온다.
$movieId = $_GET['movieId'];
try {
    $conn = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    echo("에러 내용: ".$e -> getMessage());
}
 
?>

<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset = "utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.4.1.js"   
	    integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="   
	    crossorigin="anonymous">
        </script>
        <script src="./resource/js/bootstrap.bundle.min.js"></script>
        <style> a { text-decoration: none; color: black;} </style>
        <title>영화 상세 페이지</title>
    </head>

    <body>
        <!-- 메뉴바 시작 -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="./main.php" style="padding-left: 20px;">
                <img src="./resource/image/Logo.png" height="60" class="d-inline-block align-top">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="movielist.php">영화 검색</span></a>
                    </li>
                    <li class="nav-item dropdown active">
                        <a class="nav-link dropdown-toggle" href="resrved_list.php" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        영화 예매 내역
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="reserved_list.php">영화 예매 내역</a>
                        <a class="dropdown-item" href="canceled_list.php">영화 취소 내역</a>
                        <a class="dropdown-item" href="watched_list.php">영화 관람 내역</a>
                        </div>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="mypage.php">마이 페이지</span></a>
                    </li>
                    <?php
                    if($_SESSION['user_id'] == 0) {
                        ?>
                    <li class="nav-item active">
                        <a class="nav-link" href="statistics.php">통계 확인</span></a>
                    </li>
                    <?php
                    }
                    ?>
                </ul>

            </div>
            <div style='padding-right: 30px;'>
                <img src='./resource/image/account.png' height='40px'>
                <a id='user_info' href='mypage.php'> <?= $user_name?> 님</a>
            </div>
            <div>
                <a href='logout.php'>
                    <img src='./resource/image/logout.png' height=40px>
                    로그아웃
                </a>
            </div>
        </nav>
        <!-- 메뉴바 끝 -->
        <div class="container">
<?php
// movieId(MID) 를 이용해 영화에 대한 상세 정보를 받아온다.
$stmt = $conn -> prepare("SELECT TITLE, OPEN_DAY, DIRECTOR, RATING, LENGTH M_LEN, 
                            (SELECT LISTAGG(A.ANAME, ',') WITHIN GROUP(ORDER BY A.ANAME) FROM ACTOR A WHERE A.MID = $movieId) AS ACTORS FROM MOVIE WHERE MID = $movieId");
$stmt -> execute();
$row = $stmt -> fetch(PDO::FETCH_ASSOC);
$openday = $row['OPEN_DAY'];
$title = $row['TITLE'];
?>
            <h1><?= $row['TITLE']?></h1>
            <table class="table table-bordered text-center">
                <tr>
                    <td>영화 개봉일</td>
                    <td><?= $row['OPEN_DAY']?></td>
                </tr>
                <tr>
                    <td>영화 감독</td>
                    <td><?= $row['DIRECTOR']?></td>
                </tr>
                <tr>
                    <td>관람 등급</td>
                    <td><?= $row['RATING']?></td>
                </tr>
                <tr>
                    <td>영화 길이</td>
                    <td><?= $row['M_LEN']?></td>
                </tr>
                <tr>
                    <td>출연진</td>
                    <td><?= $row['ACTORS']?></td>
                </tr>
<?php
// 예매자 수를 구하는 과정 -> STATUS가 'W'인 사람들은 예매해서 영화를 본 사람들, 'R'인 사람들은 예매를 하고 아직 보지 않은 사람들이다.
$stmt = $conn -> prepare("SELECT NVL(SUM(NVL(T.SEATS, 0)),0) SEATS FROM TICKETING T, MSCHEDULE S 
WHERE S.MID = $movieId AND T.S_ID = S.S_ID AND (T.STATUS = 'W' OR T.STATUS = 'R')");
$stmt -> execute();
$row = $stmt -> fetch(PDO::FETCH_ASSOC);
?>
                <tr>
                    <td>예매자 수</td>
                    <td><?= $row['SEATS'] ?></td>
                </tr>
<?php
// 영화 개봉일과 현재를 비교하여 이미 개봉한 영화의 경우에만 누적 관객수가 나타나도록 한다.
$nowDate = date('Y-m-d h:i:s');
$date_now = new DateTime($nowDate);
$open_date = str_replace('/', '-', $openday);
$date_open = new DateTime($open_date);
if($date_now >= $date_open) {
    // 누적 관객수의 경우는 STATUS가 'W'인 경우에만 해당한다 (아직 관람하지 않은 사람들은 제외함.)
    $stmt = $conn -> prepare("SELECT NVL(SUM(NVL(T.SEATS, 0)),0) T_SEATS FROM TICKETING T, MSCHEDULE S 
    WHERE S.MID = $movieId AND T.S_ID = S.S_ID AND T.STATUS = 'W'");
    $stmt -> execute();
    $row = $stmt -> fetch(PDO::FETCH_ASSOC);
    ?>
                <tr>
                    <td>누적 관람객 수</td>
                    <td><?= $row['T_SEATS'] ?></td>
                </tr>
    <?php
}
?>
            </table>
<?php
// 만 나이를 계산해서 관람 등급과 맞는지 확인한다.
$stmt = $conn -> prepare("SELECT TO_CHAR(BIRTH_DATE, 'YYYY-MM-DD') BIRTH_DATE FROM CUSTOMER WHERE CID = $user_id");
$stmt -> execute();
$row = $stmt -> fetch(PDO::FETCH_ASSOC);
$birth_date = $row['BIRTH_DATE'];
$stmt = $conn -> prepare("SELECT RATING FROM MOVIE WHERE MID = $movieId");
$stmt -> execute();
$row = $stmt -> fetch(PDO::FETCH_ASSOC);
$rate = $row['RATING'];
$limit = (int) $rate;   // 형 변환 -> 숫자가 아닌 문자열의 경우 0을 반환한다
$age = floor((date("Ymd") - date("Ymd", strtotime($birth_date))) / 10000);      //만 나이 계산
// 만 나이가 관람등급과 같거나 더 많을 경우 : 예매하기 버튼을 보여준다
if($age >= $limit) {
?>
            <a href="javascript:window.open('reservation.php?movieId=<?= $movieId?>', 'popup','top=10, left=10, width=800, height=600, status=no, menubar=no, toolbar=no, resizable=no');"
            class= "btn btn-primary btn-lg active" id="reserve" role="button" aria-pressed="true">예매하기</a>
<?php
}
else {
    // 만 나이가 관람등급보다 적을 경우 : 예매 버튼을 보여주지 않아서 예매가 불가능 하도록 한다.
    ?>
            <div class="alert alert-danger" role="alert">
                연령 제한으로 인해 관람할 수 없습니다.
            </div>
<?php 
}
?>
        </div>
    </body>
</html>
