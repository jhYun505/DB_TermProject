<?php
session_start();
include_once('session_chk.php');
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['cname'];

/*관리자만 접근 가능한 통계 페이지이므로 일반 유저 아이디로 접근 시 메인 화면으로 돌려보낸다. */
if($user_id != 0) {
    echo "<script>alert('관리자만 접근 가능한 페이지 입니다.');";
    echo "window.location.replace('main.php');</script>";
}

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
        <title>관람 통계</title>
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
                    <li class="nav-item active">
                        <a class="nav-link" href="statistics.php"><b>통계 확인</b></span></a>
                    </li>
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

        <!--조인 질의문 부분-->
        <div class="container">
            <h2 class="text-center">전체 고객 관람 완료 내역</h2>
            <table class="table table-bordered text-center">
                <thead>
                    <th>고객 번호</th>
                    <th>고객명</th>
                    <th>제목</th>
                    <th>영화관 명</th>
                    <th>예매 좌석 수</th>
                    <th>관람일자</th>
                </thead>
                <tbody>
<?php
/* 모든 관람 완료된(취소되지 않은) 예매 내역에 대하여 고객명, 영화 제목, 상영관 명, 
* 인원 수, 상영 일자를 출력한다.
*/
    $stmt = $conn -> prepare("SELECT CID, CNAME, TITLE, TNAME, SEATS, MSCHEDULE.SDATETIME VIEW_DATE
    FROM MSCHEDULE NATURAL JOIN CUSTOMER NATURAL JOIN MOVIE NATURAL JOIN TICKETING
    WHERE TICKETING.STATUS = 'W'
    ORDER BY VIEW_DATE");
    $stmt -> execute();
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
?>
                <tr>
                    <td><?= $row['CID'] ?></td>
                    <td><?= $row['CNAME'] ?></td>
                    <td><?= $row['TITLE'] ?></td>
                    <td><?= $row['TNAME'] ?></td>
                    <td><?= $row['SEATS'] ?></td>
                    <td><?= $row['VIEW_DATE'] ?></td>
                </tr>

<?php
    }
?>
                </tbody>
            </table>
        </div>

        <!-- 그룹함수 질의문 부분-->
        <div class="container">
            <h2 class="text-center">영화별, 성별 기준 관람 인원 수</h2>
            <table class="table table-bordered text-center">
                <thead>
                    <th>제목</th>
                    <th>성별</th>
                    <th>누적 관객 수</th>
                </thead>
                <tbody>
<?php
/*모든 관람 완료된(취소되지 않은) 예매 내역에 대해 예매자 성별, 영화 제목을 기준으로 
* 총 관람 인원수를 출력한다.
 */
    $stmt = $conn -> prepare("SELECT 
    CASE GROUPING(M.TITLE)
        WHEN 1  THEN 'All Movies'
        ELSE M.TITLE END AS TITLE, 
    CASE GROUPING(C.SEX)
        WHEN 1 THEN 'All Sex'
        ELSE C.SEX END AS SEX, 
    SUM(T.SEATS) TOTAL_WATCHED FROM MOVIE M, TICKETING T, MSCHEDULE S, CUSTOMER C
    WHERE T.S_ID = S.S_ID
    AND S.MID = M.MID
    AND T.CID = C.CID
    AND T.STATUS = 'W'
    GROUP BY ROLLUP(M.TITLE, C.SEX)
    ORDER BY M.TITLE");
    $stmt -> execute();
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
?>
                <tr>
                    <td><?= $row['TITLE'] ?></td>
                    <td><?= $row['SEX'] ?></td>
                    <td><?= $row['TOTAL_WATCHED'] ?></td>
                </tr>

<?php
    }
?>
                </tbody>
            </table>
        </div>
        <!-- 윈도우 함수 질의문 부분-->
        <div class="container">
            <h2 class="text-center">누적 예매 수 순위</h2>
            <table class="table table-bordered text-center">
                <thead>
                    <th>제목</th>
                    <th>누적 예매 수</th>
                    <th>순위</th>
                </thead>
                <tbody>
<?php
/* 예매 내역 데이터에서 영화별로 영화 제목, 영화별 총 관객수, 
* 관람을 완료한 관객이 많은 순서를 동일한 값에 대해 동일한 순위를 부여하되 
* 동일 순위를 같은 건수로 취급하지 않는 관객 수 순위를 출력한다. */

    $stmt = $conn -> prepare("SELECT M.TITLE TITLE, SUM(T.SEATS) TOTAL_WATCHED,
    RANK() OVER (ORDER BY SUM(SEATS) DESC) RANKING FROM MOVIE M, MSCHEDULE S, TICKETING T
   WHERE (T.STATUS = 'W' OR T.STATUS = 'R')
   AND M.MID = S.MID
   AND T.S_ID = S.S_ID
   GROUP BY M.TITLE");
    $stmt -> execute();
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
?>
                <tr>
                    <td><?= $row['TITLE'] ?></td>
                    <td><?= $row['TOTAL_WATCHED'] ?></td>
                    <td><?= $row['RANKING'] ?></td>
                </tr>

<?php
    }
?>
                </tbody>
            </table>
        </div>

    </body>
</html>