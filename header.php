<body>
  <!-- メインヘッダー -->
  <header class="main-header">
    <div class="outer-g-nav">
      <h1 class="logo"><img src="" alt="">logo</h1>
      <nav class="g-nav">
        <ul class="g-nav-list">
          <li class="g-nav-item"><a href="signup.php">新規登録</a></li>
          <li class="g-nav-item"><a href="login.php">ログイン</a></li>
        </ul>
      </nav>
    </div>
  </header>
  <?php if (!empty($_SESSION['user_id'])) : ?>
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
  <?php endif; ?>
