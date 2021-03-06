<?php
session_start();
session_regenerate_id(TRUE);
require('common.php');
require('dbconnect.php');

if (isset($_POST['token'], $_SESSION['token'])) {
  $token = $_POST['token'];
  if ($token !== $_SESSION['token']) {
    die('CSRFトークンが無効です。フォームを再送信してください');
  }
} else {
  header('location: login.php');
  exit();
}

$email = isset($_POST['email']) ? $_POST['email'] : NULL;
$password = isset($_POST['password']) ? $_POST['password'] : NULL;

$error = array();
if (mb_strlen($password) < 8) {
  $error['password'] = 'tooshort_length';
} elseif (mb_strlen($password) > 128) {
    $error['password'] = 'toolong_length';
}

if (!empty($email) && !empty($password)) {
  $login = $db->prepare('SELECT * FROM users WHERE email=? AND password=?');
  $login->execute([
    $email,
    sha1($password)
  ]);
  $user = $login->fetch();

  if ($user) {
    $_SESSION['id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['time'] = time();
    $_SESSION['save'] = $_POST['save'];

    $two_week = 60*60*24*14;

    if ($_POST['save'] === 'on') {
      setcookie('email', $email,time()+$two_week);
      setcookie('password', $password, time()+$two_week);
    } else {
      setcookie('email', '', time()-3600);
      setcookie('password', '', time()-3600);
    }

    unset($_SESSION['email'], $_SESSION['password'] ,$_SESSION['error'], $_SESSION['token']);

  } else {
    $error['login'] = 'false';
    $_SESSION['email'] = $email;
    $_SESSION['password'] = $password;
    $_SESSION['error'] = $error;
  }
}

if (count($error) > 0) {
  header('Location: login.php');
  exit();
}

if (isset($_SESSION['id'])) {
  $login_out_url = 'logout.php';
  $login_out = 'ログアウト';
  $login_user = '<a href="login_m.php" class="login_user">' .$_SESSION['name'] . '</a>';
  $one_month = 3600*24*30;
  $one_day = 3600*24;
  
  if ($_SESSION['save'] === 'on' && $_SESSION['time'] + $one_month > time()) {
    $_SESSION['time'] = time();
  } elseif ($_SESSION['time'] + $one_day > time()) {
    $_SESSION['time'] = time();
  } else {
    header('Location: logout.php');
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
  <!-- Bootstrap core CSS -->
  <link rel="stylesheet" href="join/css/bootstrap.min.css">
  <!-- Material Design Bootstrap -->
  <link rel="stylesheet" href="join/css/mdb.min.css">
  <!-- Custom styles -->
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/login.css">
  <link rel="stylesheet" href="css/responsive.css" media="screen and (max-width: 1000px)">
  <title>ログイン完了</title>
</head>

<body>
<header>
  <div class="container">
    <div class="header-title">
      <div id="top-btn" class="header-logo"><a href="index.php">最安値検索<span id="shop">（Yahoo!ショッピング＆楽天市場）</span></a></div>
    </div>
    <div id="wrapper">
      <p class="btn-gnavi">
        <span></span>
        <span></span>
        <span></span>
      </p>        
      <div class="header-menu" id="global-navi">
        <ul class="header-menu-right">
          <li>
            <a href="<?php echo $login_out_url; ?>"><?php echo $login_out; ?></a>
          </li>
          <?php if (!isset($_SESSION['id'])): ?>
          <li>
            <a href="join/index.php">新規会員登録</a>
          </li>
          <?php endif; ?>
          <li>
            <a href="contact/index.php">お問い合わせ</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</header>

<main>
<div class="user_jc container">ようこそ、<strong><?php echo $login_user; ?></strong>さん</div>
  <div class="content">
    <h1 class="text-center">ログイン</h1>
    <div class="card mt-3">
      <div class="card-body text-center mail_box">
        <h2 class="h3 card-title text-center mt-2">ようこそ<?php echo $_SESSION['name']; ?>さん！</h2>
        <div id="content">
          <p>ログインが完了しました</p>
          <p>
            <button class="btn btn-primary mt-2 mb-2" type="button" onclick="location.href='index.php'">
              トップへ戻る
            </button>
          </p>
          <!-- <p>
            <button class="btn btn-blue-grey mt-2 mb-2" type="button" onclick="location.href='login_m.php'">
              ログイン情報を編集する
            </button>
          </p> -->
        </div>
      </div>
    </div>
  </div>
</main>

<footer>
  <div class="copyright">&copy; 2020<?php if( date('Y') > "2020") {echo "-".date('Y');}?> Samua</div>
</fotter>

  <!-- jQuery -->
  <script src="join/js/jquery.min.js"></script>
  <!-- Bootstrap tooltips -->
  <script src="join/js/popper.min.js"></script>
  <!-- Bootstrap core JavaScript -->
  <script src="join/js/bootstrap.min.js"></script>
  <!-- MDB core JavaScript -->
  <script src="join/js/mdb.min.js"></script>
  <script src="js/script.js"></script>

</body>
</html>