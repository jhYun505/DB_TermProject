<?php
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
$username = 'USERID';   # Change When You use your database
$password = 'PASSWORD'; # Change When You use your database
// 취소 내역은 날짜를 기준으로 검색할 수 있다.
//시작 날짜 : 전달되지 않는 경우 '' 공백
$startDate = $_GET['startDate'] ?? '';
//종료 날짜 : 전달되지 않는 경우 '' 공백
$endDate = $_GET['endDate'] ?? '';
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
        <title>취소 내역</title>
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
                    // 관리자인 경우($user_id == 0인 경우)만 통계 확인 메뉴가 보이도록 한다.
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
                <a id='user_info' href='mypage.php'> <?= $user_name ?> 님</a>
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
            <h2 class="text-center" style="padding:10px;">예매 취소 내역</h2>
            <form class="row" style="padding: 20px;">
                    <!-- 시작 일자를 받아오는 부분 -->
                    <div class = "col-5">
                        <label for="startDate">시작 일자</label>   
                        <Input type="date" class="form-control" id="startDate" name="startDate" value="<?= $startDate?>">
                    </div>
                    <!-- 종료 일자를 받아오는 부분 -->
                    <div class = "col-5">
                        <label for="endDate">종료 일자</label>
                        <Input type="date" class="form-control" id="endDate" name="endDate" value="<?= $endDate ?>">
                    </div>
                    <div class="col-auto text-end">
                        <button type="submit" class="btn btn-primary mb-3">검색</button>
                    </div>
                </form>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>영화 제목</th>
                            <th>취소 일자</th>
                            <th>상영 일자</th>
                            <th>예매 좌석 수</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
/* 둘 다 공백을 입력한 경우 (메뉴 초기 진입 시) 전체 목록이 보이도록 하였다. */
if($startDate == '' && $endDate == '')     {
    $stmt = $conn -> prepare("SELECT S.MID MID, M.TITLE TITLE, T.RC_DATE RC_DATE, 
    TO_CHAR(S.SDATETIME, 'YY/MM/DD HH24:MI') WATCH_DATE, T.SEATS SEATS 
    FROM MOVIE M, MSCHEDULE S, TICKETING T
    WHERE T.CID = $user_id 
    AND T.STATUS = 'C'
    AND S.S_ID = T.S_ID
    AND M.MID = S.MID
    ORDER BY RC_DATE DESC");
    $stmt -> execute();
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
?>
                        <tr>
                            <td><a href="movieview.php?movieId=<?= $row['MID'] ?>"><?= $row['TITLE'] ?></td>
                            <td><?= $row['RC_DATE'] ?></td>
                            <td><?= $row['WATCH_DATE'] ?></td>
                            <td><?= $row['SEATS'] ?></td>
                        </tr>
                        <?php   
            }}
            ?>
<?php

/* 시작 날짜와 종료 날짜가 모두 입력 된 경우 -> 날짜를 받아와서 BETWEEN을 이용해 그 사이에 있는 날짜만 선택하도록 하였다.
*  BETWEEN을 날짜에 사용할 경우 시간이 00:00:00으로 설정되기 때문에 0.99999를 더해주어서 해당 날짜까지 포함할 수 있도록 하였다.
*/
if($startDate != '' && $endDate != '') {
    $stmt = $conn -> prepare("SELECT S.MID MID, M.TITLE TITLE, T.RC_DATE RC_DATE, 
    TO_CHAR(S.SDATETIME, 'YY/MM/DD HH24:MI') WATCH_DATE, T.SEATS SEATS, T.T_ID TID
    FROM MOVIE M, MSCHEDULE S, TICKETING T
    WHERE T.CID = $user_id 
    AND T.RC_DATE BETWEEN TO_DATE(:startDate, 'YYYY-MM-DD') AND TO_DATE(:endDate, 'YYYY-MM-DD')+0.99999
    AND T.STATUS = 'C'
    AND S.S_ID = T.S_ID
    AND M.MID = S.MID
    ORDER BY RC_DATE DESC"
    );
    $stmt -> execute(array($startDate, $endDate));
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
?>
                        <tr>
                            <td><a href="movieview.php?movieId=<?= $row['MID'] ?>"><?= $row['TITLE'] ?></td>
                            <td><?= $row['RC_DATE'] ?></td>
                            <td><?= $row['WATCH_DATE'] ?></td>
                            <td><?= $row['SEATS'] ?></td>
                        </tr>
                        <?php   
            }}
?>
<?php

/* 종료 일자만 있는 경우 */
if($startDate == '' && $endDate != '') {
    $stmt = $conn -> prepare("SELECT S.MID MID, M.TITLE TITLE, T.RC_DATE RC_DATE, TO_CHAR(S.SDATETIME, 'YY/MM/DD HH24:MI') WATCH_DATE, T.SEATS SEATS, T.T_ID TID
    FROM MOVIE M, MSCHEDULE S, TICKETING T
    WHERE T.CID = $user_id 
    AND T.RC_DATE <= TO_DATE(:endDate, 'YYYY-MM-DD')+0.99999
    AND T.STATUS = 'C'
    AND S.S_ID = T.S_ID
    AND M.MID = S.MID
    ORDER BY RC_DATE DESC"
    );
    $stmt -> execute(array($endDate));
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
?>
                        <tr>
                            <td><a href="movieview.php?movieId=<?= $row['MID'] ?>"><?= $row['TITLE'] ?></td>
                            <td><?= $row['RC_DATE'] ?></td>
                            <td><?= $row['WATCH_DATE'] ?></td>
                            <td><?= $row['SEATS'] ?></td>
                        </tr>
                        <?php   
            }}
?>
<?php

/* 시작 날짜만 있는 경우 */
if($startDate != '' && $endDate == '') {
    $stmt = $conn -> prepare("SELECT S.MID MID, M.TITLE TITLE, T.RC_DATE RC_DATE, TO_CHAR(S.SDATETIME, 'YY/MM/DD HH24:MI') WATCH_DATE, T.SEATS SEATS, T.T_ID TID
    FROM MOVIE M, MSCHEDULE S, TICKETING T
    WHERE T.CID = $user_id 
    AND T.RC_DATE >= TO_DATE(:startDate, 'YYYY-MM-DD')
    AND T.STATUS = 'C'
    AND S.S_ID = T.S_ID
    AND M.MID = S.MID
    ORDER BY RC_DATE DESC"
    );
    $stmt -> execute(array($startDate));
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
?>
                        <tr>
                            <td><a href="movieview.php?movieId=<?= $row['MID'] ?>"><?= $row['TITLE'] ?></td>
                            <td><?= $row['RC_DATE'] ?></td>
                            <td><?= $row['WATCH_DATE'] ?></td>
                            <td><?= $row['SEATS'] ?></td>
                        </tr>
                        <?php   
            }}
?>


            </form>
        </div>
    </body>
</html>