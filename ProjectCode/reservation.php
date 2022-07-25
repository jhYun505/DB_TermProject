<?php
/* 영화 예매 창 : 기존 한 페이지에서 하던 것과 다르게 팝업창을 이용해 보았다. */
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
        <script src="./resource/js/my_function.js"></script>
        <style> a { text-decoration: none; color: black;} </style>
        <title>영화 예매</title>
    </head>
    <body>
        <div class="container" style="padding-top: 30px;">
            <div>
                <form id="schedule_list" action="reserve_check.php" method="POST">
                    <h2>상영 목록</h2>
                    <table class="table table-bordered text-center">
                        <thead>
                            <th>선택</th>
                            <th>상영일시</th>
                            <th>상영관 이름</th>
                            <th>남은 좌석 수</th>
                        </thead>

<?php
// 예매 가능한 목록을 보여줘야 하므로 현재 시간보다 뒤의 목록에 대해서만 출력되도록 설정하였다.
$stmt = $conn -> prepare("SELECT TITLE FROM MOVIE WHERE MID = $movieId");
$stmt -> execute();
$row = $stmt -> fetch(PDO::FETCH_ASSOC);
$movieTitle = $row['TITLE'];
$stmt = $conn -> prepare("SELECT TO_CHAR(S.SDATETIME, 'YY/MM/DD HH:MM') SDATETIME, S.TNAME, S.S_ID, 
(SEATS - NVL(REV_SEATS,0)) REST_SEATS
FROM (MSCHEDULE S LEFT OUTER JOIN THEATER ON S.TNAME = THEATER.TNAME)
LEFT OUTER JOIN (SELECT T.S_ID, SUM(NVL(T.SEATS,0)) REV_SEATS FROM TICKETING T WHERE T.STATUS = 'R' 
GROUP BY(T.S_ID)) SE ON s.s_id = SE.S_ID
WHERE S.MID=$movieId
AND S.SDATETIME > SYSDATE
ORDER BY S.SDATETIME");
$stmt -> execute();
while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
?> 

                        <!-- 라디오 버튼을 이용해서 value로 S_ID를 넘겨주도록 한다. -->
                        <tr>
                            <td>
                                <input type="radio" name="schedule" value="<?= $row['S_ID']?>">
                            </td>
                            <td>
                                <?= $row['SDATETIME'] ?>
                            </td>
                            <td>
                                <?= $row['TNAME'] ?>
                            </td>
                            <td>
                                <?= $row['REST_SEATS'] ?>
                            </td>
                        </tr>

<?php
}
?>
                </table>

                </div>
                <h2>예매 정보</h2>
                <!-- 예매에 필요한 정보를 입력 하는 부분 : 인원수 입력-->
                <div class="row">
                    
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>
                                        영화 제목
                                    </th>
                                    <th>
                                        예매 좌석 수
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?= $movieTitle?></td>
                                    <!-- 좌석 수의 최솟값은 1 최댓값은 10으로 설정하였다. 초기값을 1로 두어 0이 입력되는 것을 막는다. -->
                                    <td><input type="number" name="seats" id="seats" max="10" min="1" value="1"></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="col-auto text-end">
                            <button type="submit" class="btn btn-primary btn-lg active">예매 완료</button>
                        </div>

                </form>
            </div>

    </body>
</html>