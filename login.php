<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「');
debug('ログイン画面表示');
debug('「「「「「「「「「「「「「「「「「「「「「');

debugLogStart();

require('auth.php');

if (!empty($_POST)) {
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_save = (!empty($_POST['pass_save'])) ? true : false;

    validRequired($email, 'email');
    validRequired($pass, 'pass');
    if (empty($err_msg)) {
        validMaxLen($email, 'email');
        validEmail($email, 'email');
        validPass($pass, 'pass');
        if (empty($err_msg)) {
            debug('バリデーションOK');
            try {
                $dbh = dbConnect();
                $sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
                $data = array(':email' => $email);
                $stmt = queryPost($dbh, $sql, $data);
                debug('stmtの中身：' . print_r($stmt, true));
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                debug(print_r($result));
                if (password_verify($pass, $result['password'])) {
                    debug('パスワード一致');
                    $sesLimit = 60 * 60;
                    //セッション有効期限をデフォルトは1時間に設定
                    $_SESSION['login_date'] = time();
                    //ログイン時間を現在時間に更新
                    if (!empty($pass_save)) {
                        debug('ログイン保持にチェックがありました');
                        $_SESSION['login_limit'] = $sesLimit * 24 * 30;
                    } else {
                        debug('ログイン保持はしません');
                        $_SESSION['login_limit'] = $sesLimit;
                    }
                    $_SESSION['user_id'] = $result['id'];
                    debug('セッションの中身：' . print_r($_SESSION, true));
                    header('Location:mypage.php');
                } else {
                    debug('パスワード不一致');
                    $err_msg['pass'] = ERR_MSG08;
                }
            } catch (Exception $e) {
                error_log('エラー発生' . $e->getMessage());
                $err_msg['common'] = ERR_MSG06;
            }
        }
    }
}
debug('画面表示処理終了');





?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.1/css/all.css" integrity="sha384-vp86vTRFVJgpjF9jiIGPEEqYqlDwgyBgEF109VFjmqGmIY/Y4HV4d3Gp2irVfcrp" crossorigin="anonymous">
    <link rel="stylesheet" href="reset.css">
    <link rel="stylesheet" href="style.css">
    <title>topページ</title>
</head>

<body>
    <!-- メインヘッダー -->
    <header class="main-header">
        <div class="outer-g-nav">
            <h1 class="logo"><a href="index.php">logo</a></h1>
            <nav class="g-nav">
                <ul class="g-nav-list">
                    <li class="g-nav-item"><a href="signup.php">新規登録</a></li>
                    <li class="g-nav-item"><a href="login.php">ログイン</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="contant border-outer">
            <h1 class="page-title">ログイン</h1>
            <form method="post" class="form">
                <div class="area-msg">
                    <span class="err-msg"><?php if (!empty($err_msg['email'])) echo $err_msg['email']; ?></span>
                    <input type="text" class="input" name="email" placeholder="メールアドレス" value="<?php if (!empty($err_msg)) echo $email; ?>">
                </div>
                <div class="area-msg">
                    <span class="err-msg"><?php if (!empty($err_msg)) echo $err_msg['pass']; ?></span>
                    <input type="password" class="input" name="pass" placeholder="パスワード">
                </div>
                <p class="submit"><input type="submit" value="ログイン" class="login-submit"></p>
                <p class="checkbox"><input type="checkbox" name="pass_save">次回ログインを省略する</p>
                <p><a href="passRemindSend.php">&lt &lt パスワードを忘れた方はコチラ</a></p>
            </form>
        </div>
    </div>

    <footer class="footer">
        Copyright Fujiの病 .All Rights Reserved.
    </footer>
</body>

</html>
