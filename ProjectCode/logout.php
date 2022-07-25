<?php
session_start();
//세션 삭제
$res=session_destroy();
if($res) {
    //로그아웃 시 메인 페이지로 이동 시킴
    echo "<script>window.location.replace('main.php');</script>";
}
?>