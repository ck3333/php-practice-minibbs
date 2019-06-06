<?php
session_start();

// セッション情報を削除

// セッション変数を全て解除
$_SESSION = array();

// セッションを切断するにはセッションクッキーも削除する。
// Note: セッション情報だけでなくセッションを破壊する。
if ( ini_get("session.use_cookies") ) {
  $params = session_get_cookie_params(); //現在のセッションクッキーの情報を配列として返す
  // "lifetime" - クッキーの生存期間(lifetime)
  // "path" - 情報が保存されている場所のパス
  // "domain" - クッキーのドメイン
  // "secure" - クッキーはセキュアな接続でのみ送信されます。
  // "httponly" - クッキーは HTTP を通してのみアクセス可能となります。
  setcookie(session_name(), '', time() - 420000,
    $params['path'], $params['domain'],
    $params['secure'], $params['httponly']
  );
}

// 最終的に、セッションを破壊する
session_destroy();


// Cookie情報も削除
// 空の内容を記憶し、有効期限を過去に設定
setcookie('email', '', time()-3600);
setcookie('password', '', time()-3600);


header('Location: login.php');
exit();
?>