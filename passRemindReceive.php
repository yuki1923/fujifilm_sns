<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('<<<<<<<<<<<<<<<認証キー入力ページ（パスリマインドレシーブ）>>>>>>>>>>>>>>>');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
debug('認証セッションの中身：' . print_r($_SESSION, true));

if (empty($_SESSION['auth_key'])) {
    debug('認証キーがありません。');
    header("Location:passRemindSend.php");
}

if (!empty($_POST)) {
    //ポストされた値を変数に格納
    $auth_key = $_POST['auth_key'];
    //バリデーションチェック
    validRequired($auth_key, 'auth_key');
    if (empty($err_msg)) {
        validHalf($auth_key, 'auth_key');
    }
    if (empty($err_msg)) {
        //有効期限チェック
        if ($_SESSION['auth_key_limit'] > time()) {
            debug('有効期限内なのでok');
            //認証キーが送ったものと一致するかチェック
            if ($_SESSION['auth_key'] === $auth_key) {
                debug('認証キーが一致しました。');
                //ランダムなパスワードを生成
                $randomPass = randomStr();
                //パスワードを更新
                try {
                    $dbh = dbConnect();
                    $sql = 'UPDATE users SET password = :pass WHERE email = :email';
                    $data = array(':pass' => password_hash($randomPass, PASSWORD_DEFAULT), ':email' => $_SESSION['auth_email']);
                    $stmt = queryPost($dbh, $sql, $data);
                    if ($stmt) {
                        debug('クエリ成功');
                        //パスワードをメールにてお知らせ
                        $from = 'kygarcons0629@gmail.com';
                        $to = $_SESSION['auth_email'];
                        $subject = 'パスワード発行 | 富士の病';
                        $pass = $randomPass;
                        $comment = <<<EOT
ゲスト　様

パスワードを発行します。

ログイン画面よりログインを行なってください。
ログイン後、マイページから任意のパスワードに変更することが可能です。

新しいパスワード：　{$pass}

ログインページ：　https://fujinoyamai.com/login.php


////////////////////////////////////
富士の病カスタマーセンター
URL: https://fujinoyamai.com/
E-mail: info@fujinoyamai.com
////////////////////////////////////

EOT;
                        sendMail($from, $to, $subject, $comment);
                        //ログインページに遷移
                        debug('メール送信成功。ログインページへ遷移します。');
                        header('Location:login.php');
                        session_unset();
                    } else {
                        debug('クエリ失敗');
                    }
                } catch (Exception $e) {
                    debug('エラー発生：' . error_log($e->getMessage()));
                    $err_msg['common'] = ERR_MSG06;
                }
            } else {
                debug('認証キーが不一致');
                $err_msg['auth_key'] = ERR_MSG14;
            }
        } else {
            debug('有効期限切れなのでエラーで知らせる');
            $err_msg['auth_key'] = ERR_MSG13;
        }
    }
    debug('aaa');
}

?>

<?php
$title = 'パスワード再設定ページ';
include_once 'head.php';
?>

<?php include_once 'header.php'; ?>

<main>
    <div class="site-width passEdit">
        <form method="post" class="form">
            <span class="err-msg"><?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?></span>
            <div class="area-msg">
                送られてきた認証キーを入力してください。
                <span class="err-msg"><?php if (!empty($err_msg['auth_key'])) echo $err_msg['auth_key']; ?></span>
                <label for="auth_key" class="prof-label">認証キー</label>
                <input type="password" class="profEdit-input" id="auth_key" name="auth_key">
            </div>
            <input type="submit" value="送信する" class="profEdit-submit">
        </form>
    </div>
</main>
<?php include_once 'footer.php'; ?>
