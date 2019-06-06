<?php
require('dbconnect.php');

session_start();

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
    $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_post_id=?, created=NOW()');
    $message->execute(array(
      $member['id'],
      $_POST['message'],
      $_POST['reply_post_id']
    ));

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
          <textarea name="message" cols="50" rows="5"><?php if(isset($message)): echo htmlspecialchars($message, ENT_QUOTES); endif; ?></textarea>
          <input type="hidden" name="reply_post_id" value="<?php echo htmlspecialchars($_GET['res'], ENT_QUOTES); ?>">
        </dd>
      </dl>

      <div><input type="submit" value="投稿する"></div>
    </form>

    <?php foreach( $posts as $post ) : ?>
    <div class="msg">
      <img src="member_picture/<?php echo htmlspecialchars($post['picture'], ENT_QUOTES); ?>" wudtg="48" height="48" alt="<?php echo htmlspecialchars($post['name'], ENT_QUOTES); ?>">
      <p>
        <?php echo htmlspecialchars($post['message'], ENT_QUOTES); ?>
        <span class="name">(<?php echo htmlspecialchars($post['name'], ENT_QUOTES); ?>)</span>
        [<a href="index.php?res=<?php echo htmlspecialchars($post['id'], ENT_QUOTES); ?>">Re</a>]
      </p>
      <p class="day"><?php echo htmlspecialchars($post['created'], ENT_QUOTES);  ?></p>
    </div>
    <?php endforeach; ?>

  </div>

</div>
</body>
</html>
