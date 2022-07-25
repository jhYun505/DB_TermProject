<?php
/* 예매 목록을 확인하는 페이지 
 * 예매 일자를 기준으로 검색이 가능하다.
 */
session_start();
include_once('session_chk.php');
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['cname'];
?>
<?php
//DB와 연결하는 부분
$tns = "
    (DESCRIPTION=
        (ADDRESS_LIST = (ADDRESS=(PROTOCOL=TCP)(HOST=localhost)(PORT=1521)))
        (CONNECT_DATA=(SERVICE_NAME=XE))
        )
";
$dsn = "oci:dbname=".$tns."; charset=utf8";
$username = 'USERID';   # Change When You use your database
$password = 'PASSWORD'; # Change When You use your database
// 예매 날짜를 검색할 때 시작 날짜와 종료 일자를 저장하는 변수.
$startDate = $_GET['startDate'] ?? '';
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
        <title>영화 예매 내역</title>
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
            <h2 class="text-center" style="padding:10px;">영화 예매 내역</h2>
            <form class="row" style="padding: 20px;">
                    <div class = "col-5">
                        <label for="startDate">시작 일자</label>   
                        <Input type="date" class="form-control" id="startDate" name="startDate" value="<?= $startDate?>">
                    </div>
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
                            <th>예매 일자</th>
                            <th>상영 일자</th>
                            <th>예매 좌석 수</th>
                            <th>취소하기</th>
                        </tr>
                    </thead>
                    <tbody>
                    
<?php
// 시작 날짜와 종료일자를 입력하지 않은 경우는 전체 예매 목록을 예매 날짜 기준 내림차순으로 보여준다.
if($startDate == '' && $endDate == '')     {
    $stmt = $conn -> prepare("SELECT S.MID MID, M.TITLE TITLE, T.RC_DATE RC_DATE, 
    TO_CHAR(S.SDATETIME,'YY/MM/DD HH24:MI') WATCH_DATE, T.SEATS SEATS, T.T_ID TID
    FROM MOVIE M, MSCHEDULE S, TICKETING T
    WHERE T.CID = $user_id 
    AND T.STATUS = 'R'
    AND S.S_ID = T.S_ID
    AND M.MID = S.MID
    ORDER BY RC_DATE DESC");
    $stmt -> execute();
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
        $tid = $row['TID'];
?>
                        
                            <tr>
                            <form action="cancel_check.php?tid=<?=$row['TID']?>" method="POST">
                            <!-- 영화의 상세 정보를 확인 할 수 있도록 영화 제목을 누르면 상세페이지로 이동하도록 하였다. -->
                                <td><a href="movieview.php?movieId=<?= $row['MID'] ?>"><?= $row['TITLE'] ?></td>
                                <td><?= $row['RC_DATE'] ?></td>
                                <td><?= $row['WATCH_DATE'] ?></td>
                                <td><span name="seats"><?= $row['SEATS'] ?></span></td>
                                <!-- 취소하기 버튼을 누르면 TID(Ticketing ID)가 cancel_check.php로 전달되어 취소 과정을 거칠 수 있게 한다.--> 
                                <td><input type="submit" class="btn btn-outline-danger" value = "취소하기" name="cancel" ></td>
                            </form>
                            </tr>
                        
                        <?php   
            }}
            ?>
