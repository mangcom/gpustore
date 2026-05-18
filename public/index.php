<?php
// public/index.php
session_start();
require_once __DIR__ . '/../config/config.php';

// ดึงข้อมูลสินค้าทั้งหมด พร้อมกับชื่อแบรนด์ผู้ผลิต
try {
    $sql = "SELECT p.*, m.name AS manufacturer_name 
            FROM products p 
            LEFT JOIN manufacturers m ON p.manufacturer_id = m.manufacturer_id 
            ORDER BY p.product_id DESC";
    $products = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลสินค้า: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPU Store - แหล่งรวมการ์ดจอคุณภาพ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; background-color: #f8fafc; } </style>
</head>
<body class="text-slate-800 flex flex-col min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <header class="bg-gradient-to-r from-slate-900 to-slate-800 text-white py-16 text-center">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-5xl font-bold text-emerald-400 mb-4 tracking-wide">GPU STORE</h1>
            <p class="text-slate-300 text-lg max-w-xl mx-auto">ยินดีต้อนรับสู่แหล่งรวมการ์ดจอสำหรับการเล่นเกมและการทำงานระดับมืออาชีพ</p>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold text-slate-800 mb-8 border-b pb-3 flex items-center">
            <span>📦 สินค้าทั้งหมดของเรา</span>
            <span class="ml-3 text-sm font-normal text-slate-500">(พบการ์ดจอ <?= count($products) ?> รายการ)</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $item): 
                    // ตรวจสอบรูปภาพ: ถ้ามีใน DB ให้ใช้ภาพจากโฟลเดอร์ thumbnails ถ้าไม่มีให้ใช้ภาพ no-image
                    $imagePath = "uploads/products/no-image.png"; 
                    if (!empty($item['image_thumbnail']) && file_exists(__DIR__ . "/uploads/products/thumbnails/" . $item['image_thumbnail'])) {
                        $imagePath = "uploads/products/thumbnails/" . $item['image_thumbnail'];
                    }
                ?>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col group hover:shadow-xl hover:border-emerald-500/20 transition duration-300 transform hover:-translate-y-1">
                        
                        <div class="relative bg-slate-50 pt-[100%] overflow-hidden border-b border-slate-100">
                            <img src="<?= htmlspecialchars($imagePath) ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                 class="absolute top-0 left-0 w-full h-full object-cover p-4 transition duration-500 group-hover:scale-105">
                            
                            <?php if ($item['stock_quantity'] <= 0): ?>
                                <span class="absolute top-3 right-3 bg-rose-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow">
                                    สินค้าหมด
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="p-5 flex flex-col flex-grow">
                            <span class="text-xs font-semibold tracking-wider text-emerald-600 uppercase mb-1">
                                <?= htmlspecialchars($item['manufacturer_name'] ?? 'ไม่ระบุแบรนด์') ?>
                            </span>
                            
                            <h3 class="font-bold text-slate-800 text-base line-clamp-2 mb-2 group-hover:text-emerald-500 transition">
                                <?= htmlspecialchars($item['name']) ?>
                            </h3>
                            
                            <p class="text-xs text-slate-500 line-clamp-2 mb-4 flex-grow">
                                <?= htmlspecialchars($item['description'] ?? 'ไม่มีรายละเอียดสินค้า') ?>
                            </p>

                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
                                <div>
                                    <span class="text-xs text-slate-400 block">ราคาพิเศษ</span>
                                    <span class="text-lg font-extrabold text-slate-900">
                                        ฿<?= number_format($item['price'], 2) ?>
                                    </span>
                                </div>
                                
                                <?php if ($item['stock_quantity'] > 0): ?>
                                    <a href="product_detail.php?id=<?= $item['product_id'] ?>" 
                                       class="bg-slate-900 hover:bg-emerald-500 text-white hover:text-slate-950 font-bold text-sm py-2 px-4 rounded-lg shadow-sm transition duration-300">
                                        ดูรายละเอียด
                                    </a>
                                <?php else: ?>
                                    <button disabled 
                                            class="bg-slate-200 text-slate-400 font-bold text-sm py-2 px-4 rounded-lg cursor-not-allowed">
                                        หมดชั่วคราว
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12 bg-white rounded-xl border border-dashed">
                    <p class="text-slate-400">ยังไม่มีสินค้าวางจำหน่ายในขณะนี้</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-slate-900 text-slate-400 py-8 text-center border-t border-slate-800 mt-12">
        <div class="container mx-auto px-4">
            <p>&copy; <?= date('Y'); ?> GPU Store. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>