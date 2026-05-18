<?php
// public/profile.php
session_start();
require_once __DIR__ . '/../config/config.php';

// 1. ตรวจสอบสถานะการเข้าสู่ระบบ (ถ้ายังไม่ได้ล็อกอิน ให้เด้งไปหน้า login.php)
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customerId = $_SESSION['customer_id'];
$message = '';
$messageType = '';

// 2. จัดการเมื่อมีการกดปุ่มบันทึกข้อมูล (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName       = trim($_POST['first_name']);
    $lastName        = trim($_POST['last_name']);
    $phone           = trim($_POST['phone']);
    $addressDetail   = trim($_POST['address_detail']);
    $provinceCode    = $_POST['province_code'];
    $districtCode    = $_POST['district_code'];
    $subdistrictCode = $_POST['subdistrict_code'];
    $zipcode         = $_POST['zipcode'];
    $password        = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    $isPasswordChanged = false;

    // ตรวจสอบเรื่องการเปลี่ยนรหัสผ่าน (กรอกเฉพาะเมื่อต้องการเปลี่ยน)
    if (!empty($password)) {
        if ($password !== $confirmPassword) {
            $message = "รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน!";
            $messageType = "error";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $isPasswordChanged = true;
        }
    }

    // หากไม่มีข้อผิดพลาด ให้ทำการอัปเดตข้อมูลลงฐานข้อมูล
    if ($messageType !== 'error') {
        try {
            if ($isPasswordChanged) {
                // กรณีเปลี่ยนรหัสผ่านด้วย
                $sql = "UPDATE customers SET first_name = ?, last_name = ?, phone = ?, address_detail = ?, province_code = ?, district_code = ?, subdistrict_code = ?, zipcode = ?, password = ? WHERE customer_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$firstName, $lastName, $phone, $addressDetail, $provinceCode, $districtCode, $subdistrictCode, $zipcode, $hashedPassword, $customerId]);
            } else {
                // กรณีไม่เปลี่ยนรหัสผ่าน
                $sql = "UPDATE customers SET first_name = ?, last_name = ?, phone = ?, address_detail = ?, province_code = ?, district_code = ?, subdistrict_code = ?, zipcode = ? WHERE customer_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$firstName, $lastName, $phone, $addressDetail, $provinceCode, $districtCode, $subdistrictCode, $zipcode, $customerId]);
            }

            // อัปเดตข้อมูลใน Session ของ Navbar ให้เป็นชื่อใหม่ทันที
            $_SESSION['customer_first_name'] = $firstName;
            $_SESSION['customer_last_name']  = $lastName;

            $message = "บันทึกการแก้ไขข้อมูลเรียบร้อยแล้ว";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// 3. ดึงข้อมูลล่าสุดของลูกค้าเพื่อนำมาแสดงใน Form (ข้อมูลเก่า)
try {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id = ?");
    $stmt->execute([$customerId]);
    $customer = $stmt->fetch();

    if (!$customer) {
        die("ไม่พบข้อมูลผู้ใช้งานในระบบ");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลส่วนตัว - GPU Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; background-color: #f8fafc; } </style>
</head>
<body class="text-slate-800 flex flex-col min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12 flex justify-center items-center">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-3xl border border-slate-100">
            <h1 class="text-3xl font-bold text-slate-800 mb-2 text-center">ข้อมูลส่วนตัวและที่อยู่</h1>
            <p class="text-center text-slate-500 mb-6 text-sm">คุณสามารถแก้ไขข้อมูลส่วนตัวและที่อยู่หลักของคุณได้ที่นี่</p>
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-md text-sm font-medium <?= $messageType === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'; ?>">
                    <?= htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="profile.php" method="POST" class="space-y-6">
                
                <div class="bg-slate-50 p-5 rounded-lg border border-slate-100 space-y-4">
                    <h2 class="text-lg font-semibold text-slate-700 border-b pb-2">ข้อมูลบัญชีผู้ใช้</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">ชื่อจริง <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" required value="<?= htmlspecialchars($customer['first_name']) ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">นามสกุล <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" required value="<?= htmlspecialchars($customer['last_name']) ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">เบอร์โทรศัพท์ติดต่อ <span class="text-red-500">*</span></label>
                            <input type="tel" name="phone" required value="<?= htmlspecialchars($customer['phone']) ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">อีเมล (ไม่สามารถแก้ไขได้)</label>
                            <input type="email" readonly value="<?= htmlspecialchars($customer['email']) ?>" class="w-full px-4 py-2 border rounded-lg bg-slate-200 text-slate-500 outline-none cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 p-5 rounded-lg border border-slate-100 space-y-4">
                    <div class="flex items-center justify-between border-b pb-2">
                        <h2 class="text-lg font-semibold text-slate-700">เปลี่ยนรหัสผ่าน</h2>
                        <span class="text-xs text-amber-600 font-medium">* ปล่อยว่างไว้ หากไม่ต้องการเปลี่ยนรหัสผ่าน</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">รหัสผ่านใหม่</label>
                            <input type="password" name="password" minlength="6" placeholder="••••••••" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="confirm_password" minlength="6" placeholder="••••••••" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 p-5 rounded-lg border border-slate-100 space-y-4">
                    <h2 class="text-lg font-semibold text-slate-700 border-b pb-2">ที่อยู่หลักของลูกค้า</h2>
                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-600">รายละเอียดที่อยู่ (เลขที่, อาคาร, หมู่, ซอย, ถนน) <span class="text-red-500">*</span></label>
                        <input type="text" name="address_detail" required value="<?= htmlspecialchars($customer['address_detail']) ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">จังหวัด <span class="text-red-500">*</span></label>
                            <select name="province_code" id="province" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                                <option value="">-- เลือกจังหวัด --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">อำเภอ/เขต <span class="text-red-500">*</span></label>
                            <select name="district_code" id="district" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                                <option value="">-- เลือกอำเภอ --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">ตำบล/แขวง <span class="text-red-500">*</span></label>
                            <select name="subdistrict_code" id="subdistrict" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                                <option value="">-- เลือกตำบล --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">รหัสไปรษณีย์ <span class="text-red-500">*</span></label>
                            <input type="text" name="zipcode" id="zipcode" readonly required value="<?= htmlspecialchars($customer['zipcode']) ?>" class="w-full px-4 py-2 border rounded-lg bg-slate-200 text-slate-600 outline-none cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <div class="flex space-x-4 pt-2">
                    <a href="index.php" class="w-1/3 bg-slate-200 text-slate-700 hover:bg-slate-300 font-bold py-3 px-4 rounded-lg text-center transition">
                        ยกเลิก
                    </a>
                    <button type="submit" class="w-2/3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition transform hover:-translate-y-0.5">
                        บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="bg-slate-900 text-slate-400 py-6 text-center border-t border-slate-800">
        <div class="container mx-auto px-4"><p>&copy; <?= date('Y'); ?> GPU Store. All rights reserved.</p></div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const provinceSelect = document.getElementById('province');
            const districtSelect = document.getElementById('district');
            const subdistrictSelect = document.getElementById('subdistrict');
            const zipcodeInput = document.getElementById('zipcode');

            // ดึงค่ารหัสพื้นที่เดิมที่อยู่ใน DB ของลูกค้ามาบันทึกเป็นตัวแปร JS ไว้เปรียบเทียบ
            const savedProvince    = "<?= $customer['province_code'] ?>";
            const savedDistrict    = "<?= $customer['district_code'] ?>";
            const savedSubdistrict = "<?= $customer['subdistrict_code'] ?>";

            // ฟังก์ชันที่ 1: โหลดจังหวัดทั้งหมด และเลือกจังหวัดเดิมของลูกค้า
            function initProvinces() {
                fetch('api_location.php?action=get_provinces')
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(item => {
                            let option = new Option(item.province_name, item.province_code);
                            if(item.province_code === savedProvince) option.selected = true;
                            provinceSelect.add(option);
                        });
                        // เมื่อโหลดจังหวัดเสร็จ และถ้าลูกค้ามีข้อมูลจังหวัดเก่า ให้สั่งโหลดอำเภอต่อทันที
                        if(savedProvince) { loadDistricts(savedProvince, savedDistrict); }
                    });
            }

            // ฟังก์ชันที่ 2: โหลดอำเภอตามจังหวัด และเลือกอำเภอเดิมของลูกค้า
            function loadDistricts(provinceCode, targetDistrictCode = '') {
                districtSelect.innerHTML = '<option value="">-- เลือกอำเภอ --</option>';
                subdistrictSelect.innerHTML = '<option value="">-- เลือกตำบล --</option>';
                
                fetch(`api_location.php?action=get_districts&province_code=${provinceCode}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(item => {
                            let option = new Option(item.district_name, item.district_code);
                            if(item.district_code === targetDistrictCode) option.selected = true;
                            districtSelect.add(option);
                        });
                        // เมื่อโหลดอำเภอเสร็จ และถ้าลูกค้ามีข้อมูลอำเภอเก่า ให้สั่งโหลดตำบลต่อทันที
                        if(targetDistrictCode) { loadSubdistricts(targetDistrictCode, savedSubdistrict); }
                    });
            }

            // ฟังก์ชันที่ 3: โหลดตำบลตามอำเภอ และเลือกตำบลเดิมของลูกค้า
            function loadSubdistricts(districtCode, targetSubdistrictCode = '') {
                subdistrictSelect.innerHTML = '<option value="">-- เลือกตำบล --</option>';
                
                fetch(`api_location.php?action=get_subdistricts&district_code=${districtCode}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(item => {
                            let option = new Option(item.subdistrict_name, item.subdistrict_code);
                            if(item.subdistrict_code === targetSubdistrictCode) option.selected = true;
                            subdistrictSelect.add(option);
                        });
                    });
            }

            // --- ผูก Event Listener เมื่อผู้ใช้มีการเปลี่ยน Dropdown เองหน้าเว็บ ---
            provinceSelect.addEventListener('change', function() {
                zipcodeInput.value = '';
                if (this.value) { loadDistricts(this.value); } 
                else { districtSelect.innerHTML = '<option value="">-- เลือกอำเภอ --</option>'; subdistrictSelect.innerHTML = '<option value="">-- เลือกตำบล --</option>'; }
            });

            districtSelect.addEventListener('change', function() {
                zipcodeInput.value = '';
                if (this.value) { loadSubdistricts(this.value); } 
                else { subdistrictSelect.innerHTML = '<option value="">-- เลือกตำบล --</option>'; }
            });

            subdistrictSelect.addEventListener('change', function() {
                zipcodeInput.value = '';
                if (this.value) {
                    fetch(`api_location.php?action=get_zipcode&subdistrict_code=${this.value}`)
                        .then(res => res.json())
                        .then(data => { if(data && data.zipcode) zipcodeInput.value = data.zipcode; });
                }
            });

            // เรียกทำงานดึงข้อมูลครั้งแรกเมื่อเปิดหน้าขึ้นมา
            initProvinces();
        });
    </script>
</body>
</html>