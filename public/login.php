<?php
// public/login.php
session_start();
require_once __DIR__ . '/../config/config.php';

// ถ้าลูกค้าเข้าสู่ระบบอยู่แล้ว ให้เด้งไปหน้าแรกทันที
if (isset($_SESSION['customer_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // ค้นหาข้อมูลลูกค้าจากอีเมล
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        // ตรวจสอบว่าพบข้อมูล และรหัสผ่านถูกต้อง (เปรียบเทียบกับ Hash ใน DB)
        if ($customer && password_verify($password, $customer['password'])) {
            // บันทึกข้อมูลลง Session
            $_SESSION['customer_id'] = $customer['customer_id'];
            $_SESSION['customer_first_name'] = $customer['first_name'];
            $_SESSION['customer_last_name'] = $customer['last_name'];
            $_SESSION['customer_email'] = $customer['email'];

            // เปลี่ยนหน้าไปยังหน้าแรก
            header('Location: index.php');
            exit;
        } else {
            $message = "อีเมลหรือรหัสผ่านไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง";
        }
    } catch (PDOException $e) {
        $message = "เกิดข้อผิดพลาดในระบบ: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - GPU Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; background-color: #f8fafc; } </style>
</head>
<body class="text-slate-800 flex flex-col min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12 flex justify-center items-center">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md border border-slate-100">
            <h1 class="text-3xl font-bold text-slate-800 mb-2 text-center">เข้าสู่ระบบ</h1>
            <p class="text-center text-slate-500 mb-6 text-sm">เพลิดเพลินกับการเลือกซื้อการ์ดจอมือหนึ่งราคาพิเศษ</p>
            
            <?php if ($message): ?>
                <div class="mb-4 p-4 rounded-md text-sm font-medium bg-red-100 text-red-700">
                    <?= htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">อีเมล</label>
                    <input type="email" name="email" required placeholder="example@email.com" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">รหัสผ่าน</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                </div>

                <div class="flex justify-between items-center text-sm">
                    <label class="flex items-center text-slate-600">
                        <input type="checkbox" class="rounded text-emerald-500 focus:ring-emerald-500 mr-2"> จดจำฉันไว้
                    </label>
                    <a href="#" class="text-emerald-600 hover:underline">ลืมรหัสผ่าน?</a>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition transform hover:-translate-y-0.5">
                        เข้าสู่ระบบ
                    </button>
                </div>
                
                <div class="text-center mt-6 text-sm text-slate-500">
                    ยังไม่มีบัญชีผู้ใช้? <a href="register.php" class="text-emerald-600 hover:underline font-medium">สมัครสมาชิกใหม่</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="bg-slate-900 text-slate-400 py-6 text-center border-t border-slate-800">
        <div class="container mx-auto px-4">
            <p>&copy; <?= date('Y'); ?> GPU Store. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>