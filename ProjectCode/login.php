<?php
  session_start();
  //혹시 남아있는 session이 있다면 삭제한다 -> 로그 아웃 시켜버림
  session_destroy();
?>

<!doctype html>
<html lang="ko">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LogIn</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.4.1.js"   
	    integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="   
	    crossorigin="anonymous">
    </script>
    <script src="./resource/js/bootstrap.bundle.min.js"></script>
  </head>
  <body class="text-center">
    
    <main class="form-signin w-100 m-auto">
      <div style="margin: auto; text-align: center;">
        <div style="display: inline-block;">
        <!-- form에서 post 방식으로 아이디와 비밀번호를 받아온다. 받아온 id와 비밀 번호를 가지고 login_chk.php로 넘겨준다. -->
          <form action="login_chk.php" method="post">
            <center>
              <img class="mb-6" src="./resource/image/Logo.png" alt="" height="57">
              <h1 class="h3 mb-6 fw-normal">로그인</h1>
              
                <div class="form-floating">
                  <label for="floatingInput">회원 번호</label>
                  <!-- 회원 번호에 required를 붙여 입력하지 않고서는 로그인 버튼을 누를 수 없게 하였다. -->
                  <input type="user_id" name="user_id" class="form-control" id="floatingInput" placeholder="id" width="150px" required >
                </div>
                <div class="form-floating">
                  <label for="floatingPassword">비밀번호</label>
                  <!-- 비밀번호에 required를 붙여 입력하지 않고서는 로그인 버튼을 누를 수 없게 하였다. -->
                  <input type="password" class="form-control" id="floatingPassword" name="user_pw" placeholder="passwd" width="150px" required>
                </div>
              
              <button class="btn btn-primary" type="submit">Log in</button>
            </center>
          </form>
        </div>
      </div>
    </main>
    
    
        
      </body>
</html>