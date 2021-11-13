<?php
include_once "includes/functions.php";

// echo "<pre>";
// var_dump($_GET['id']);
// echo "</pre>";
// die;

$id = 0;
if (isset($_GET['id']) && !empty($_GET['id'])) {
  $id = $_GET['id'];
  $title = 'Твиты пользователя @' . $posts[0]['login'];
} else {
  $title = 'Все твиты';
}
$posts = get_posts($id);

include_once "includes/header.php";
include_once "includes/posts.php";
include_once "includes/footer.php";
