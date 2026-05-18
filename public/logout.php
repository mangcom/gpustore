<?php
// public/logout.php
session_start();

// ล้างค่าเซสชันทั้งหมด
$_SESSION = array();

// ทำลายเซสชัน
session_destroy();

// ส่งกลับไปยังหน้าแรก
header('Location: index.php');
exit;