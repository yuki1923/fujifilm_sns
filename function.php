<?php
error_reporting(E_ALL);
ini_set('display', 'on');
ini_set('error_log', 'error.log');
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$debug_flg = true;

//デバッグ関数を定義
function debug($str)
{
    global $debug_flg;
    if (!empty($debug_flg)) {
        error_log('デバッグ：' . $str);
    }
}

session_save_path('/var/tmp');
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
session_start();

session_regenerate_id();

function debugLogStart()
{
    debug('debugLogStartはじまり');
    debug('>>>>>>>>>>>>>>>>>画面表示処理開始');
    debug('セッションID：' . session_id());
    debug('セッション変数の中身：' . print_r($_SESSION, true));
    debug('現在日時タイムスタンプ：' . time());
    if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
        debug('ログイン期限日時タイムスタンプ：' . ($_SESSION['login_date'] + $_SESSION['login_limit']));
    }
}




define('ERR_MSG01', '入力必須です。');
define('ERR_MSG02', 'メールアドレスの形式ではありません。');
define('ERR_MSG03', '半角英数字で入力してください。');
define('ERR_MSG04', '6文字以上で入力してください。');
define('ERR_MSG05', 'パスワードと再入力が違います。');
define('ERR_MSG06', 'エラーが発生しました。しばらく経ってからやり直してください。');
define('ERR_MSG07', '既に登録されているメールアドレスです。');
define('ERR_MSG08', 'メールアドレスまたはパスワードが異なります。');
define('ERR_MSG09', '半角数字で入力してください。');
define('ERR_MSG10', '古いパスワードと同じものは使用できません。');
define('ERR_MSG11', '登録されているパスワードと異なります。');
define('ERR_MSG12', 'このメールアドレスは登録されていません。');
define('ERR_MSG13', '認証キーの有効期限が切れています。再度認証キーを発行してください。');
define('ERR_MSG14', '認証キーが異なります。');
define('ERR_MSG15', '写真が選択されていません。');
define('ERR_MSG16', '該当する写真がありませんでした。');



function dbConnect()
{
    $dsn = $_ENV['DB_CONNECTION'] . ':dbname=' . $_ENV['DB_DATABASE'] . ';host=' . $_ENV['DB_HOST'] . ';charset=utf8';
    $user = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];
    $opptions = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );

    $dbh = new PDO($dsn, $user, $password, $opptions);
    return $dbh;
}

function queryPost($dbh, $sql, $data)
{
    global $err_msg;
    $stmt = $dbh->prepare($sql);
    if (!$stmt->execute($data)) {
        //sql文のエラーを出力
        debug("\nPDOStatement::errorInfo():\n");
        $arr = $stmt->errorInfo();
        debug(print_r($arr, true));
        debug('クエリに失敗しました');
        debug('失敗したSQL：' . print_r($stmt, true));
        $err_msg['common'] = ERR_MSG06;
        return false;
    } else {
        return $stmt;
    }
}

$err_msg = array();

//入力チェック
function validRequired($str, $key)
{
    if (empty($str)) {
        global $err_msg;
        return $err_msg[$key] = ERR_MSG01;
    }
}

//E-mail形式チェック
function validEmail($email, $key)
{
    global $err_msg;
    if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\?\*\[|\]%'=~^\{\}\/\+!#&\$\._-])*@([a-zA-Z0-9_-])+\.([a-zA-Z0-9\._-]+)+$/", $email)) {
        $err_msg[$key] = ERR_MSG02;
    }
}


function validEmailDup($email)
{
    global $err_msg;
    try {
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($result["count(*)"])) {
            $err_msg['email'] = ERR_MSG07;
        }
    } catch (Exception $e) {
        error_log('エラー発生；' . $e->getMessage());
        $err_msg['common'] = ERR_MSG06;
    }
}

//半角英数字チェック
function validHalf($str, $key)
{
    if (!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
        global $err_msg;
        $err_msg[$key] = ERR_MSG03;
    }
}