<?php
// 시작일자와 종료 일자가 입력된 경우 : BETWEEN을 사용하여 조건에 맞는 것들만 출력한다.
// 0.99999를 더해주는 이유 -> 날짜의 경우 자동으로 00:00:00으로 설정되어 해당 날짜가 포함이 되지 않는다.
// 따라서 0.99999를 더해주어 해당 날짜도 포함할 수 있도록 한다.
if($startDate != '' && $endDate != '') {

    $stmt = $conn -> prepare("SELECT S.MID MID, M.TITLE TITLE, T.RC_DATE RC_DATE, 
    TO_CHAR(S.SDATETIME,'YY/MM/DD HH24:MI') WATCH_DATE, T.SEATS SEATS, T.T_ID TID
    FROM MOVIE M, MSCHEDULE S, TICKETING T
    WHERE T.CID = $user_id 
    AND T.RC_DATE BETWEEN TO_DATE(:startDate, 'YYYY-MM-DD') AND TO_DATE(:endDate, 'YYYY-MM-DD')+0.99999
    AND T.STATUS = 'R'
    AND S.S_ID = T.S_ID
    AND M.MID = S.MID
    ORDER BY RC_DATE DESC");
    $stmt -> execute(array($startDate, $endDate));
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
        $tid = $row['TID'];
?>
                        
                            <tr>
                                <form action="cancel_check.php?tid=<?=$row['TID']?>" method="POST">
                                    <td><a href="movieview.php?movieId=<?= $row['MID'] ?>"><?= $row['TITLE'] ?></td>
                                    <td><?= $row['RC_DATE'] ?></td>
                                    <td><?= $row['WATCH_DATE'] ?></td>
                                    <td><span name="seats"><?= $row['SEATS'] ?></span></td>
                                    <td><input type="submit" class="btn btn-outline-danger" value = "취소하기" name="cancel" ></td>
                                </form>
                            </tr>
                        
                        <?php   
            }}
            ?>
            <?php
// 시작일자만 입력된 경우 : 시작 날짜 이후 부터 쭉 출력
if($startDate != '' && $endDate == '') {

    $stmt = $conn -> prepare("SELECT S.MID MID, M.TITLE TITLE, T.RC_DATE RC_DATE, TO_CHAR(S.SDATETIME,'YY/MM/DD HH24:MI') WATCH_DATE, T.SEATS SEATS, T.T_ID TID
    FROM MOVIE M, MSCHEDULE S, TICKETING T
    WHERE T.CID = $user_id 
    AND T.RC_DATE >= TO_DATE(:startDate, 'YYYY-MM-DD')
    AND T.STATUS = 'R'
    AND S.S_ID = T.S_ID
    AND M.MID = S.MID
    ORDER BY RC_DATE DESC");
    $stmt -> execute(array($startDate));
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
        $tid = $row['TID'];
?>
                        
                            <tr>
                                <form action="cancel_check.php?tid=<?=$row['TID']?>" method="POST">
                                    <td><a href="movieview.php?movieId=<?= $row['MID'] ?>"><?= $row['TITLE'] ?></td>
                                    <td><?= $row['RC_DATE'] ?></td>
                                    <td><?= $row['WATCH_DATE'] ?></td>
                                    <td><span name="seats"><?= $row['SEATS'] ?></span></td>
                                    <td><input type="submit" class="btn btn-outline-danger" value = "취소하기" name="cancel" ></td>
                                </form>
                            </tr>
                        
                        <?php   
            }}
            ?>
            <?php
// 종료일자만 입력된 경우 : 종료일자 이전의 것만 출력
if($startDate == '' && $endDate != '') {

    $stmt = $conn -> prepare("SELECT S.MID MID, M.TITLE TITLE, T.RC_DATE RC_DATE, TO_CHAR(S.SDATETIME,'YY/MM/DD HH24:MI') WATCH_DATE, T.SEATS SEATS, T.T_ID TID
    FROM MOVIE M, MSCHEDULE S, TICKETING T
    WHERE T.CID = $user_id 
    AND T.RC_DATE <= TO_DATE(:endDate, 'YYYY-MM-DD')+0.99999
    AND T.STATUS = 'R'
    AND S.S_ID = T.S_ID
    AND M.MID = S.MID
    ORDER BY RC_DATE DESC");
    $stmt -> execute(array($endDate));
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
        $tid = $row['TID'];
?>
                        
                            <tr>
                                <form action="cancel_check.php?tid=<?=$row['TID']?>" method="POST">
                                    <td><a href="movieview.php?movieId=<?= $row['MID'] ?>"><?= $row['TITLE'] ?></td>
                                    <td><?= $row['RC_DATE'] ?></td>
                                    <td><?= $row['WATCH_DATE'] ?></td>
                                    <td><span name="seats"><?= $row['SEATS'] ?></span></td>
                                    <td><input type="submit" class="btn btn-outline-danger" value = "취소하기" name="cancel" ></td>
                                </form>
                            </tr>
                        
                        <?php   
            }}
            ?>

            </form>
        </div>
    </body>
    

</html>