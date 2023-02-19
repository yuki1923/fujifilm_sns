<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「');
debug('<<<<<<<<<プロフィール編集>>>>>>>>>');
debug('」」」」」」」」」」」」」」」」」」」');
debugLogStart();

//ログインしているかどうか判断
require('auth.php');
$dbPrefectureData = getPrefecture();

//ユーザーIDをもとにユーザーの全情報を取得->ユーザー情報を$dbFormDataに格納
$dbFormData = getUser($_SESSION['user_id']);
debug('$dbFormDataの中身：' . print_r($dbFormData, true));
debug('現在のプロフ画像の中身：' . print_r($dbFormData['pic'], true));
debug('セッション:' . print_r($_SESSION, true));

//ポスト送信されているか判断
if (!empty($_POST)) {
    debug('POST送信されています。');
    debug('postの中身：' . print_r($_POST, true));
    debug('upload_imageの中身：' . print_r($_FILES['pic'], true));

    //ポストされた値を変数に入れる
    // $image = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : null;
    $upload_image = ($_FILES['pic']['error'] === 4) ? null : $_FILES['pic'];
    debug('画像の詳細データ' . print_r($upload_image, true));
    $name = $_POST['username'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $prefecture = $_POST['prefecture'];
    $email = $_POST['email'];

    if ($dbFormData['username'] !== $name) {
        debug('ユーザー名が変更されています。');
        validRequired($name, 'name');
        validMaxLen($name, 'name');
    }
    if ($dbFormData['sex'] !== $sex) {
        debug('性別が変更されています。');
    } else {
        debug('変更なし');
    }
    if ((int)$dbFormData['age'] !== $age) {
        debug('年齢が変更されています。');
        validNum($age, 'age');
    }
    if ((int)$dbFormData['prefecture_id'] !== $prefecture) {
        debug('住所が変更されています。');
        debug('初期データ：' . print_r($dbFormData['prefecture_id'], true));
        debug('更新データ：' . print_r($prefecture, true));
    }
    if ($dbFormData['email'] !== $email) {
        debug('メアドが変更されています。');
        validRequired($email, 'email');
        validEmail($email, 'email');
        validEmailDup($email);
    }
    if ($dbFormData['pic'] !== $upload_image) {
        debug('プロフ画像が変更されています');
        $upload_image = uploadImg($upload_image, 'pic');
    }


    //入力した変更に問題がなければデータベースに情報を更新
    if (empty($err_msg)) {
        debug('エラーはありませんでした。');
        try {
            $dbh = dbConnect();
            $sql = 'UPDATE users SET username = :u_name, email = :email, prefecture_id = :pref, age = :age, sex = :sex, pic = :pic WHERE id = :u_id';
            $data = array(':u_name' => $name, ':email' => $email, ':pref' => $prefecture, ':age' => $age, 'sex' => $sex, ':pic' => $upload_image, ':u_id' => $dbFormData['id']);
            $stmt = queryPost($dbh, $sql, $data);
            if ($stmt) {
                debug('クエリ成功');
                header('Location:mypage.php');
            } else {
                debug('クエリ失敗');
                $err_msg['common'] = ERR_MSG06;
            }
        } catch (Exception $e) {
            $e->getMessage();
            $err_msg['common'] = ERR_MSG06;
        }
    }
}


?>

<?php
$title = 'プロフィール編集ページ';
include_once 'head.php';
?>
<?php include_once 'header.php'; ?>

<!-- メインコンテンツ -->
<main>
    <div class="site-width prof-edit">
        <h2 class="section-title">プロフィール編集</h2>
        <form action="" class="prof-form" method="post" enctype="multipart/form-data">
            <div class="prof-img areaDrop">
                <img src="<?php ($dbFormData['pic'] !== null) ? print $dbFormData['pic'] : print ""; ?>" class="prof-prev-img prevImg" alt="">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145278">
                <input type="file" name="pic" class="prof-input-file inputFile">
            </div>

            <div class="prof-form-list">
                <span class="err-msg"><?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?></span>
                <span class="err-msg"><?php if (!empty($err_msg['name'])) echo $err_msg['name']; ?></span>
                <label for="name" class="prof-label">ユーザー名</label>
                <input type="text" class="profEdit-input" id="name" name="username" placeholder="ユーザー名" value="<?php if (!empty($dbFormData['username'])) echo $dbFormData['username']; ?>">
            </div>

            <div class="prof-form-list">
                <span class="err-msg"><?php if (!empty($err_msg['sex'])) echo $err_msg['sex']; ?></span>
                <label for="sex" class="prof-label">性別</label>
                <select name="sex" id="sex" class="profEdit-input">
                    <option <?php if (($dbFormData['sex']) === "0") echo 'selected'; ?> value="0">不明</option>
                    <option <?php if (($dbFormData['sex']) === "1") echo 'selected'; ?> value="1">男</option>
                    <option <?php if (($dbFormData['sex']) === "2") echo 'selected'; ?> value="2">女</option>
                </select>
            </div>

            <div class="prof-form-list">
                <span class="err-msg"><?php if (!empty($err_msg['age'])) echo $err_msg['age']; ?></span>
                <label for="age" class="prof-label">年齢</label>
                <input type="text" class="profEdit-input" id="age" name="age" placeholder="年齢" value="<?php if (!empty($dbFormData['age'])) echo $dbFormData['age']; ?>">
            </div>

            <div class="prof-form-list">
                <span class="err-msg"><?php if (!empty($err_msg['prefecture'])) echo $err_msg['prefecture']; ?></span>
                <label for="addr" class="prof-label">住所（都道府県）</label>
                <select name="prefecture" id="addr" class="profEdit-input">
                    <?php foreach ($dbPrefectureData as $key => $val) : ?>
                        <?php if ($val['id'] === $dbFormData['prefecture_id']) : ?>
                            <?php echo "<option value=" . $val['id'] . " selected>" ?>
                            <?php echo $val['prefecture_name']; ?>
                            <?php echo "</option>" ?>
                        <?php else : ?>
                            <option value="<?php echo $val['id']; ?>"><?php echo $val['prefecture_name']; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="prof-form-list">
                <span class="err-msg"><?php if (!empty($err_msg['email'])) echo $err_msg['email']; ?></span>
                <label for="email" class="prof-label">メールアドレス</label>
                <input type="email" class="profEdit-input" id="email" name="email" placeholder="メールアドレス" value="<?php if (!empty($dbFormData['email'])) echo $dbFormData['email']; ?>">
            </div>
            <input type="submit" value="編集する" class="profEdit-submit">
        </form>

    </div>
</main>
<?php include_once 'footer.php'; ?>
