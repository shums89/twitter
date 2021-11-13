<?php
include_once "includes/functions.php";

$error = get_error_message();

// echo "<pre>";
// var_dump($_GET['id']);
// echo "</pre>";
// die;

if (isset($_GET['id']) && !empty($_GET['id'])) {
  $id = $_GET['id'];
} else if (isset($_SESSION['user']['id'])) {
  $id = $_SESSION['user']['id'];
} else {
  $id = 0;
}

$posts = get_posts($id);

if (!empty($posts)) {
  $title = 'Твиты пользователя @' . $posts[0]['login'];
} else {
  $title = 'Твиты пользователя';
}

include_once "includes/header.php";
include_once "includes/posts.php";
include_once "includes/footer.php";
