<?php
include '../config/db.php';

$url = $_GET['url'] ?? 'login';
$page = "../pages/{$url}.php";



if (file_exists($page)) {
    include $page;
} else {
    echo "<h2>404 - Page not found</h2>";
}

;
?>
