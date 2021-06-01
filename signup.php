<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「');
debug('新規登録画面表示');
debug('「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if (!empty($_POST)) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $re_pass = $_POST['re_pass'];

    validRequired($name, 'name');
    validRequired($email, 'email');
    validRequired($pass, 'pass');
    validRequired($re_pass, 're_pass');
    if (empty($err_msg)) {
        validEmail($email, 'email');
        validPass($pass, 'pass');
        validMatch($pass, $re_pass, 'pass');
        validEmailDup($email);


        if (empty($err_msg)) {
            try {
                $dbh = dbConnect();
                $sql = 'INSERT INTO users(username,email,password,login_time,create_at) VALUES(:name,:email,:pass,:login_time,:creat_at)';
                $data = array(
                    ':name' => $name, ':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT),
                    ':login_time' => date('Y-m-d H:m:s'), ':creat_at' => date('Y-m-d H:m:s')
                );
                $stmt = queryPost($dbh, $sql, $data);
                if ($stmt) {
                    $_SESSION['user_id'] = $dbh->lastInsertId();
                    $_SESSION['login_date'] = time();
                    $sesLimit = 60 * 60;
                    $_SESSION['login_limit'] = $sesLimit;
                    header('Location:mypage.php');
                } else {
                    // debug('クエリに失敗しました。');
                    // $err_msg['common'] = ERR_MSG06;
                    return false;
                }
            } catch (Exception $e) {
                error_log('エラー発生：' . $e->getMessage());
                $err_msg['common'] = ERR_MSG06;
            }
        }
    }
}

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

<?php include_once 'header.php'; ?>


<div class="container">
    <div class="contant border-outer">
        <h1 class="page-title">新規登録</h1>
        <form class="form" method="post">
            <div class="area-msg">
                <span class="err-msg"><?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?></span>
                <span class="err-msg"><?php if (!empty($err_msg['name'])) echo $err_msg['name']; ?></span>
                <input type="text" name="name" placeholder="ユーザー名" class="input" value="<?php if (!empty($err_msg)) echo $name ?>">
            </div>
            <div class="area-msg">
                <span class="err-msg"><?php if (!empty($err_msg['email'])) echo $err_msg['email']; ?></span>
                <input type="text" name="email" placeholder="メールアドレス" class="input" value="<?php if (!empty($err_msg)) echo $email ?>">
            </div>
            <div class="area-msg">
                <span class="err-msg"><?php if (!empty($err_msg['pass'])) echo $err_msg['pass']; ?></span>
                <input type="password" name="pass" placeholder="パスワード(半角英数字6文字以上)" class="input" value="<?php if (!empty($err_msg)) echo $pass ?>">
            </div>
            <div class="area-msg">
                <span class="err-msg"><?php if (!empty($err_msg['re_pass'])) echo $err_msg['re_pass']; ?></span>
                <input type="password" name="re_pass" class="input" placeholder="パスワード（再入力）">
            </div>
            <p class="submit"><input type="submit" value="登録" class="signup-submit"></p>
        </form>
    </div>
</div>

<footer class="footer">
    Copyright Fujiの病 .All Rights Reserved.
</footer>
</body>

</html>
