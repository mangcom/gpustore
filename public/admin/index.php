<?php
// public/admin/index.php
session_start();

// 1. ตรวจสอบความปลอดภัย: ถ้าไม่มีเซสชันของพนักงาน ให้เด้งกลับไปหน้า login ของ admin ทันที
if (!isset($_SESSION['employee_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../config/config.php';

// 2. ดึงข้อมูลสินค้าทั้งหมดพร้อมชื่อแบรนด์ผู้ผลิตเพื่อนำมาแสดงในตารางจัดการ
try {
    $sql = "SELECT p.*, m.name AS manufacturer_name 
            FROM products p 
            LEFT JOIN manufacturers m ON p.manufacturer_id = m.manufacturer_id 
            ORDER BY p.product_id DESC";
    $products = $pdo->query($sql)->fetchAll();
    
    // คำนวณสถิติเบื้องต้นสำหรับโชว์บน Dashboard
    $totalProducts = count($products);
    $outOfStock = 0;
    foreach ($products as $p) {
        if ($p['stock_quantity'] <= 0) {
            $outOfStock++;
        }
    }
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการฐานข้อมูล: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบหลังบ้าน จัดการสินค้า - GPU Store Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; background-color: #f8fafc; } </style>
</head>
<body class="text-slate-800 flex flex-col min-h-screen">

    <nav class="bg-slate-900 text-white shadow-md py-4 px-6">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="text-xl font-bold tracking-wider text-emerald-400">GPUSTORE</span>
                <span class="bg-slate-800 text-slate-400 text-xs px-2.5 py-1 rounded border border-slate-700">ระบบหลังบ้าน</span>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-slate-200"><?= htmlspecialchars($_SESSION['employee_name']) ?></p>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">สิทธิ์: <?= htmlspecialchars($_SESSION['employee_role']) ?></p>
                </div>
                <a href="logout.php" class="bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white px-3 py-1.5 rounded-lg text-sm font-medium border border-rose-500/20 transition">
                    ออกจากระบบ
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-grow container mx-auto px-4 py-8 max-w-7xl">
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">📦 ระบบจัดการข้อมูลสินค้า</h1>
                <p class="text-slate-500 text-sm mt-1">เจ้าหน้าที่สามารถเพิ่ม ค้นหา และเลือกรายการสินค้าที่ต้องการอัปเดตราคาหรือรูปภาพได้จากหน้านี้</p>
            </div>
            <div>
                <a href="product_form.php" class="inline-flex items-center bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold px-5 py-3 rounded-xl shadow-lg shadow-emerald-500/20 transition transform hover:-translate-y-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    เพิ่มสินค้าใหม่เข้าสู่ระบบ
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center space-x-4">
                <div class="p-3 rounded-xl bg-blue-50 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <span class="block text-slate-500 text-sm font-medium">สินค้าทั้งหมดในระบบ</span>
                    <span class="text-2xl font-bold text-slate-900"><?= $totalProducts ?> <span class="text-sm font-normal text-slate-500">รุ่น</span></span>
                </div>
            </div>
            
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center space-x-4">
                <div class="p-3 rounded-xl bg-rose-50 text-rose-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <span class="block text-slate-500 text-sm font-medium">สินค้าที่หมดชั่วคราว (สต็อก 0)</span>
                    <span class="text-2xl font-bold text-rose-600"><?= $outOfStock ?> <span class="text-sm font-normal text-slate-500">รุ่น</span></span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center space-x-4 lg:col-span-1 sm:col-span-2">
                <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="block text-slate-500 text-sm font-medium">สถานะฐานข้อมูล</span>
                    <span class="text-base font-semibold text-emerald-600 flex items-center">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 inline-block mr-2 animate-pulse"></span>
                        เชื่อมต่อปกติพร้อมทำงาน
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-lg">📋 รายการการ์ดจอทั้งหมดในสต็อก</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-100/70 text-slate-600 text-xs font-semibold uppercase tracking-wider">
                            <th class="py-4 px-5 w-20 text-center">รูปภาพ</th>
                            <th class="py-4 px-5">แบรนด์ / รุ่นสินค้า</th>
                            <th class="py-4 px-5 text-right">ราคาจำหน่าย (บาท)</th>
                            <th class="py-4 px-5 text-center">คงเหลือในคลัง</th>
                            <th class="py-4 px-5 text-center w-36">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if ($totalProducts > 0): ?>
                            <?php foreach ($products as $row): 
                                // จัดการรูปภาพที่จะแสดงในตารางจัดการ (ดึงภาพ thumbnail มาโชว์เล็กๆ)
                                $imagePath = "../uploads/products/no-image.png";
                                if (!empty($row['image_thumbnail']) && file_exists(__DIR__ . "/../uploads/products/thumbnails/" . $row['image_thumbnail'])) {
                                    $imagePath = "../uploads/products/thumbnails/" . $row['image_thumbnail'];
                                }
                            ?>
                                <tr class="hover:bg-slate-50/80 transition duration-150">
                                    <td class="py-3 px-5 text-center">
                                        <div class="h-12 w-12 rounded-lg bg-slate-100 border p-1 overflow-hidden inline-flex items-center justify-center">
                                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="thumb" class="max-h-full max-w-full object-contain rounded">
                                        </div>
                                    </td>
                                    <td class="py-3 px-5">
                                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wide block mb-0.5">
                                            <?= htmlspecialchars($row['manufacturer_name'] ?? 'ทั่วไป') ?>
                                        </span>
                                        <span class="font-semibold text-slate-900 block truncate max-w-md">
                                            <?= htmlspecialchars($row['name']) ?>
                                        </span>
                                        <span class="text-xs text-slate-400 block truncate max-w-xs mt-0.5">
                                            ID: #<?= $row['product_id'] ?> | <?= !empty($row['description']) ? htmlspecialchars(mb_strimwidth($row['description'], 0, 50, '...')) : 'ไม่มีรายละเอียด' ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-5 text-right font-bold text-slate-900">
                                        ฿<?= number_format($row['price'], 2) ?>
                                    </td>
                                    <td class="py-3 px-5 text-center">
                                        <?php if ($row['stock_quantity'] > 5): ?>
                                            <span class="bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full text-xs font-medium border border-emerald-200">
                                                <?= $row['stock_quantity'] ?> ชิ้น
                                            </span>
                                        <?php elseif ($row['stock_quantity'] > 0): ?>
                                            <span class="bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full text-xs font-medium border border-amber-200">
                                                เหลือเพียง <?= $row['stock_quantity'] ?> ชิ้น
                                            </span>
                                        <?php else: ?>
                                            <span class="bg-rose-50 text-rose-700 px-2.5 py-1 rounded-full text-xs font-bold border border-rose-200">
                                                สินค้าหมด
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-5 text-center">
                                        <a href="product_form.php?id=<?= $row['product_id'] ?>" class="inline-flex items-center bg-slate-800 hover:bg-emerald-500 text-white hover:text-slate-950 px-3 py-1.5 rounded-lg text-xs font-bold transition duration-200 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            แก้ไขข้อมูล
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-10 text-center text-slate-400 font-medium">
                                    ไม่มีรายการสินค้าในระบบฐานข้อมูลในขณะนี้
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="bg-white border-t border-slate-200 text-slate-400 py-5 text-center text-xs">
        <div class="container mx-auto px-4">
            <p>&copy; <?= date('Y'); ?> GPU Store Control Panel. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>