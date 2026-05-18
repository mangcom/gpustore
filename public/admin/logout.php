<?php
// public/admin/logout.php
session_start();

// ล้างเฉพาะเซสชันของเจ้าหน้าที่ (ไม่กระทบตะกร้าสินค้าของลูกค้าในหน้าหลัก)
unset($_SESSION['employee_id']);
unset($_SESSION['employee_name']);
unset($_SESSION['employee_role']);

header('Location: login.php');
exit;