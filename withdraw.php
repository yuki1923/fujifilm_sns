<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「');
debug('>>>>>>>>>>>>退会>>>>>>>>>>>>>');
debug('「「「「「「「「「「「「「「「「「「「「「');

require('auth.php');

if (!empty($_POST)) {
    debug('退会処理開始');
    try {
        $dbh = dbConnect();
        $sql = 'UPDATE users SET delete_flg = 1 WHERE id = :u_id'; //論理削除
        // $sql = 'DELETE FROM users WHERE id = :u_id';　物理削除
        $data = array(':u_id' => $_SESSION['user_id']);
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            debug('クエリ成功');
            debug('デストロイ前：' . print_r($_SESSION, true));
            session_destroy();
            debug('デストロイ後：' . print_r($_SESSION, true));
            $_SESSION = array();
            debug('セッションarrayで削除：' . print_r($_SESSION, true));
            header('Location:signup.php');
        } else {
            debug('クエリ失敗');
            $err_msg['common'] = ERR_MSG06;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
debug('画面表示処理終了');

?>

<?php
$title = '退会ページ';
include_once 'head.php'
?>
<?php include_once 'header.php'; ?>

<div class="container">
    <div class="contant border-outer">
        <div class="area-msg">
            <span class="err-msg"><?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?></span>
        </div>
        <h1 class="page-title">退会</h1>
        <form method="post" class="form">
            <p class="submit"><input type="submit" name="withdraw" value="退会する" class="login-submit"></p>
        </form>
    </div>
    <p class="return-btn"><a href="mypage.php">&lt &lt マイページへ戻る</a></p>
</div>

<?php include_once 'footer.php'; ?>
