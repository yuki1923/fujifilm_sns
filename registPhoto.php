<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debug('写真投稿ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証をする
require('auth.php');

//p_idを定義。一覧から編集しにきたのか、新規投稿しにきたのか判断
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
//DBから写真情報を取得。p_idがあればユーザーidとp_idを基に写真データを取得する
$dbFormData = (!empty($p_id)) ? getPhoto($_SESSION['user_id'], $p_id) : '';
//新規か編集か判断するためのフラグを作成 dbFormDataがあれば編集、なければ新規
$editFlg = (!empty($dbFormData)) ? true : false;
//それぞれのセレクトボックスの値を取得し表示させるために格納
$dbLens = getLenses();
$dbSituation = getSituation();
$dbFilm = getFilm();

if (!empty($p_id) && empty($dbFormData)) {
  debug('urlが改ざんされています');
  header('Location:mypage.php');
}

if (!empty($_POST)) {
  $title = $_POST['title'];
  $situ = $_POST['situ'];
  $lens = $_POST['lens'];
  $film = $_POST['film'];
  $comment = $_POST['comment'];
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';


  if (!$editFlg) {
    debug('新規投稿バリデーション');
    //タイトルvc
    validRequired($title, 'title');
    validMaxLen($title, 'title', 20);
    //セレクトボックスvc
    // validSelect($situ,'situ');
    //レンズ選択のみ必須にしている
    // validSelect($lens,'lens');
    // validSelect($film,'film');
    //コメントvc
    validRequired($comment, 'comment');
    validMaxLen($comment, 'comment', 400);
    //写真vc
    // validPic($pic,'pic');
  } else {
    debug('編集バリデーション');
    if ($dbFormData['title'] !== $title) {
      validRequired($title, 'title');
      validMaxLen($title, 'title');
    }
    // if($dbFormData['situation_id'] !== $situ){
    //   validSelect($situ,'situ');
    // }
    //レンズ選択のみ必須にしている
    if ($dbFormData['lens_id'] !== $lens) {
      validSelect($lens, 'lens');
    }
    // if($dbFormData['film_id'] !== $film){
    //   validSelect($film,'film');
    // }
    if ($dbFormData['comment'] !== $comment) {
      validRequired($comment, 'comment');
      validMaxLen($comment, 'comment');
    }
    // if($dbFormData['pic'] !== $pic){
    //   validPic($pic,'pic');
    // }
  }
  debug('投稿の中身；' . print_r($_POST, true));

  if (empty($err_msg)) {
    debug('バリデーションチェックOK');
    try {
      $dbh = dbConnect();
      if (!$editFlg) {
        debug('新規投稿のためDBにインサート');
        $sql = 'INSERT INTO posts(title,pic,lens_id,situation_id,film_id,comment,create_at,user_id) VALUES(:title,:pic,:lens,:situation,:film,:comment,:create_at,:user_id)';
        $data = array(':title' => $title, ':pic' => $pic, ':lens' => $lens, ':situation' => $situ, ':film' => $film, ':comment' => $comment, ':create_at' => date('Y-m-d H:m:s'), ':user_id' => $_SESSION['user_id']);
      } else {
        debug('編集のためDBにアップデート');
        $sql = 'UPDATE posts SET title=:title, pic=:pic, lens_id=:lens, situation_id=:situation, film_id=:film, comment=:comment, update_at=:update_at WHERE id=:p_id';
        $data = array(':title' => $title, ':pic' => $pic, ':lens' => $lens, ':situation' => $situ, ':film' => $film, ':comment' => $comment, ':update_at' => date('Y-m-d H:m:s'), ':p_id' => $p_id);
      }
      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      if ($stmt) {
        debug('クエリ成功');
        header('Location:mypage.php');
      }
    } catch (Exception $e) {
      debug('エラー発生：' . print_r($e->getMessage()));
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
  <title>写真投稿</title>
</head>

<?php include_once 'header.php'; ?>

<div class="container">
  <div class="contant">
    <h1 class="page-title regist-photo-title"><?php echo ($editFlg) ? '写真投稿編集' : '新規投稿'; ?></h1>
    <section class="main">
      <form method="post" class="regist-form" enctype="multipart/form-data">
        <span class="err-msg"><?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?></span>
        <div class="img-outer area-drop">
          <input type="hidden" name="MAX_FILE_SIZE" value="3145278">
          <input type="file" name="pic" class="input-file">
          <img src="<?php echo getFormData('pic'); ?>" class="prev-img" alt="" style="<?php if (empty(getFormData('pic'))) echo "display: none";  ?>">
          ドラッグ＆ドロップ
        </div>
        <span class="err-msg"><?php if (!empty($err_msg['pic'])) echo $err_msg['pic']; ?></span>

        <span class="err-msg"><?php if (!empty($err_msg['title'])) echo $err_msg['title']; ?></span>
        <label for="" class="tag">タイトル</label>
        <input type="text" name="title" class="regist-photo-list js-count-text" value="<?php echo getFormData('title'); ?>">


        <span class="err-msg"><?php if (!empty($err_msg['situ'])) echo $err_msg['situ']; ?></span>
        <label for="" class="tag">写真ジャンル</label>
        <select name="situ" class="regist-photo-list">
          <option value="0" <?php if (getFormData('situ') == 0) {
                              echo 'selected';
                            } ?>>選択してください</option>
          <?php foreach ($dbSituation as $key => $val) : ?>
            <option value="<?php echo $val['id']; ?>" <?php if (getFormData('situ') === $val['id']) {
                                                        echo 'selected';
                                                      } ?>> <?php echo $val['name']; ?> </option>
          <?php endforeach; ?>
        </select>

        <span class="err-msg"><?php if (!empty($err_msg['lens'])) echo $err_msg['lens']; ?></span>
        <label for="" class="tag">使用レンズ</label>
        <select name="lens" class="regist-photo-list">
          <option value="0" <?php if (getFormData('lens')) echo 'selected'; ?>>選択してください</option>
          <?php foreach ($dbLens as $key => $val) : ?>
            <option value="<?php echo $val['id']; ?>" <?php if (getFormData('lens') === $val['id']) {
                                                        echo 'selected';
                                                      } ?>><?php echo $val['name']; ?></option>
          <?php endforeach; ?>
        </select>

        <span class="err-msg"><?php if (!empty($err_msg['film'])) echo $err_msg['film']; ?></span>
        <label for="" class="tag">フイルムシミュレーション</label>
        <select name="film" class="regist-photo-list">
          <option value="0" <?php if (getFormData('film')) echo 'selected'; ?>>選択してください</option>
          <?php foreach ($dbFilm as $key => $val) : ?>
            <option value="<?php echo $val['id']; ?>" <?php if (getFormData('film') === $val['id']) {
                                                        echo 'selected';
                                                      } ?>><?php echo $val['name']; ?></option>
          <?php endforeach; ?>
        </select>
        <span class="err-msg"><?php if (!empty($err_msg['comment'])) echo $err_msg['comment']; ?></span>
        <label for="" class="tag">コメント</label>
        <textarea name="comment" id="" cols="30" rows="10" class="regist-textare js-count-text"><?php echo getFormData('comment'); ?></textarea>
        <div><span class="js-count-view"></span>/400</div>
        <div class="regist-input"><input type="submit" value="<?php echo ($editFlg) ? '変更する' : '投稿する'; ?>"></div>
      </form>
    </section>
  </div>

  <?php require('footer.php'); ?>
  </body>

</html>
