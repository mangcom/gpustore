<?php
session_start();
// นำเข้าไฟล์ตั้งค่าการเชื่อมต่อฐานข้อมูล (ปรับ path ให้ตรงกับโฟลเดอร์ของคุณ)
require_once __DIR__ . '/../config/config.php';

// ดึงข้อมูลสินค้าจากฐานข้อมูล (ดึงรุ่นล่าสุด 8 รายการแรก)
try {
    $sql = "SELECT p.product_id, p.name AS product_name, p.description, p.price, p.stock_quantity, m.name AS brand_name 
            FROM products p 
            LEFT JOIN manufacturers m ON p.manufacturer_id = m.manufacturer_id 
            ORDER BY p.created_at DESC 
            LIMIT 8";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลสินค้า: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_ENV['APP_NAME'] ?? 'GPU First-Hand Store' ?> - หน้าแรก</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f8fafc; /* พื้นหลังสีเทาอ่อน สบายตา */
        }
    </style>
</head>
<body class="text-slate-800">

<?php include 'includes/navbar.php'; ?>

    <header class="bg-gradient-to-r from-slate-800 to-slate-700 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">การ์ดจอมือหนึ่ง ของแท้ ประกันศูนย์ไทย</h1>
            <p class="text-lg md:text-xl text-slate-300 mb-8 max-w-2xl mx-auto">
                ยกระดับประสบการณ์การเล่นเกมและการทำงานของคุณ ด้วยสุดยอดขุมพลังกราฟิกรุ่นใหม่ล่าสุดที่เราคัดสรรมาเพื่อคุณโดยเฉพาะ
            </p>
            <a href="#product-section" class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-3 px-8 rounded-full shadow-lg transition transform hover:scale-105">
                เลือกชมสินค้า
            </a>
        </div>
    </header>

    <main id="product-section" class="container mx-auto px-4 py-12">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-800">สินค้ามาใหม่ (New Arrivals)</h2>
                <p class="text-slate-500 mt-2">อัปเดตสต็อกการ์ดจอรุ่นล่าสุด</p>
            </div>
            <a href="#" class="text-emerald-600 hover:text-emerald-700 font-medium hidden md:block">ดูทั้งหมด &rarr;</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-slate-100 flex flex-col">
                        <div class="h-48 bg-slate-200 flex items-center justify-center relative">
                            <span class="absolute top-2 right-2 bg-slate-800 text-white text-xs font-bold px-2 py-1 rounded">
                                <?= htmlspecialchars($product['brand_name'] ?? 'Unknown Brand') ?>
                            </span>
                            <span class="text-slate-400">รูปภาพการ์ดจอ</span>
                        </div>
                        
                        <div class="p-5 flex flex-col flex-grow">
                            <h3 class="text-lg font-semibold mb-2 line-clamp-2" title="<?= htmlspecialchars($product['product_name']) ?>">
                                <?= htmlspecialchars($product['product_name']) ?>
                            </h3>
                            <p class="text-sm text-slate-500 mb-4 line-clamp-2 flex-grow">
                                <?= htmlspecialchars($product['description']) ?>
                            </p>
                            
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-xl font-bold text-emerald-600">
                                    ฿<?= number_format($product['price'], 2) ?>
                                </span>
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <span class="text-xs font-medium bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full">
                                        มีสินค้า (<?= $product['stock_quantity'] ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs font-medium bg-red-100 text-red-700 px-2 py-1 rounded-full">
                                        สินค้าหมด
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <button 
                                class="w-full py-2 rounded-lg font-medium transition <?= $product['stock_quantity'] > 0 ? 'bg-slate-900 text-white hover:bg-slate-800' : 'bg-slate-200 text-slate-400 cursor-not-allowed' ?>"
                                <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>
                            >
                                <?= $product['stock_quantity'] > 0 ? 'เพิ่มลงตะกร้า' : 'สินค้าหมด' ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12 text-slate-500">
                    <p class="text-xl">ยังไม่มีสินค้าในระบบขณะนี้</p>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <footer class="bg-slate-900 text-slate-400 py-8 text-center border-t border-slate-800">
        <div class="container mx-auto px-4">
            <p>&copy; <?= date('Y') ?> <?= $_ENV['APP_NAME'] ?? 'GPU Store' ?>. All rights reserved.</p>
            <p class="mt-2 text-sm">ระบบจำหน่ายการ์ดจอมือหนึ่งที่ดีที่สุด</p>
        </div>
    </footer>

</body>
</html>