//半角数字チェック
function validNum($str, $key)
{
    global $err_msg;
    if (!preg_match("/^[0-9]+$/", $str)) {
        $err_msg[$key] = ERR_MSG09;
    }
}
//最小文字数チェック
function validMinLen($str, $key, $min = 6)
{
    if (mb_strlen($str, 'UTF-8') < $min) {
        global $err_msg;
        $err_msg[$key] = $min . '文字以上で入力してください。';
    }
}

//最大文字数チェック
function validMaxLen($str, $key, $max = 255)
{
    if (mb_strlen($str, 'UTF-8') > $max) {
        global $err_msg;
        $err_msg[$key] = $max . '文字以内で入力してください。';
    }
}

//パスワード重複チェック
function validPassDup($oldPass, $newPass, $newPassKeyStr)
{
    global $err_msg;
    if ($oldPass === $newPass) {
        $err_msg[$newPassKeyStr] = ERR_MSG10;
    }
}

//パスワードチェック
function validPass($str, $key)
{
    validHalf($str, $key);
    validMaxLen($str, $key);
    validMinLen($str, $key);
}

//同値チェック
function validMatch($str1, $str2, $key)
{
    global $err_msg;
    if ($str1 !== $str2) {
        $err_msg[$key] = ERR_MSG05;
    }
}

//ユーザー情報を取得
function getUser($u_id)
{
    global $err_msg;
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            debug('ユーザー情報取得成功');
        } else {
            debug('ユーザー情報取得失敗');
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $err_msg['common'] = ERR_MSG06;
    }
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

//フォーム入力保持
function getFormData($str, $flg = false)
{
    if ($flg) {
        $method = $_GET;
    } else {
        $method = $_POST;
    }
    global $dbFormData;
    //ユーザーデータがある場合
    if (!empty($dbFormData)) {
        //データベースにデータがあった場合　既に一度登録したことがある
        if (!empty($err_msg[$str])) {
            //データベースにデータがあり、ポストした値にエラーがあった場合
            if (isset($method[$str])) {
                return sanitize($method[$str]);
            } else {
                //エラーがあったがポストはしていなかった場合（ありえないが）
                return sanitize($dbFormData[$str]);
            }
        } else {
            if (isset($method[$str]) !== $dbFormData[$str]) {
                //ポストされたフォーム（名前部分のみなど）にエラーはなかった場合
                return sanitize($method[$str]);
            } else { //そもそも変更していない
                return sanitize($dbFormData[$str]);
            }
        }
    } else {
        //データベースにデータがない場合 未入力の場合
        if (isset($method[$str])) {
            return sanitize($method[$str]);
        }
    }
}

//都道府県データ取得関数
function getPrefecture()
{
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM prefecture';
        $data = array();
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetchALL();
        return $result;
        debug('都道府県データ取得ok');
    } catch (Exception $e) {
        error_log($e->getMessage());
        $err_msg['common'] = ERR_MSG06;
    }
}

//メール送信関数
function sendMail($from, $to, $subject, $comment)
{
    if (!empty($to) && !empty($subject) && !empty($comment)) {
        //お決まりの設定
        mb_language('japanese');
        mb_internal_encoding('UTF-8');
        //formは送信元をどこにするか。指定しなければデフォルト設定のアドレスになる。
        $result = mb_send_mail($to, $subject, $comment, 'from:' . $from);
        if ($result) {
            debug('メール送信成功');
        } else {
            debug('メール送信失敗');
        }
    }
}

//エラー表示関数
function getErrMsg($key)
{
    global $err_msg;
    if (!empty($err_msg[$key])) {
        return $err_msg[$key];
    }
}

//認証キー作成関数
function randomStr($length = 8)
{
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, $length);
}

//投稿した写真情報を取得する関数
function getPhoto($u_id, $photo_id)
{
    global $err_msg;
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM posts WHERE user_id = :u_id AND id = :photo_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id, ':photo_id' => $photo_id);
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            return $err_msg;
        }
        debug('写真情報取得クエリ成功');
        return $result;
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = ERR_MSG06;
    }
}

