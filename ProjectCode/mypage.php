<?php
//마이 페이지 -> 나의 정보를 보여준다.
session_start();
include_once('session_chk.php');
$user_id = $_SESSION['user_id'];
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
        <title>마이 페이지</title>
    </head>

    <body>
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
        <div class="container" style="padding-top: 30px;">
            <h2 class="text-center">마이 페이지</h2>
            <table class="table table-bordered text-center">
<?php
// DB에서 userid를 통해 정보를 받아와서 출력한다.
//null 인 것들은 빈칸으로 출력된다.
    $stmt = $conn -> prepare("SELECT CID, CNAME, PASSWD, EMAIL, BIRTH_DATE, SEX  FROM CUSTOMER WHERE CID = :user_id");
    $stmt -> execute(array($user_id));
    $row = $stmt -> fetch(PDO::FETCH_ASSOC);
?>
                <tr>
                    <td>회원 번호</td>
                    <td><?= $row['CID'] ?></td>
                </tr>
                <tr>
                    <td>이름</td>
                    <td><?= $row['CNAME'] ?></td>
                </tr>
                <tr>
                    <td>이메일</td>
                    <td><?= $row['EMAIL'] ?></td>
                </tr>
                <tr>
                    <td>생일</td>
                    <td><?= $row['BIRTH_DATE'] ?></td>
                </tr>
                <tr>
                    <td>성별</td>
                    <td><?= $row['SEX'] ?></td>
                </tr>

            </table>
        </div>
    </body>
</html>