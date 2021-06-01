<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「');
debug('マイページ画面表示');
debug('「「「「「「「「「「「「「「「「「「「「「');

debugLogStart();
require('auth.php');

$user_id = $_SESSION['user_id'];

$userAllPost = getAllMyPost($user_id);
debug('ユーザーの投稿写真一覧：' . print_r($userAllPost, true));

$favPhoto = getFavPhoto($user_id);







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
      <h1 class="logo"><img class="imgg" src="images/h_logo.png" alt=""></h1>
      <nav class="g-nav">
        <ul class="g-nav-list">
          <li class="g-nav-item"><a href="top.php">トップページ</a></li>
          <li class="g-nav-item"><a href="mypage.php"></a>マイページ</li>
          <li class="g-nav-item"><a href="logout.php">ログアウト</a></li>
        </ul>
      </nav>
    </div>
  </header>
  <!-- サブヘッダー -->
  <header class="sub-header">
    <div class="site-width outer-sub-header">
      <ul class="action-nav-list-left">
        <a href="registPhoto.php">
        <li class="action-nav-btn"><a href="registPhoto.php">写真を投稿する</a></li>
        </a>
      </ul>
      <ul class="action-nav-list-right">
        <li class="action-nav-btn"><a href="profEdit.php">プロフィール編集</a></li>
        <li class="action-nav-btn"><a href="passEdit.php">パスワード変更</a></li>
        <a href="withdraw.php">
          <li class="action-nav-btn"><a href="withdraw.php">退会</a></li>
        </a>
      </ul>
    </div>
  </header>
  <div class="mypage-outer container">
    <main class="main-section">
      <div class=" topPage-container">
        <h2 class="mypage-title">投稿写真一覧</h2>
        <?php if (!empty($userAllPost)) : ?>
          <section class="main-content post-area">

            <?php foreach ($userAllPost as $key => $val) : ?>
              <div class="photo-panel-wrap">
                <a href="photoDetail.php?p_id=<?php echo $val['id']; ?>" class="photo-panel-container">
                  <div class="photo-panel">
                    <img src="<?php echo $val['pic']; ?>" alt="">
                  </div>
                </a>
                <p class="photo-title"><?php echo $val['title']; ?></p>
              </div>
            <?php endforeach; ?>
          </section>
        <?php else : ?>
          <section class="no-section">
            <p class="no-post">まだ投稿がありません。</p>
          </section>
        <?php endif; ?>

        <h2 class="mypage-title">お気に入り</h2>
        <?php if (!empty($favPhoto)) : ?>
          <section class="main-content fav-area">
          <?php foreach($favPhoto as $key=>$val) : ?>
            <div class="photo-panel-wrap">
              <a href="photoDetail.php?p_id=<?php echo $val['id']; ?>" class="photo-panel-container">
                <div class="photo-panel">
                  <img src="<?php echo $val['pic']; ?>" alt="">
                </div>
              </a>
              <p class="photo-title"><?php echo $val['title']; ?></p>
            </div>
            <?php endforeach ; ?>
          </section>
        <?php else : ?>
          <section class="no-section">
            <p class="no-post">まだ投稿がありません。</p>
          </section>
        <?php endif; ?>

      </div>
    </main>
  </div>




  <footer class="footer">
    Copyright Fujiの病 .All Rights Reserved.
  </footer>
</body>

</html>