//レンズカテゴリ取得関数
function getLenses()
{
    global $err_msg;
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM lenses WHERE delete_flg = 0';
        $data = array();
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            debug('レンズ情報取得クエリ成功');
            return $stmt->fetchALL();
        } else {
            debug('レンズ情報取得クエリ失敗');
            $err_msg['common'] = ERR_MSG06;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = ERR_MSG06;
    }
}

//シチュエーションカテゴリ取得関数
function getSituation()
{
    global $err_msg;
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM situations WHERE delete_flg = 0';
        $data = array();
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            debug('シチュエーション情報取得クエリ成功');
            return $stmt->fetchALL();
        } else {
            debug('シチュエーション情報取得クエリ失敗');
            $err_msg['common'] = ERR_MSG06;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = ERR_MSG06;
    }
}

//フイルムシミュレーションカテゴリ取得関数
function getFilm()
{
    global $err_msg;
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM films WHERE delete_flg = 0';
        $data = array();
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            debug('フィルムシミュレーション情報取得クエリ成功');
            return $stmt->fetchALL();
        } else {
            debug('フィルムシミュレーション情報取得クエリ失敗');
            $err_msg['common'] = ERR_MSG06;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = ERR_MSG06;
    }
}

function uploadImg($file, $key)
{
    if (isset($file['error']) && is_int($file['error'])) {
        try {
            switch ($file['error']) {
                case  UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException('ファイルが選択されていません。');
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('ファイルサイズが大きすぎます。');
                default:
                    throw new RuntimeException('そのほかのエラーが発生しました。');
            }
            $type = @exif_imagetype($file['tmp_name']);
            debug('typeの中身；' . print_r($type, true));
            if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
                debug('画像ファイルではない');
                throw new RuntimeException('画像形式が未対応です。');
            }

            $path = 'uploads/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);
            if (!move_uploaded_file($file['tmp_name'], $path)) {
                throw new RuntimeException('ファイル保存時にエラーが発生しました。');
            }

            chmod($path, 0644);
            debug('ファイルは正常にアップロードされました。');
            debug('ファイルパス' . $path);
            return $path;
        } catch (RuntimeException $e) {
            debug($e->getMessage());
            global $err_msg;
            $err_msg[$key] = $e->getMessage();
        }
    }
}

function validSelect($str, $key)
{
    global $err_msg;
    if (empty($str)) {
        $err_msg[$key] = '選択必須です。';
    }
}

function validPic($pic, $key)
{
    global $err_msg;
    if (empty($pic === null)) {
        $err_msg['pic'] = ERR_MSG15;
    }
}

