<?php
// auth.php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['profile_id'])) {
  $self = basename($_SERVER['REQUEST_URI']);
  header('Location: login.html?next=' . urlencode($self));
  exit;
}
