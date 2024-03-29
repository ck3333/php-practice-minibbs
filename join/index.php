<?php
session_start();
require('../dbconnect.php');

if (!empty($_POST)) { //$_POSTが空でない = 送信された場合、
  // エラー項目の確認
  if ( $_POST['name'] == '' ) {
    $error['name'] = 'blank';
  }
  if ( $_POST['email'] == '' ) {
    $error['email'] = 'blank';
  }
  if ( strlen($_POST['password']) < 4 ) {
    $error['password'] = 'length';
  }
  if ( $_POST['password'] == '' ) {
    $error['password'] = 'blank';
  }
  // 画像チェック
  $fileName = $_FILES['image']['name'];
  if (!empty($fileName)) {
    $ext = substr($fileName, -3);
    if ($ext != 'jpg' && $ext != 'png' && $ext != 'gif' && $ext != 'svg') {
      $error['image'] = 'type';
    }
  }

  // 重複アカウントのチェック
  if ( empty($error) ) {
    $member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE email=?');
    $member->execute(array($_POST['email']));
    $record = $member->fetch();
    if ($record['cnt'] > 0) {
      $error['email'] = 'duplicate';
    }
  }

  // エラー項目がなければ
  if ( empty($error) ) {
    // 画像をアップロードする
    $image = date('YmdHis') . $fileName;
    move_uploaded_file($_FILES['image']['tmp_name'], '../member_picture/'. $image);

    $_SESSION['join'] = $_POST;
    $_SESSION['join']['image'] = $image;
    header('Location: check.php');
    exit();
  }
}

// 書き直しの場合
if ( isset($_GET['action']) && $_GET['action'] == 'rewrite' ) {
  // $_POSTに$_SESSION['join']の内容を書き戻し（入力内容を再現）
  $_POST = $_SESSION['join'];
  // 写真の再指定メッセージを表示するために空のエラー
  $error['rewrite'] = true;
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

    <p>次のフォームに必要事項をご記入ください。</p>
    <form action="" method="post" enctype="multipart/form-data">
      <dl>

        <dt>ニックネーム<span class="required">必須</span></dt>
        <dd>
          <input type="text" name="name" size="35" maxlength="255" value="<?php if ( isset($_POST['name']) ) : echo htmlspecialchars($_POST['name'], ENT_QUOTES); endif; ?>">
          <?php if ( isset($error['name']) && $error['name'] == 'blank' ) : ?>
          <p class="error">* ニックネームを入力してください</p>
          <?php endif; ?>
        </dd>

        <dt>メールアドレス<span class="required">必須</span></dt>
        <dd>
          <input type="text" name="email" size="35" maxlength="255" value="<?php if ( isset($_POST['email']) ) : echo htmlspecialchars($_POST['email'], ENT_QUOTES); endif; ?>">
          <?php if ( isset($error['email']) && $error['email'] == 'blank' ) : ?>
          <p class="error">* メールアドレスを入力してください</p>
          <?php endif; ?>
          <?php if ( isset($error['email']) && $error['email'] == 'duplicate' ) : ?>
          <p class="error">* 指定されたメールアドレスは既に登録されています</p>
          <?php endif; ?>
        </dd>

        <dt>パスワード<span class="required">必須</span></dt>
        <dd>
          <input type="text" name="password" size="10" maxlength="20" value="<?php if ( isset($_POST['password']) ) : echo htmlspecialchars($_POST['password'], ENT_QUOTES); endif; ?>">
          <?php if ( isset($error['password']) && $error['password'] == 'blank' ) : ?>
          <p class="error">* パスワードを入力してください</p>
          <?php endif; ?>
          <?php if ( isset($error['password']) && $error['password'] == 'length' ) : ?>
          <p class="error">* パスワードは４文字以上で入力してください</p>
          <?php endif; ?>
        </dd>

        <dt>写真など</dt>
        <dd>
          <input type="file" name="image" size="35">
          <?php if ( isset($error['image']) && $error['image'] == 'type' ) : ?>
          <p class="error">* 写真の拡張子を確認してください</p>
          <?php endif; ?>
          <?php if ( !empty($error) ) : ?>
          <p class="error">* 恐れ入りますが、画像を改めて指定してください</p>
          <?php endif; ?>
        </dd>
      </dl>

      <div><input type="submit" value="入力内容を確認する"></div>
    </form>

    <p style="margin-top: 40px;"><a href="../login.php">ログインする</a></p>

  </div>

</div>
</body>
</html>
