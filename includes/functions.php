<?php
include_once "config.php";

function get_url($page = '') {
  return HOST . "/$page";
}

function get_page_title($title = '') {
  if (!empty($title)) {
    return SITE_NAME . " - $title";
  } else {
    return SITE_NAME;
  }
}

function redirect($link = HOST) {
  header("Location: $link");
  die;
}

function db() {
  try {
    return new PDO(
      "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
      DB_USER,
      DB_PASS,
      [
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
      ]
    );
  } catch (PDOException $e) {
    die($e->getMessage());
  }
}

function db_query($sql, $exec = false) {
  if (empty($sql)) return false;

  if ($exec) return db()->exec($sql);

  return db()->query($sql);
}

function get_posts($user_id = 0, $sort = false) {
  $sorting = 'DESC';
  if ($sort) $sorting = 'ASC';

  return db_query("SELECT posts.*, users.name, users.login, users.avatar FROM `posts` JOIN `users` ON users.id = posts.user_id WHERE $user_id = 0 or $user_id > 0 and users.id = $user_id ORDER BY posts.date $sorting;")->fetchAll();
}

function get_user_info($login) {
  return db_query("SELECT * FROM `users` WHERE login = '$login';")->fetch();
}

function add_user($login, $pass) {
  $login = trim($login);
  $name = ucfirst($login);
  $password = password_hash($pass, PASSWORD_DEFAULT);

  return db_query("INSERT INTO `users` (id, login, pass, name) VALUES (NULL, '$login', '$password', '$name');", true);
}

function register_user($auth_data) {
  if (
    empty($auth_data) ||
    !isset($auth_data['login']) || empty($auth_data['login']) ||
    !isset($auth_data['pass']) || empty($auth_data['pass']) ||
    !isset($auth_data['pass2']) || empty($auth_data['pass2'])
  ) {
    return false;
  }

  $user = get_user_info($auth_data['login']);
  if (!empty($user)) {
    $_SESSION['error'] = 'Пользователь ' . $auth_data['login'] . ' уже существует';
    redirect(get_url('register.php'));
  }

  if ($auth_data['pass'] !== $auth_data['pass2']) {
    $_SESSION['error'] = 'Пароли не совпадают';
    redirect(get_url('register.php'));
  }

  if (add_user($auth_data['login'], $auth_data['pass'])) {
    redirect(get_url());
  }
}

function login($auth_data) {
  if (
    empty($auth_data) ||
    !isset($auth_data['login']) || empty($auth_data['login']) ||
    !isset($auth_data['pass']) || empty($auth_data['pass'])
  ) {
    return false;
  }

  $user = get_user_info($auth_data['login']);

  if (empty($user)) {
    $_SESSION['error'] = 'Пользователь ' . $auth_data['login'] . ' не найден';
    redirect(get_url());
  }

  if (password_verify($auth_data['pass'], $user['pass'])) {
    $_SESSION['user'] = $user;
    $_SESSION['erroe'] = '';
    redirect(get_url('user_posts.php?id=' . $user['id']));
  } else {
    $_SESSION['error'] = 'Неверный пароль';
    redirect(get_url());
  }
}

function get_error_message() {
  $error = '';
  if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
    $error = $_SESSION['error'];
    $_SESSION['error'] = '';
  }

  return $error;
}

function logged_in() {
  return isset($_SESSION['user']['id']) && !empty($_SESSION['user']['id']);
}

function add_post($text, $image) {
  $text = trim($text);
  if (mb_strlen($text) > 255) {
    $text = mb_substr($text, 0, 250) . ' ...';
  }

  $user_id = $_SESSION['user']['id'];
  $sql = "INSERT INTO `posts` (user_id, text, image) VALUES ($user_id, '$text', '$image');";
  return db_query($sql, true);
}

function delete_post($id) {
  $user_id = $_SESSION['user']['id'];
  $sql = "DELETE FROM `posts` WHERE id = $id AND user_id = $user_id;";
  return db_query($sql, true);
}
