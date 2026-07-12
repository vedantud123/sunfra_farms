<?php
include 'phpqrcode.php';

// send image header
header('Content-Type: image/png');

// your URL
$url = "https://sunfra.com/farm/test2/task/task_list_save.php";

// output QR directly
QRcode::png($url);
?>
