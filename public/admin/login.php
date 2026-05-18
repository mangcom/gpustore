<?php
// public/admin/login.php
session_start();
require_once __DIR__ . '/../../config/config.php';

// ถ้าเจ้าหน้าที่ล็อกอินอยู่แล้ว ให้ส่งไปหน้าฟอร์มจัดการสินค้า หรือหน้า Dashboard หลังบ้านทันที
if (isset($_SESSION['employee_id'])) {
    header('Location: product_form.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $employee = $stmt->fetch();

        if ($employee && password_verify($password, $employee['password'])) {
            // บันทึกเซสชันแยกต่างหาก (ห้ามใช้ปนกับลูกค้า)
            $_SESSION['employee_id'] = $employee['employee_id'];
            $_SESSION['employee_name'] = $employee['first_name'] . ' ' . $employee['last_name'];
            $_SESSION['employee_role'] = $employee['role'];

            // ล็อกอินสำเร็จ ส่งไปหน้าฟอร์มจัดการสินค้า
            header('Location: product_form.php');
            exit;
        } else {
            $message = "อีเมล รหัสผ่านไม่ถูกต้อง หรือบัญชีของคุณถูกระงับ";
        }
    } catch (PDOException $e) {
        $message = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบเจ้าหน้าที่ - GPU Store Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; background-color: #0f172a; } </style>
</head>
<body class="text-slate-200 flex flex-col min-h-screen justify-center items-center p-4">

    <div class="bg-slate-800 p-8 rounded-xl shadow-2xl w-full max-w-md border border-slate-700">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold tracking-wider text-emerald-400 mb-1">GPUSTORE CONTROL PANEL</h1>
            <p class="text-sm text-slate-400">สำหรับเจ้าหน้าที่และผู้ดูแลระบบเท่านั้น</p>
        </div>
        
        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded-lg text-sm font-medium bg-rose-500/10 border border-rose-500/20 text-rose-400">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">อีเมลพนักงาน</label>
                <input type="email" name="email" required placeholder="admin@gpustore.com" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500 text-white outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">รหัสผ่าน</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500 text-white outline-none transition">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold py-3 px-4 rounded-lg shadow-lg transition transform hover:-translate-y-0.5">
                    เข้าสู่ระบบทำงาน
                </button>
            </div>
        </form>
    </div>

</body>
</html>