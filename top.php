<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debug('トップページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証をする
require('auth.php');

// 現在のページ番号 デフォルトは１ページ目
$currentPageNum = (isset($_GET['p'])) ? $_GET['p'] : 1;
$lens = (!empty($_GET['lens'])) ? $_GET['lens'] : '';
$situ = (!empty($_GET['situ'])) ? $_GET['situ'] : '';
$film = (!empty($_GET['film'])) ? $_GET['film'] : '';
debug('$_GETの中身：' . print_r($_GET, true));
debug('カレントpege' . print_r($currentPageNum, true));



// パラメータに不正な値が入っているかチェック
if (!is_int((int)$currentPageNum)) {
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}
//１ページの表示件数
$listSpan = 30;


//現在ページの最小のデータ数 現在の表示レコード先頭を算出 １ページ目なら（1−1）*20 = 0, 2ページ目なら30
$currentMinNum = (($currentPageNum - 1) * $listSpan);
debug('カレント' . print_r($currentMinNum, true));


//DBから投稿写真を取得
$dbPostList = getPostList($currentMinNum, $lens, $situ, $film);
debug("全データ数：" . print_r($dbPostList['total'], true));
debug("トータルで必要なページ数：" . print_r($dbPostList['total_page'], true));
debug('写真データ：' . print_r($dbPostList['data'], true));
// debug("取得してきたデータ（１ページに表示するデータ）：" . print_r($dbPostList['data'], true));

// 検索リストを格納
$dbLens = getLenses();
$dbSituation = getSituation();
$dbFilm = getFilm();

//パラメータに不正な値が入っていないかチェック
if (!isset($dbPostList['data'])) {
  debug('エラー発生：指定ページに不正な値が入りました。');
  header("Location:top.php");
}

//現在のページ
debug('現在ページ1：' . print_r($currentPageNum, true));
global $dbFormData;
debug('dbformdata:' . print_r($dbFormData, true));




?>

<?php
$title = 'トップページ';
include_once 'head.php';
?>

<?php include_once 'header.php'; ?>

<div class="container topPage-container">
  <form method="GET" class="search-form">
    <div class="search-block">
      <h2 class="search-title">レンズ</h2>
      <select name="lens" class="sort">
        <option value="0" <?php if (getFormData('lens', true) == 0) echo 'selected'; ?>> 選択してください </option>
        <?php foreach ($dbLens as $key => $val) : ?>
          <option value="<?php echo $val['id'] ?>" <?php if (getFormData('lens', true) === $val['id']) echo 'selected'; ?>> <?php echo $val['name'] ?> </option>
        <?php endforeach; ?>
      </select>
    </div>
    <i class="fas fa-times"></i>
    <div class="search-block">
      <h2 class="search-title">フイルムシミュレーション</h2>
      <select name="film" class="sort">
        <option value="0" selected>選択してください</option>
        <?php foreach ($dbFilm as $key => $val) : ?>
          <option value="<?php echo $val['id'] ?>" <?php if (getFormData('film', true) === $val['id']) echo 'selected'; ?>> <?php echo $val['name'] ?> </option>
        <?php endforeach; ?>
      </select>
    </div>
    <i class="fas fa-times"></i>
    <div class="search-block">
      <h2 class="search-title">写真ジャンル</h2>
      <select name="situ" class="sort">
        <option value="0" selected>選択してください</option>
        <?php foreach ($dbSituation as $key => $val) : ?>
          <option value="<?php echo $val['id'] ?>" <?php if (getFormData('situ', true) === $val['id']) echo 'selected'; ?>> <?php echo $val['name'] ?> </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="search-btn">
      <span class="blank"></span>
      <input type="submit" value="検索" class="search-submit">
    </div>
  </form>

  <div class="search-result">
    <p class="search-result-left"><span><?php echo $dbPostList['total']; ?></span>件の写真が見つかりました</p>
    <p class="search-result-right">
      <!-- <span><?php echo $dbPostList['total']; ?></span>件中 -->
      <span><?php echo $currentMinNum + 1; ?></span>
      <span>-</span>
      <span><?php echo $currentMinNum + count($dbPostList['data']); ?>件を表示</span>
    </p>
  </div>
  <section class="main-content">
    <?php foreach ($dbPostList['data'] as $key => $val) : ?>
      <div class="photo-panel-wrap">
        <a href="photoDetail.php<?php echo (!empty(appendGetParam()) ? appendGetParam() . '&p_id=' . $val['id'] : '?p_id=' . $val['id']); ?>" class="photo-panel-container">
          <div class="photo-panel">
            <img src="<?php echo $val['pic'];  ?>" alt="">
          </div>
        </a>
        <p class="photo-title"><?php echo $val['title']; ?></p>
      </div>
    <?php endforeach; ?>

  </section>


  <div class="page-nation">

    <?php
    debug('現在ページ2：' . print_r($currentPageNum, true));
    //トータルページ数
    $totalPageNum = $dbPostList['total_page'];
    //最大ページング表示数
    $pageColNum = 5;


    if ($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum) {
      $maxPageNum = $currentPageNum;
      $minPageNum = $currentPageNum - 4;
      debug('現在ページ3：' . print_r($currentPageNum, true));
    } elseif ($currentPageNum == ($totalPageNum - 1) && $totalPageNum >= $pageColNum) {
      $maxPageNum = $currentPageNum + 1;
      $minPageNum = $currentPageNum - 3;
      debug('現在ページ4：' . print_r($currentPageNum, true));
    } elseif ($currentPageNum == 2 && $totalPageNum >= $pageColNum) {
      $maxPageNum = $currentPageNum + 3;
      $minPageNum = $currentPageNum - 1;
      debug('現在ページ5：' . print_r($currentPageNum, true));
    } elseif ($currentPageNum == 1 && $totalPageNum >= $pageColNum) {
      $maxPageNum = 5;
      $minPageNum = $currentPageNum;
      debug('現在ページ6：' . print_r($currentPageNum, true));
    } elseif ($totalPageNum < $pageColNum) {
      $maxPageNum = $totalPageNum;
      $minPageNum = 1;
      debug('現在ページ7：' . print_r($currentPageNum, true));
    } else {
      $maxPageNum = $currentPageNum + 2;
      $minPageNum = $currentPageNum - 2;
      debug('現在ページ1：' . print_r($currentPageNum, true));
    }
    debug('現在ページ' . print_r($currentPageNum, true));
    debug('最小ページ' . print_r($minPageNum, true));
    debug('最大ページ' . print_r($maxPageNum, true));

    ?>


    <?php if ($currentPageNum != 1) : ?>
      <li class="page-list"><a href="?p=1">&lt;</a></li>
    <?php endif; ?>

    <?php for ($i = $minPageNum; $i <= $maxPageNum; $i++) : ?>
      <li class="page-list <?php if ($currentPageNum == $i) echo 'active-color'; ?>">
        <a href="top.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&p=' . $i : '?p=' . $i; ?>"><?php echo $i ?></a>
      </li>
    <?php endfor;  ?>
    <?php if ($currentPageNum != $maxPageNum) : ?>
      <li class="page-list"><a href="?p=<?php echo $maxPageNum; ?>">&gt;</a></li>
    <?php endif; ?>
  </div>
</div>

<?php require('footer.php'); ?>
