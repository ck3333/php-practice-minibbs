<?php
session_start();
require('../dbconnect.php');

if (!isset($_SESSION['join'])) {
  header('Location: index.php');
  exit();
}

if (!empty($_POST)) {
  // DBへの登録処理
  $statement = $db->prepare('INSERT INTO members SET name=?, email=?, password=?, picture=?, created=NOW()');
  echo $rel = $statement->execute(array(
    $_SESSION['join']['name'],
    $_SESSION['join']['email'],
    sha1($_SESSION['join']['password']), //暗号化。＊現在はsha1はおすすめされていない点に注意
    $_SESSION['join']['image'],
  ));
  unset($_SESSION['join']); //DB登録後、重複登録等を避けるためセッション削除

  header('Location: thanks.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>

	<link rel="stylesheet" href="../style.css" />
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>会員登録</h1>
  </div>
  <div id="content">

    <p>入力内容を確認してください。</p>
    <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="submit">

      <dl>
        <dt>ニックネーム<span class="required">必須</span></dt>
        <dd>
        <?php echo htmlspecialchars($_SESSION['join']['name'], ENT_QUOTES); ?>
        </dd>
        <dt>メールアドレス<span class="required">必須</span></dt>
        <dd>
        <?php echo htmlspecialchars($_SESSION['join']['email'], ENT_QUOTES); ?>
        </dd>
        <dt>パスワード<span class="required">必須</span></dt>
        <dd>【表示されません】</dd>
        <dt>写真など<span class="required">必須</span></dt>
        <dd>
        <img src="../member_picture/<?php echo htmlspecialchars($_SESSION['join']['image'], ENT_QUOTES); ?>" width="100" height="100" alt="">
        </dd>
      </dl>

      <div><a href="index.php?action=rewrite">&laquo;&nbsp;書き直す</a><input type="submit" value="登録する"></div>
    </form>

  </div>

</div>
</body>
</html>
