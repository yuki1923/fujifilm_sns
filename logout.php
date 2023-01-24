<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「');
debug('ログアウト処理開始');
debug('「「「「「「「「「「「「「「「「「「「「「');

debug('セッションの中身：' . print_r($_SESSION, true));
$_SESSION = array();
debug('セッションの中身：' . print_r($_SESSION, true));
session_destroy();
debug('セッションの中身：' . print_r($_SESSION, true));
debug('ログアウト完了');

header('Location:login.php');
