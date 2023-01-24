<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('<<<<<<<<<<<<<<<パスワード変更ページ>>>>>>>>>>>>>>>');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');
$userData = getUser($_SESSION['user_id']);
debug('userDataの中身：' . print_r($userData, true));

if (!empty($_POST)) {
    //ポストされた値を変数に宣言
    $oldPass = $_POST['old-pass'];
    $newPass = $_POST['new-pass'];
    $reNewPass = $_POST['re-new-pass'];

    //バリデーションチェック
    validRequired($oldPass, 'oldPass');
    validRequired($newPass, 'newPass');
    validRequired($newPass, $reNewPass, 'reNewPass');
    if (empty($err_msg)) {
        debug('入力チェックoK');
        validPass($oldPass, 'oldPass');
        if (!password_verify($oldPass, $userData['password'])) {
            $err_msg['oldPass'] = ERR_MSG11;
        }
        // try{
        //     $dbh = dbConnect();
        //     $sql = 'SELECT password FROM users WHERE id = :u_id';
        //     $data = array(':u_id'=> $_SESSION['user_id']);
        //     $stmt = queryPost($dbh,$sql,$data);
        //     $result = $stmt->fetch(PDO::FETCH_ASSOC);
        //     debug('resultの中身：'.print_r($result,true));
        //     if($result){
        //         debug('クエリ成功');
        //         if(!password_verify($oldPass,$result['password']) ){
        //             debug('登録されているパスワードと違う');
        //             $err_msg['oldPass'] = ERR_MSG11;
        //         }
        //     }else{
        //         debug('クエリ失敗');
        //         $err_msg['common'] = ERR_MSG06;
        //     }
        // }catch(Exception $e){
        //     error_log($e->getMessage());
        //     $err_msg['common'] = ERR_MSG06;
        // }


        debug('古いパスのバリデーションok');
        validPass($newPass, 'newPass');
        debug('新しいパスのバリデーションok');
        validPassDup($oldPass, $newPass, 'newPass');
        debug('古いパスと新しいパスが同値ではないのでOK');
        validMatch($newPass, $reNewPass, 'reNewPass');
        debug('再入力と同じでOK');

        //エラーなければデータベース更新
        if (empty($err_msg)) {
            debug('バリデーションチェックok');
            //dbに接続
            try {
                $dbh = dbConnect();
                $sql = 'UPDATE users SET password = :newPass WHERE id = :u_id';
                $data = array(':newPass' => password_hash($newPass, PASSWORD_DEFAULT), 'u_id' => $_SESSION['user_id']);
                $stmt = queryPost($dbh, $sql, $data);
                debug('パスワード変更完了');
                if ($stmt) {
                    debug('クエリ成功、パスワード変更完了');

                    $username = $userData['username'];
                    $from = 'kygarcons0629@gmail.com';
                    $to = $userData['email'];
                    $subject = 'パスワード変更通知 | 富士の病';
                    $comment = <<<EOT

{$username}　さん
パスワードが変更されました。

////////////////////////////////////
富士の病カスタマーセンター
URL: https://fujinoyamai.com/
E-mail: info@fujinoyamai.com
////////////////////////////////////
EOT;
                    //メール送信プログラム実行
                    sendMail($from, $to, $subject, $comment,);
                    //マイページに遷移
                    header('Location:mypage.php');
                } else {
                    debug('クエリ失敗');
                    $err_msg['common'] = ERR_MSG06;
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                $err_msg['common'] = ERR_MSG06;
            }
        }
    }
}

// $dbh = dbConnect();
// $sql = 'SELECT password FROM users WHERE id = :u_id';
// $data = array(':u_id'=> $_SESSION['user_id']);
// $stmt = queryPost($dbh,$sql,$data);
// $result = $stmt->fetch(PDO::FETCH_ASSOC);

// if(!password_verify($newPass,$result['password']) ){
//     debug('登録されているパスワードと違う');
//     $err_msg['oldPass'] = ERR_MSG11;
// }


?>

<?php
$title = 'パスワード変更ページ';
include_once 'head.php';
?>

<?php include_once 'header.php'; ?>

<main>
    <div class="site-width passEdit">
        <h2 class="section-title">パスワード変更</h2>
        <form method="post" class="form">
            <span class="err-msg"><?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?></span>
            <div class="prof-form-list">
                <span class="err-msg"><?php echo getErrMsg('oldPass') ?></span>
                <label for="old-pass" class="prof-label">古いパスワード</label>
                <input type="password" class="profEdit-input" id="old-pass" name="old-pass" value="<?php if (!empty($err_msg)) echo $oldPass; ?>">
            </div>
            <div class="prof-form-list">
                <span class="err-msg"><?php echo getErrMsg('newPass') ?></span>
                <label for="new-pass" class="prof-label">新しいパスワード</label>
                <input type="password" class="profEdit-input" id="new-pass" name="new-pass" value="<?php if (!empty($err_msg)) echo $newPass; ?>">
            </div>
            <div class="prof-form-list">
                <span class="err-msg"><?php echo getErrMsg('Pass') ?></span>
                <label for="re-new-pass" class="prof-label">新しいパスワード（再入力）</label>
                <input type="password" class="profEdit-input" id="re-new-pass" name="re-new-pass">
            </div>
            <input type="submit" value="変更する" class="profEdit-submit">
        </form>
    </div>
</main>
<?php include_once 'footer.php'; ?>
