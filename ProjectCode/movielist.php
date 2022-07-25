<?php
/* 영화 검색 페이지 */
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
// 영화는 제목 또는 날짜 또는 제목&날짜의 조합으로 검색할 수 있다.
// 입력 값이 없다면 ''(공백)으로 대체한다.
$searchWord = $_GET['searchWord'] ?? '';
$viewDate = $_GET['viewDate'] ?? '';
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
        <title>영화 검색</title>
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
            <div style="padding-right: 30px;">
                <img src="./resource/image/account.png" height="40px">
                <a id='user_info' href="mypage.php"><?= $_SESSION['cname'] ?> 님</a>
            </div>
            <div>
                <a href="logout.php">
                    <img src="./resource/image/logout.png" height=40px>
                    로그아웃
                </a>
            </div>
        </nav>
        <!-- 메뉴바 끝 -->
        <div class="container">
            <h2 class="text-center" style="padding:10px;">영화 검색</h2>
            <form class="row" style="padding: 20px;">
                <div class = "col-10">
                    <!-- 제목 검색 부분 -->
                    <label for="searchWord">제목 검색</label>
                    <Input type="text" class="form-control" id="searchWord" name="searchWord" placeholder="검색어 입력" value="<?= $searchWord ?>">
                </div>
                <div class = "col-10">
                    <!-- 관람 일자로 검색하는 부분 -->
                    <label for="searchDate">관람 일자</label>
                    <Input type="date" class="form-control" id="viewDate" name="viewDate" placeholder="2022/01/01" value="<?= $viewDate ?>">
                </div>
                <div class="col-auto text-end">
                    <button type="submit" class="btn btn-primary mb-3">검색</button>
                </div>
            </form>
            <table class="table table-bordered text-center">
                <!-- 테이블 헤더 부분 : 검색 목록에서는 관람 등급, 제목, 시간만 보이도록 하고 나머지 정보는 상세페이지에서 확인이 가능하도록 하였다. -->
                <thead>
                    <tr>
                        <th>관람 등급</th>
                        <th>영화 제목</th>
                        <th>상영 시간</th>
                    </tr>
                </thead>
                <tbody>
<?php
// 입력된 검색어에 따라 다른 결과가 출력되도록 하였다.
/* 관람 날짜만 입력 된 경우 -> 관람 날짜로만 검색한다. */
if($searchWord == '' && $viewDate != '') {
    $stmt = $conn -> prepare("SELECT DISTINCT  M.RATING RATING, S.MID MID, M.TITLE TITLE, M.LENGTH M_LEN FROM MOVIE M, MSCHEDULE S
    WHERE TO_CHAR(S.SDATETIME, 'YYYY-MM-DD') = :viewDate AND M.MID = S.MID");

    $stmt -> execute(array($viewDate));
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
        ?>
                        
                        <tr>
                            <td><?= $row['RATING'] ?></td>
                            <!-- 영화 제목을 누르면 상세 페이지로 이동하도록 -->
                            <td><a href="movieview.php?movieId=<?= $row['MID'] ?>"><?= $row['TITLE'] ?></a></td>
                            <td><?= $row['M_LEN'] ?></td>
                        </tr>
                        <?php   
            }}
            ?>

<?php
/* 영화 제목만 입력 한 경우 -> 영화 제목을 이용한 검색 */
if($searchWord != '' && $viewDate == '') {
    $stmt = $conn -> prepare("SELECT DISTINCT S.MID MID, M.TITLE TITLE, M.LENGTH M_LEN, M.RATING RATING FROM MOVIE M, MSCHEDULE S 
    WHERE M.MID = S.MID AND LOWER(M.TITLE) LIKE '%' || :searchWord || '%' AND S.SDATETIME > SYSDATE");
    $stmt -> execute(array($searchWord));
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
        ?>
                        <tr>
                            
                            <td><?= $row['RATING'] ?></td>
                            <td><a href="movieview.php?movieId=<?= $row['MID'] ?>"><?= $row['TITLE'] ?></a></td>
                            <td><?= $row['M_LEN'] ?></td>
                        </tr>
                        <?php   
            }}?>
<?php
/* 영화 제목과 관람 날짜 모두 입력된 경우 : 두가지 조건을 모두 만족하는 결과만 나타낸다. */
if( $searchWord != '' && $viewDate != '') {
    $stmt = $conn -> prepare("SELECT DISTINCT S.MID MID, M.TITLE TITLE, M.LENGTH M_LEN, M.RATING RATING FROM MOVIE M, MSCHEDULE S 
    WHERE M.MID = S.MID AND LOWER(M.TITLE) LIKE '%' || :searchWord || '%' AND TO_CHAR(S.SDATETIME, 'YYYY-MM-DD') = :viewDate");
    $stmt -> execute(array($searchWord, $viewDate));
   while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
?>
                <tr>
                    
                    <td><?= $row['RATING'] ?></td>
                    <td><a href="movieview.php?movieId=<?= $row['MID'] ?>"><?= $row['TITLE'] ?></a></td>
                    <td><?= $row['M_LEN'] ?></td>
                </tr>
                <?php   
    }
}

?>
                </tbody>

            </table>
        </div>
    </body>
</html>