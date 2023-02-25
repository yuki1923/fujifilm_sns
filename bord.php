<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debug('掲示板ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


$otherUserID = '';
$otherUserInfo = '';
$postUserInfo = '';
$photoInfo = '';
$comment = (isset($_POST['comment'])) ? $_POST['comment'] : '';
// //画面表示用データ取得
// //================================
// // GETパラメータを取得
$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';

// //DBから掲示板とメッセージデータを取得
$viewData = getMsgAndBord($m_id);
debug('ビュー：' . print_r($viewData, true));

//パラメータに不正な値が入っているかチェック;
if (empty($viewData)) {
  debug('エラー発生:指定ページに不正な値が取得入りました');
  header('Location:mypage.php'); //マイページへ
}

//投稿情報を取得
$photoInfo = getPhotoOne($viewData[0]['post_id']);
debug('$photoInfoの中身：' . print_r($photoInfo, true));

//投稿情報が入っているかチェック
if (empty($photoInfo)) {
  debug('エラー発生：投稿情報が取得できませんでした。');
  header('Location:mypage.php'); //マイページへ
}

//親ユーザーの情報を取得
$postUserId = $photoInfo['user_id'];
$postUserInfo = getUser($postUserId);
debug('$postUserInfoの中身：' . print_r($postUserInfo, true));

//そのほかのユーザー情報を取得
//viewDataの多次元配列からcomment_userの値であるユーザーIDを取得する。
$commentUserId = array_column($viewData, 'comment_user');
debug('commentUserId中身：' . print_r($commentUserId, true));

//コメントしているユーザーIDからポストユーザーIDを除外
while (($index = array_search($postUserId, $commentUserId, true)) !== false) {
  unset($commentUserId[$index]);
}
//otherUserIdを定義
$otherUserId = $commentUserId;
debug('otherUserIdの中身：' . print_r($otherUserId, true));

//コメントしたotherUserの情報を取得
$otherUserInfo = array();
foreach ($otherUserId as $key => $val) {
  $otherUserOne = getUser($val);
  $otherUserInfo[] =  $otherUserOne;
}
debug('$otherUserInfoの中身：' . print_r($otherUserInfo, true));




if (!empty($_POST)) {
  debug('POST送信があります。');

  //ログイン認証
  require('auth.php');

  //必須入力チェック
  validRequired($comment, 'comment');
  if (empty($err_msg)) {
    //最大文字数チェック
    validMaxLen($comment, 'comment', 400);
    if (empty($err_msg)) {
      //バリデーションOK
      try {
        debug('DBへコメントをインサート');
        $dbh = dbConnect();
        $sql = 'INSERT INTO message(bord_id, user_id, send_date, msg, create_at) VALUE(:bord_id, :user_id, :send_date, :msg, :create_at)';

        if ($_SESSION['user_id'] == $postUserId) {
          $data = array(':bord_id' => $m_id, ':user_id' => $postUserId, ':send_date' => date('Y-m-d H:i:s'), ':msg' => $comment, 'create_at' => date('Y-m-d H:i:s'));
        } else {
          $data = array(':bord_id' => $m_id, ':user_id' => $_SESSION['user_id'], ':send_date' => date('Y-m-d H:i:s'), ':msg' => $comment, 'create_at' => date('Y-m-d H:i:s'));
        }
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ成功の場合
        if ($stmt) {
          $_POST = array(); //POSTをクリア
          debug('連絡掲示板へ遷移します。');
          header('Location:' . $_SERVER['PHP_SELF'] . '?m_id=' . $m_id); //自分自身に遷移する。
        }
      } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = ERR_MSG06;
      }
    }
  }
}
debug('viewdataコメント：' . print_r($viewData[0]['post_user'], true));
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');



?>



<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.1/css/all.css" integrity="sha384-vp86vTRFVJgpjF9jiIGPEEqYqlDwgyBgEF109VFjmqGmIY/Y4HV4d3Gp2irVfcrp" crossorigin="anonymous">
  <link rel="stylesheet" href="reset.css">
  <link rel="stylesheet" href="style.css">
  <title>コメント</title>
</head>

<?php include_once 'header.php'; ?>

<div class="container">
  <div class="contant site-width">
    <h2 class="section-title">コメントページ</h2>
    <div class="img-outer img-detail-outer comment-photo">
      <img src="<?php echo $photoInfo['pic'];  ?>" class="prev-img">
    </div>
    <div class="iner-width">

      <div class="bord">
        <!-- 写真投稿者コメント -->
        <?php if (!empty($viewData[0]['comment_user'])) : ?>
          <?php foreach ($viewData as $key => $val) : ?>
            <?php if (!empty($val['post_user'] && $val['post_user'] == $val['comment_user'])) : ?>
              <div class="talk">
                <figure class="talk-Limg">
                  <img src="<?php echo $postUserInfo['pic']; ?>" alt="" />
                  <figcaption class="talk-imgname"><?php echo sanitize($postUserInfo['username']); ?></figcaption>
                </figure>
                <div class="talk-Ltxt">
                  <p class="talk-text"><?php echo sanitize($val['msg']); ?></p>
                </div>
              </div>
            <?php else : ?>

              <!-- そのほかのユーザーコメント -->
              <div class="talk">
                <figure class="talk-Rimg">
                  <img src="<?php echo $val['pic']; ?>" alt="" />
                  <figcaption class="talk-imgname"><?php echo sanitize($val['username']); ?></figcaption>
                </figure>
                <div class="talk-Rtxt">
                  <p class="talk-text"><?php echo sanitize($val['msg']); ?></p>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php else : ?>
          <p style="text-align:center;">メッセージ投稿はまだありません</p>
        <?php endif; ?>
      </div>



      <form action="" method="post">
        <span class="err-msg"><?php if (!empty($err_msg['comment'])) echo $err_msg['comment']; ?></span>
        <label for="" class="tag">コメント</label>
        <textarea name="comment" id="" cols="30" rows="10" class="regist-textare js-count-text"><?php echo getFormData('comment'); ?></textarea>
        <div><span class="js-count-view"></span>/400</div>
        <div class="regist-input comment-btn"><input type="submit" value="コメントする"></div>
      </form>
      <p class="return-page mb80"><a href="top.php<?php echo appendGetParam(array('p_id')); ?>">&lt;&lt; 写真詳細に戻る</a></p>

    </div>
  </div>

  <?php require('footer.php'); ?>
  </body>

</html>
