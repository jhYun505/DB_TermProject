<?php
session_start();
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
        <title>메인 페이지</title>
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

                </ul>

            </div>
<?php            
// 로그인이 되어있지 않다면
if(!isset($_SESSION['user_id']) || !isset($_SESSION['cname'])) {
    //상단 메뉴바에 로그인 버튼이 뜨도록 한다.
    echo "<a class='nav-link' href='login.php'>로그인</a>";
}
// 로그인이 되어있는 상태라면 
else {
    // 이름을 받아 와서 이름이 뜨도록 한다.
    // 이름을 누르면 마이 페이지로 이동할 수 있도록 한다.
    // 로그아웃이 가능하도록 로그 아웃 버튼도 추가해준다.
    $user_name = $_SESSION['cname'];
    echo "<div style='padding-right: 30px;'>
    <img src='./resource/image/account.png' height='40px'>
    <a id='user_info' href='mypage.php'> $user_name 님</a>
</div>
<div>
    <a href='logout.php'>
        <img src='./resource/image/logout.png' height=40px>
        로그아웃
    </a>
</div>";

}
?>
        </nav>
        <!-- 메뉴바 끝 -->
        <div class="align-items-center" style="padding-top:150px; padding-left: 30%">
        <!-- 메인 화면은 따로 구현할 것이 없어서 로고와 환영 메세지가 뜨도록 구현하였다. -->
            <img class="mx-auto" src="./resource/image/Logo.png">
            <h2 class="mx-auto">CNU CINEMA에 오신 것을 환영합니다.</h2>
        </div>
    </body>
</html>