<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('<<<<<<<<<<<<<<<パスワードリマインダーページ>>>>>>>>>>>>>>>');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if (!empty($_POST)) {
    //ポストされた値を変数に格納
    $email = $_POST['email'];
    //バリデーションチェック
    validRequired($email, 'email');
    if (empty($err_msg)) {
        validEmail($email, 'email');
        validMaxLen($email, 'email');
        if (empty($err_msg)) {
            debug('バリデーションOK');
            try {
                //dbにメールアドレスが登録されているか
                $dbh = dbConnect();
                $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
                $data = array(':email' => $email);
                $stmt = queryPost($dbh, $sql, $data);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                debug('resultの中身：' . print_r($result, true));

                if ($stmt && array_shift($result)) {
                    debug('クエリ成功');
                    //認証キーを発行
                    $auth_key = randomStr();
                    $from = 'kygarcons0629@gmail.com';
                    $to = $email;
                    $subject = 'パスワード認証キー発行 | 富士の病';
                    $comment = <<<EOT
ゲスト　様

認証キーを発行します。

下記URLよりアクセスし認証キーをご入力してください。

認証キー：　{$auth_key}

http://localhost:8888/web%E3%82%B5%E3%83%BC%E3%83%93%E3%82%B9%E3%82%A2%E3%82%A6%E3%83%88%E3%83%97%E3%83%83%E3%83%88/passRemindReceive.php


////////////////////////////////////
富士の病カスタマーセンター
URL: https://fujinoyamai.com/
E-mail: info@fujinoyamai.com
////////////////////////////////////

EOT;
                    sendMail($from, $to, $subject, $comment);
                    $_SESSION['auth_key'] = $auth_key;
                    $_SESSION['auth_email'] = $email;
                    $_SESSION['auth_key_limit'] = time() + (60 * 30);
                    debug('セッションの中身：' . print_r($_SESSION, true));
                    header('Location:passRemindReceive.php');
                } else {
                    debug('クエリ失敗');
                    $err_msg['email'] = ERR_MSG12;
                }
            } catch (Exception $e) {
                error_log('エラー発生：' . error_log($e->getMessage()));
                $err_msg['common'] = ERR_MSG06;
            }
        }
    }
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
                ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。
                <span class="err-msg"><?php if (!empty($err_msg['email'])) echo $err_msg['email']; ?></span>
                <label for="email" class="prof-label">メールアドレス</label>
                <input type="email" class="profEdit-input" id="email" name="email">
            </div>
            <input type="submit" value="送信する" class="profEdit-submit">
        </form>
    </div>
</main>
<?php include_once 'footer.php'; ?>
