<?php
if( !empty($_SESSION['login_date']) ){
    debug('ログイン済みユーザーです。');
    
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
        debug('ログイン有効期限切れです。');

        session_destroy();

        header('Location:login.php');
    }else{
        debug('ログイン有効期限を更新します。');
        $_SESSION['login_date'] = time();
        debug('今のディレクトリ：'.print_r($_SERVER['PHP_SELF'],true));
        if(basename($_SERVER['PHP_SELF']) === 'login.php'){
            debug('マイページへ遷移します');
            header('Location:mypage.php');
        } 
    }
}else{
    debug('未ログインユーザーです。');
    if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
        header("Location:login.php");
    }
}
?>