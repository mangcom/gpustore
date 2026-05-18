<?php
// นำโค้ดนี้ไปแทนที่ด้านบนสุดของไฟล์ public/admin/product_form.php
session_start();

// ตรวจสอบ: ถ้าไม่มีเซสชันของพนักงาน ให้เด้งดีดออกไปหน้า Login หลังบ้านทันที
if (!isset($_SESSION['employee_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../config/config.php';

// หมายเหตุ: ในระบบจริง ควรตรวจสอบว่าพนักงาน (Employee) ล็อกอินอยู่หรือไม่
// if (!isset($_SESSION['employee_id'])) { header('Location: admin_login.php'); exit; }

$message = '';
$messageType = '';
$productId = $_GET['id'] ?? null;
$product = null;

// ฟังก์ชันสำหรับ Resize รูปภาพ
function resizeImage($sourcePath, $destPath, $maxWidth, $maxHeight) {
    list($origWidth, $origHeight, $imageType) = getimagesize($sourcePath);
    
    // คำนวณสัดส่วนใหม่
    $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
    $newWidth = round($origWidth * $ratio);
    $newHeight = round($origHeight * $ratio);
    
    // สร้างภาพ canvas เปล่าๆ
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // จัดการพื้นหลังโปร่งใสสำหรับ PNG และ WEBP
    if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_WEBP) {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // โหลดภาพต้นฉบับตามประเภทไฟล์
    switch ($imageType) {
        case IMAGETYPE_JPEG: $sourceImage = imagecreatefromjpeg($sourcePath); break;
        case IMAGETYPE_PNG:  $sourceImage = imagecreatefrompng($sourcePath); break;
        case IMAGETYPE_GIF:  $sourceImage = imagecreatefromgif($sourcePath); break;
        case IMAGETYPE_WEBP: $sourceImage = imagecreatefromwebp($sourcePath); break;
        default: return false; // ไม่รองรับ
    }
    
    // ทำการ Resize
    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
    
    // บันทึกไฟล์ใหม่
    switch ($imageType) {
        case IMAGETYPE_JPEG: imagejpeg($newImage, $destPath, 85); break; // คุณภาพ 85%
        case IMAGETYPE_PNG:  imagepng($newImage, $destPath, 8); break;
        case IMAGETYPE_GIF:  imagegif($newImage, $destPath); break;
        case IMAGETYPE_WEBP: imagewebp($newImage, $destPath, 85); break;
    }
    // ล้างหน่วยความจำ ของภาพ ที่สร้างขึ้น เพื่อป้องกันการใช้หน่วยความจำมากเกินไป หากเป็น php 8.1 ขึ้นไป จะมีฟังก์ชัน imagedestroy ที่จะล้างหน่วยความจำของภาพที่สร้างขึ้น
    // @imagedestroy($newImage);
    // @imagedestroy($sourceImage);
    return true;
}

// 1. ดึงข้อมูลสินค้าเดิมมาแสดง (กรณี Edit)
if ($productId) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
}

// 2. จัดการเมื่อมีการ Submit Form (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $manufacturerId = $_POST['manufacturer_id'];
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $stockQuantity = $_POST['stock_quantity'];
    
    // จัดการอัปโหลดและ Resize รูปภาพ
    $imageOriginal = $product['image_original'] ?? null;
    $imageThumbnail = $product['image_thumbnail'] ?? null;
    
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDirOrig = __DIR__ . '/../uploads/products/originals/';
        $uploadDirThumb = __DIR__ . '/../uploads/products/thumbnails/';
        
        $fileInfo = pathinfo($_FILES['product_image']['name']);
        $extension = strtolower($fileInfo['extension']);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (in_array($extension, $allowedExtensions)) {
            // ตั้งชื่อไฟล์ใหม่ด้วย uniqid เพื่อป้องกันชื่อซ้ำ
            $newFileName = uniqid('gpu_') . '.' . $extension;
            $targetOrig = $uploadDirOrig . $newFileName;
            $targetThumb = $uploadDirThumb . $newFileName;
            
            // ย้ายไฟล์ภาพต้นฉบับ
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetOrig)) {
                $imageOriginal = $newFileName;
                
                // ทำการ Resize ภาพสำหรับ Thumbnail (สมมติจำกัดขนาดที่ 600x600 px)
                if (resizeImage($targetOrig, $targetThumb, 600, 600)) {
                    $imageThumbnail = $newFileName;
                } else {
                    $message = "บันทึกภาพต้นฉบับสำเร็จ แต่ไม่สามารถ Resize ภาพได้";
                    $messageType = "warning";
                }
            } else {
                $message = "เกิดข้อผิดพลาดในการอัปโหลดภาพ";
                $messageType = "error";
            }
        } else {
            $message = "รองรับเฉพาะไฟล์รูปภาพ JPG, PNG, WEBP, GIF เท่านั้น";
            $messageType = "error";
        }
    }

    // บันทึกหรืออัปเดตลงฐานข้อมูล
    if ($messageType !== 'error') {
        try {
            if ($productId) {
                // Update
                $sql = "UPDATE products SET manufacturer_id=?, name=?, description=?, image_original=?, image_thumbnail=?, price=?, stock_quantity=? WHERE product_id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$manufacturerId, $name, $description, $imageOriginal, $imageThumbnail, $price, $stockQuantity, $productId]);
                $message = "อัปเดตข้อมูลสินค้าสำเร็จ";
            } else {
                // Insert
                $sql = "INSERT INTO products (manufacturer_id, name, description, image_original, image_thumbnail, price, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$manufacturerId, $name, $description, $imageOriginal, $imageThumbnail, $price, $stockQuantity]);
                $message = "เพิ่มสินค้าใหม่สำเร็จ";
                // ดึงข้อมูลที่เพิ่ง insert เพื่อมาแสดง
                $productId = $pdo->lastInsertId();
                $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
            }
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// ดึงข้อมูลผู้ผลิตมาใส่ Dropdown
$manufacturers = $pdo->query("SELECT * FROM manufacturers ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $productId ? 'แก้ไขสินค้า' : 'เพิ่มสินค้าใหม่' ?> - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; background-color: #f1f5f9; } </style>
</head>
<body class="text-slate-800">

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-slate-800">
                <?= $productId ? '✏️ แก้ไขสินค้า' : '📦 เพิ่มสินค้าใหม่' ?>
            </h1>
            <a href="#" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-md transition">
                กลับหน้ารายการสินค้า
            </a>
        </div>
        <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-slate-800">📦 จัดการข้อมูลสินค้า</h1>
    <div class="flex items-center space-x-4">
        <span class="text-sm text-slate-600">ผู้ทำงาน: <strong class="text-slate-900"><?= htmlspecialchars($_SESSION['employee_name']) ?></strong> (<?= $_SESSION['employee_role'] ?>)</span>
        <a href="logout.php" class="bg-rose-500 hover:bg-rose-600 text-white px-3 py-1.5 rounded-md text-sm transition">ออกจากระบบ</a>
    </div>
</div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-md font-medium <?= $messageType === 'success' ? 'bg-emerald-100 text-emerald-800' : ($messageType === 'warning' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">ชื่อสินค้ารุ่นการ์ดจอ <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="<?= htmlspecialchars($product['name'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">แบรนด์ผู้ผลิต <span class="text-red-500">*</span></label>
                        <select name="manufacturer_id" required class="w-full px-4 py-2 border rounded-lg focus:ring-emerald-500 outline-none">
                            <option value="">-- เลือกแบรนด์ --</option>
                            <?php foreach ($manufacturers as $m): ?>
                                <option value="<?= $m['manufacturer_id'] ?>" <?= ($product['manufacturer_id'] ?? '') == $m['manufacturer_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">ราคา (บาท) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="price" required value="<?= htmlspecialchars($product['price'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">สต็อกคงเหลือ <span class="text-red-500">*</span></label>
                            <input type="number" name="stock_quantity" required value="<?= htmlspecialchars($product['stock_quantity'] ?? 0) ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-emerald-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">รายละเอียดสเปค</label>
                        <textarea name="description" rows="4" class="w-full px-4 py-2 border rounded-lg focus:ring-emerald-500 outline-none"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <label class="block text-sm font-medium mb-2">รูปภาพสินค้า</label>
                    
                    <?php if (!empty($product['image_thumbnail'])): ?>
                        <div class="mb-4 bg-white p-2 border rounded-lg text-center">
                            <p class="text-xs text-slate-500 mb-2">ภาพปัจจุบัน (ย่อขนาดแล้ว)</p>
                            <img src="../uploads/products/thumbnails/<?= htmlspecialchars($product['image_thumbnail']) ?>" alt="Product" class="max-h-48 mx-auto rounded">
                            <div class="mt-2 text-xs">
                                <a href="../uploads/products/originals/<?= htmlspecialchars($product['image_original']) ?>" target="_blank" class="text-blue-600 hover:underline">
                                    ดูภาพต้นฉบับขนาดเต็ม
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mt-2">
                        <label class="block text-sm font-medium mb-1 text-slate-600">อัปโหลดภาพใหม่ (ถ้าต้องการเปลี่ยน)</label>
                        <input type="file" name="product_image" accept="image/jpeg, image/png, image/webp, image/gif" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 outline-none">
                        <p class="text-xs text-slate-400 mt-2">* ระบบจะทำการปรับขนาดภาพอัตโนมัติ (Resize) เป็นขนาดไม่เกิน 600x600 px และเก็บภาพต้นฉบับไว้ให้</p>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t flex justify-end space-x-3">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-lg shadow transition">
                    <?= $productId ? 'บันทึกการแก้ไข' : 'เพิ่มสินค้า' ?>
                </button>
            </div>
        </form>
    </div>

</body>
</html>