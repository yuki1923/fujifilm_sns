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

<?php
$title  = 'マイページ';
include_once 'head.php';
?>

<?php include_once 'header.php'; ?>


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
          <?php foreach ($favPhoto as $key => $val) : ?>
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

    </div>
  </main>
</div>
<?php include_once 'footer.php'; ?>
