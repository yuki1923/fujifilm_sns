<?PHP
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debug('写真詳細ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// 商品IDのGETパラメータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
debug('p_idのん:' . print_r($p_id, true));

//DBから写真データを取得
$viewData = getPhotoOne($p_id);
debug('ビューデータ：' . print_r($viewData, true));
//パラメータに不正な値が入っているかチェック
if (empty($viewData)) {
  // error_log('エラー発生：指定ページに不正な値が入りました');
  debug('エラー発生：なんでや');
  header('Location:top.php'); //トップページへ
}
debug('取得したDBデータ($viewDataの中身)：' . print_r($viewData, true));

if (!empty($_POST['submit'])) {
  debug('コメントするがクリックされました');

  //ログイン認証
  require('auth.php');

  //掲示板を作成
  try {
    $dbh = dbConnect();
    //コメント掲示板がなければinsert文で作成、すでにあればselect文で参照する。
    $sql = 'SELECT id FROM bord WHERE post_id = :post_id AND delete_flg = 0';
    $data = array(':post_id' => $p_id);
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    debug('$resultの中身:' . print_r($result, true));
    if (!empty($result)) {
      debug('既に掲示板があります。');
      header('Location:bord.php?m_id=' . $result['id']);
    } else {
      debug('初めての作成になります。');
      $sql = 'INSERT INTO bord(post_id, post_user, create_at) VALUES(:post_id, :post_user, :create_at)';
      $data = array(':post_id' => $p_id, ':post_user' => $viewData['user_id'], ':create_at' => date('Y-m-d H:i:s'));
      $stmt = queryPost($dbh, $sql, $data);
      header('Location:bord.php?m_id=' . $dbh->lastInsertId()); // コメント掲示板へ
    }
    // $sql = 'INSERT INTO bord(post_user, other_user, photo_id, create_at) VALUES(:post_user, :other_user, :photo_id, :create_at)';
    // $data = array(':post_user'=> $viewData['user_id'], ':other_user'=>$_SESSION['user_id'], ':photo_id'=>$p_id, ':create_at'=>date('Y-m-d H:i:s'));
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = ERR_MSG06;
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
  <div class="contant site-width">
    <h2 class="section-title">写真詳細</h2>
    <div class="img-outer img-detail-outer">
      <img src="<?php echo $viewData['pic']; ?>" class="prev-img">
    </div>
    <div class="iner-width">
      <i class="fas fa-heart js-click-heart <?php if (isFav($_SESSION['user_id'], $viewData['post_id'])) {
                                              echo ' active';
                                            } ?>" data-photoid="<?php echo sanitize($viewData['post_id']); ?>"><span class="heart-text">いいね</span></i>
      <div class="photo-detail-box">
        <span class="badge">タイトル</span>
        <div class="title-post-area posted-area"><?php echo $viewData['title']; ?> </div>
      </div>

      <div class="photo-detail-box">
        <span class="badge">写真ジャンル</span>
        <div class="situation-post-area posted-area"><?php echo $viewData['situation_name']; ?></div>
      </div>
      <div class="photo-detail-box">
        <span class="badge">使用レンズ</span>
        <div class="lens-post-area posted-area"><?php echo $viewData['lens_name']; ?></div>
      </div>

      <div class="photo-detail-box">
        <span class="badge">フイルムシミュレーション</span>
        <div class="film-post-area posted-area"><?php echo $viewData['film_name']; ?></div>
      </div>

      <div class="photo-detail-box">
        <span class="badge">一言コメント</span>
        <div class="comment-post-area posted-area"><?php echo $viewData['comment']; ?></div>
      </div>

      <div class="photo-detail-box">
        <span class="badge">フォトグラファー</span>
        <div class="film-post-area posted-area"><?php echo $viewData['username']; ?></div>
      </div>
      <p class="return-page mb80"><a href="top.php<?php echo appendGetParam(array('p_id')); ?>">&lt;&lt; 写真一覧に戻る</a></p>

      <form action="" method="post">
        <div class="regist-input comment-btn">
          <input type="submit" value="コメントする" name="submit">
        </div>
        <?php if ($viewData['user_id'] === $_SESSION['user_id']) : ?>
          <?php
          echo '<div class="regist-input edit-btn"><input type="submit" value="写真を編集する" name="edit-submit"></div>'
          ?>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>

<?php require('footer.php'); ?>
</body>

</html>
