<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　Ajax　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// Ajax処理
//================================

//postがあり、ユーザーIDがあり、ログインしている場合
if(isset($_POST['photoid']) && isset($_SESSION['user_id']) && isLogin()){
  debug('POST送信があります。');
  $p_id = $_POST['photoid'];
  debug('写真ID：'.$p_id);
  //例外処理
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favorites WHERE post_id = :p_id AND user_id = :u_id';
    $data = array(':p_id'=>$p_id, ':u_id'=>$_SESSION['user_id']);
    //クエリ実行
    $stmt = queryPost($dbh,$sql,$data);
    $resultCount = $stmt->rowCount();
    debug($resultCount);
    if(!empty($resultCount)){
      $sql = 'DELETE FROM favorites WHERE post_id = :p_id AND user_id = :u_id';
      $data = array(':p_id'=>$p_id, ':u_id'=>$_SESSION['user_id']);
      //クエリ実行
      $stmt = queryPost($dbh,$sql,$data);
    }else{
      $sql = 'INSERT INTO favorites (post_id, user_id, create_at) VALUES(:p_id, :u_id, :create_at)';
      $data = array(':p_id'=>$p_id, ':u_id'=>$_SESSION['user_id'], ':create_at'=>date('Y-m-d H:i:s'));
      //クエリ実行
      $stmt = queryPost($dbh,$sql,$data);
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
debug('Ajax処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

?>