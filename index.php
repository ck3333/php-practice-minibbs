<?php
session_start();
require('dbconnect.php');

// ログインチェック：idがセッションに記録されている＆最後の行動から1時間以内
if ( isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time() ) {
  // ログインしている
  $_SESSION['time'] = time(); //現在の時間に上書き

  $members = $db->prepare('SELECT * FROM members WHERE id=?');
  $members->execute( array($_SESSION['id']) );
  $member = $members->fetch();
} else {
  // ログインしていない
  header('Location: login.php');
  exit();
}

// 投稿をDBに記録する
if (!empty($_POST)) {
  if ($_POST['message'] != '') {
    // reply_post_idに自動で0つかないのでif
    if ( isset($_GET['res']) ) {
      $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_post_id=?, created=NOW()');
      $message->execute(array(
        $member['id'],
        $_POST['message'],
        $_POST['reply_post_id']
      ));
    } else {
      $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_post_id=0, created=NOW()');
      $message->execute(array(
        $member['id'],
        $_POST['message'],
      ));
    }

    header('Location: index.php'); //リロードによる投稿重複を防ぐ
    exit();
  }
}

// 投稿を取得する
$posts = $db->query('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC');

// 返信の場合
if ( isset($_GET['res']) ) {
  $response = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=? ORDER BY p.created DESC');
  $response->execute(array($_GET['res']));

  $table = $response->fetch();
  $message = '@' . $table['name'] . ' ' . $table['message'];
}

// htmlspecialcharsのショートカット
function h($value) {
  return htmlspecialchars($value, ENT_QUOTES);
}

?>


<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>

	<link rel="stylesheet" href="style.css" />
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>ひとこと掲示板</h1>
  </div>
  <div id="content">

    <form action="" method="post">

      <dl>
        <dt><?php echo htmlspecialchars($member['name'], ENT_QUOTES); ?>さん、メッセージをどうぞ</dt>
        <dd>
          <textarea name="message" cols="50" rows="5"><?php if(isset($message)): echo h($message); endif; ?></textarea>
          <input type="hidden" name="reply_post_id" value="<?php echo h($_GET['res']); ?>">
        </dd>
      </dl>

      <div><input type="submit" value="投稿する"></div>
    </form>

    <?php foreach( $posts as $post ) : ?>
    <div class="msg">
      <img src="member_picture/<?php echo h($post['picture']); ?>" wudtg="48" height="48" alt="<?php echo h($post['name']); ?>">
      <p>
        <?php echo h($post['message']); ?>
        <span class="name">(<?php echo h($post['name']); ?>)</span>
        [<a href="index.php?res=<?php echo h($post['id']); ?>">Re</a>]
      </p>
      <p class="day">
        <a href="view.php?id=<?php echo h($post['id']); ?>">
          <?php echo h($post['created']);  ?>
        </a>
        <?php if ($post['reply_post_id'] > 0): ?>
        |
        <a href="view.php?id=<?php echo h($post['reply_post_id']); ?>">
          返信元のメッセージへ
        </a>
        <?php endif; ?>
      </p>
    </div>
    <?php endforeach; ?>

  </div>

</div>
</body>
</html>