//DBに保存された投稿を取得
function getPostList($currentMinNum = 1, $lens, $situ, $film, $listSpan = 30)
{
    global $err_msg;
    debug('写真情報を取得します。');
    try {
        $dbh = dbConnect();

        //件数用のSQLを実行
        $sql = 'SELECT id FROM posts';
        debug('sqlの中身0：' . print_r($sql, true));
        $where = '';
        debug('whereの中身0：' . print_r($where, true));
        if (!empty($lens) || !empty($situ) || !empty($film)) {
            $where = ' WHERE';
        }
        if (!empty($lens)) {
            $where .= ' lens_id = ' . $lens . ' AND';
        }
        if (!empty($situ)) {
            $where .= ' situation_id = ' . $situ . ' AND';
        }
        if (!empty($film)) {
            $where .= ' film_id = ' . $film . ' AND';
        }
        debug('whereの中身0.5：' . print_r($where, true));
        $where = mb_substr($where, 0, -3, "UTF-8");
        debug('whereの中身1：' . print_r($where, true));
        $sql = $sql . $where;
        debug('sqlの中身1：' . print_r($sql, true));
        $data = array();

        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $result['total'] = $stmt->rowCount(); //rowCountは行数を返す 全データの件数を取得
        debug("トータル件数：" . print_r($result['total'], true));
        //取得した件数を１ページの表示件数で除して、端数を切り上げることで総ページ数を取得
        $result['total_page'] = ceil($result['total'] / $listSpan);
        debug("トータルページ数：" . print_r($result['total_page'], true));
        if (!$stmt) {
            return false;
        }

        //絞り込み検索
        //$lensは$_GET['lens']の値
        $sql = 'SELECT * FROM posts';
        debug('sqlの中身2：' . print_r($sql, true));
        debug('whereの中身2：' . print_r($where, true));
        if (!empty($lens) || !empty($situ) || !empty($film)) {
            $where = ' WHERE';
        }
        if (!empty($lens)) {
            $where .= ' lens_id = ' . $lens . ' AND';
        }
        if (!empty($situ)) {
            $where .= ' situation_id = ' . $situ . ' AND';
        }
        if (!empty($film)) {
            $where .= ' film_id = ' . $film . ' AND';
        }
        debug('whereの中身2.5：' . print_r($where, true));
        $where = mb_substr($where, 0, -3, "UTF-8");
        debug('whereの中身3：' . print_r($where, true));
        $sql = $sql . $where;
        debug('sqlの中身3：' . print_r($sql, true));
        $data = array();



        $sql .= ' LIMIT ' . $listSpan . ' OFFSET ' . $currentMinNum;
        debug('sqlの中身4：' . print_r($sql, true));
        $stmt = $dbh->prepare($sql);
        // $stmt->bindValue(':listSpan',$listSpan,PDO::PARAM_INT);
        // $stmt->bindValue(':currentMinNum',$currentMinNum,PDO::PARAM_INT);
        $stmt->execute();
        debug('実行したSQL：' . print_r($stmt, true));
        if ($stmt) {
            $result['data'] = $stmt->fetchALL();
            return $result;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}

//サニタイズ
function sanitize($str)
{
    return htmlspecialchars($str, ENT_QUOTES);
}

//getパラメータの削除
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array())
{
    $str = '?';
    if (!empty($_GET)) {
        foreach ($_GET as $key => $val) {
            if (!in_array($key, $arr_del_key, true)) {
                $str .= $key . '=' . $val . '&';
            }
        }
        $str = mb_substr($str, 0, -1, "UTF-8"); //最後につく＆を削除してそれ以外を残す。
        return $str;
    }
}

//特定の写真情報を取得
function getPhotoOne($p_id)
{
    debug('写真情報を取得します');
    debug('写真ID：' . print_r($p_id, true));

    try {
        $dbh = dbConnect();
        //条件を絞り込んだ検索をした時に表示されるようにしている。
        $sql = 'SELECT p.id as post_id, p.pic, p.title, p.comment, p.create_at, p.update_at, u.id as user_id, u.username,
        u.pic as user_icon, s.name as situation_name, l.name as lens_name, f.name as film_name
        FROM posts as p
        LEFT JOIN
        users as u
        ON
        p.user_id = u.id
        LEFT JOIN
        situations as s
        ON
        p.situation_id = s.id
        LEFT JOIN
        lenses as l
        ON
        p.lens_id = l.id
        LEFT JOIN
        films as f
        ON
        p.film_id = f.id
        WHERE p.id = :p_id AND p.delete_flg = 0 AND u.delete_flg = 0';

        $data = array(':p_id' => $p_id);
        debug('結合したdataの中身：' . print_r($data, true));
        $stmt = queryPost($dbh, $sql, $data);
        debug('結合したstmtの中身：' . print_r($stmt, true));
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            debug('ポスト情報取得失敗');
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}


//SQLテスト
// SELECT p.id as post_id, p.pic, p.title, p.comment, p.create_at, p.update_at, u.id as user_id, u.username,
// u.pic as user_icon, s.name as situation_name, l.name as lens_name, f.name as film_name
// FROM posts as p
// INNER JOIN
// users as u
// ON
// p.user_id = u.id
// INNER JOIN
// situations as s
// ON
// p.situation_id = s.id
// INNER JOIN
// lenses as l
// ON
// p.lens_id = l.id
// INNER JOIN
// films as f
// ON
// p.film_id = f.id
// WHERE p.id = :p_id AND p.delete_flg = 0 AND u.delete_flg = 0 AND s.delete_flg = 0 AND l.delete_flg = 0 AND f.delete_flg = 0



// SELECT p.id, p.pic, p.title, p.comment, p.create_at, p.update_at, u.id as user_id, u.username,
// u.pic as user_icon, s.name as situation_name, l.name as lens_name, f.name as film_name
// FROM posts as p
// LEFT JOIN
// users as u
// ON
// p.user_id = u.id

//メッセージ取得
function getMsgAndBord($id)
{
    try {
        $dbh = dbConnect();
        $sql = 'SELECT m.user_id AS comment_user, m.send_date, m.msg, m.bord_id, b.post_id, b.post_user, b.create_at, u.username, u.pic FROM message AS m RIGHT JOIN bord AS b ON m.bord_id = b.id LEFT JOIN users AS u ON m.user_id = u.id WHERE b.id = :id ORDER BY m.send_date ASC';
        $data = array(':id' => $id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            debug('ここに入ればok');
            return $stmt->fetchAll();
        } else {
            debug('ここかな');
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}

//ログイン関数
function isLogin()
{
    if (!empty($_SESSION['login_date'])) {
        debug('ログイン済みユーザーです');
        if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
            debug('ログイン有効期限切れです。');
            //セッションを削除（ログアウトする）
            session_destroy();
            return false;
        }
        debug('ログイン有効期限内です。');
        return true;
    } else {
        debug('未ログインユーザー');
        return false;
    }
}

//いいね機能
function isFav($u_id, $p_id)
{
    debug('お気に入り情報があるかチェック');
    debug('ユーザーID：' . $u_id);
    debug('写真ID：' . $p_id);
    //例外処理
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM favorites WHERE post_id = :p_id AND  user_id = :u_id';
        $data = array(':p_id' => $p_id, ':u_id' => $u_id);
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt->rowCount()) {
            debug('お気に入りです。');
            return true;
        } else {
            debug('お気に入りではありません。');
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}

//ログインしているユーザーの投稿した全写真を取得 マイページで使用
function getAllMyPost($u_id)
{
    if (!empty($u_id)) {
        try {
            $dbh = dbConnect();
            $sql = 'SELECT id, user_id, pic, title FROM posts WHERE user_id = :u_id';
            $data = array(':u_id' => $u_id);

            $stmt = queryPost($dbh, $sql, $data);

            if ($stmt) {
                debug('あなたの投稿した全写真を取得できました。');
                return $stmt->fetchALL();
            } else {
                debug('投稿した写真を取得できませんでした。');
                return false;
            }
        } catch (Exception $e) {
            error_log('エラー発生：' . $e->getMessage());
        }
    }
    debug('ユーザーIDがわかりません');
}

//マイページでお気に入りにいれている画像を表示
function getFavPhoto($u_id)
{
    if (!empty($u_id)) {
        try {
            $dbh = dbConnect();
            $sql = 'SELECT p.id, p.user_id, p.pic, p.title FROM posts as p INNER JOIN favorites as f ON f.post_id = p.id WHERE f.user_id = :u_id';
            $data = array(':u_id' => $u_id);

            $stmt = queryPost($dbh, $sql, $data);

            if ($stmt) {
                debug('あなたの投稿した全写真を取得できました。');
                return $stmt->fetchALL();
            } else {
                debug('投稿した写真を取得できませんでした。');
                return false;
            }
        } catch (Exception $e) {
            error_log('エラー発生：' . $e->getMessage());
        }
    }
    debug('ユーザーIDがわかりません');
}
