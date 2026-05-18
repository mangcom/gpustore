<?php
// public/admin/reset.php
session_start();
require_once __DIR__ . '/../../config/config.php'; // ปรับ Path ตามความจริง ถ้าไฟล์นี้อยู่ public/ ให้เปลี่ยนเป็น /../config/config.php

// ดึงค่า Username/Password จากไฟล์ .env (ถ้าไม่มีจะใช้ค่า Default ป้องกันพัง)
$envAdminUser = $_ENV['ADMIN_USERNAME'] ?? 'superadmin';
$envAdminPass = $_ENV['ADMIN_PASSWORD'] ?? 'superpassword123';

$message = '';
$messageType = '';

// จัดการการออกจากระบบของ Master Admin
if (isset($_GET['logout'])) {
    unset($_SESSION['master_admin_logged_in']);
    header('Location: reset.php');
    exit;
}

// จัดการเมื่อมีการ Submit ฟอร์มต่างๆ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. ฟอร์มเข้าสู่ระบบ Master Admin
    if ($action === 'login') {
        $user = $_POST['username'];
        $pass = $_POST['password'];

        if ($user === $envAdminUser && $pass === $envAdminPass) {
            $_SESSION['master_admin_logged_in'] = true;
            header('Location: reset.php');
            exit;
        } else {
            $message = "Username หรือ Password ของ Master Admin ไม่ถูกต้อง!";
            $messageType = "error";
        }
    }

    // 2. ฟอร์มรีเซ็ตรหัสผ่าน (ต้องล็อกอินผ่านก่อน)
    if ($action === 'reset' && isset($_SESSION['master_admin_logged_in'])) {
        $employeeId = $_POST['employee_id'];
        $newPassword = $_POST['new_password'];

        if (!empty($employeeId) && !empty($newPassword)) {
            try {
                // เข้ารหัสผ่านใหม่
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // อัปเดตลงฐานข้อมูลพนักงาน
                $stmt = $pdo->prepare("UPDATE employees SET password = ? WHERE employee_id = ?");
                if ($stmt->execute([$hashedPassword, $employeeId])) {
                    $message = "เปลี่ยนรหัสผ่านให้พนักงานสำเร็จ! สามารถนำรหัสใหม่ไปล็อกอินได้เลย";
                    $messageType = "success";
                }
            } catch (PDOException $e) {
                $message = "เกิดข้อผิดพลาด: " . $e->getMessage();
                $messageType = "error";
            }
        } else {
            $message = "กรุณาเลือกพนักงานและกรอกรหัสผ่านใหม่";
            $messageType = "error";
        }
    }
}

// ถ้าล็อกอินแล้ว ให้ดึงรายชื่อพนักงานทั้งหมดมาเตรียมไว้ใส่ Dropdown
$employees = [];
if (isset($_SESSION['master_admin_logged_in'])) {
    try {
        $stmt = $pdo->query("SELECT employee_id, first_name, last_name, email FROM employees ORDER BY first_name ASC");
        $employees = $stmt->fetchAll();
    } catch (PDOException $e) {
        $message = "ดึงข้อมูลพนักงานล้มเหลว: " . $e->getMessage();
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Password Reset</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; background-color: #f1f5f9; } </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-slate-200">
        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">🛠️ เครื่องมือรีเซ็ตรหัสผ่าน</h1>
            <p class="text-sm text-slate-500 mt-1">ระบบฉุกเฉินสำหรับ Master Admin</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-5 p-4 rounded-md text-sm font-medium <?= $messageType === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'; ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['master_admin_logged_in'])): ?>
            <form action="reset.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="login">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Master Username</label>
                    <input type="text" name="username" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Master Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-lg transition mt-4">
                    ยืนยันตัวตน (Login)
                </button>
            </form>

        <?php else: ?>
            <form action="reset.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="reset">
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">เลือกพนักงานที่เข้าไม่ได้</label>
                    <select name="employee_id" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        <option value="">-- เลือกรายชื่อพนักงาน --</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['employee_id'] ?>">
                                <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?> (<?= htmlspecialchars($emp['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ตั้งรหัสผ่านใหม่ (New Password)</label>
                    <input type="text" name="new_password" required placeholder="ตัวอย่าง: newpass1234" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2.5 rounded-lg transition shadow-md">
                        บันทึกรหัสผ่านใหม่
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center border-t pt-4">
                <a href="reset.php?logout=1" class="text-sm text-red-500 hover:text-red-700 font-medium underline">
                    ออกจากระบบ Master Admin
                </a>
                <br>
                <a href="login.php" class="text-sm text-blue-500 hover:text-blue-700 font-medium inline-block mt-2">
                    กลับไปหน้า Login ปกติ
                </a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>