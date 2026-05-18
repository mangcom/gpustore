<?php
// public/register.php
session_start();
require_once __DIR__ . '/../config/config.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName  = trim($_POST['last_name']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $phone     = trim($_POST['phone']);
    
    // ข้อมูลที่อยู่แบบแยกส่วน
    $addressDetail   = trim($_POST['address_detail']);
    $provinceCode    = $_POST['province_code'];
    $districtCode    = $_POST['district_code'];
    $subdistrictCode = $_POST['subdistrict_code'];
    $zipcode         = $_POST['zipcode'];

    // 1. ตรวจสอบรหัสผ่านว่าตรงกันหรือไม่
    if ($password !== $confirmPassword) {
        $message = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน!";
        $messageType = "error";
    } else {
        // ตรวจสอบอีเมลซ้ำ
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn()) {
            $message = "อีเมลนี้ถูกใช้งานแล้ว กรุณาใช้อีเมลอื่น";
            $messageType = "error";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            try {
                $sql = "INSERT INTO customers (first_name, last_name, email, password, phone, address_detail, subdistrict_code, district_code, province_code, zipcode) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $firstName, $lastName, $email, $hashedPassword, $phone, 
                    $addressDetail, $subdistrictCode, $districtCode, $provinceCode, $zipcode
                ]);
                
                $message = "สมัครสมาชิกสำเร็จ! คุณสามารถเข้าสู่ระบบได้แล้ว";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "เกิดข้อผิดพลาด: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - GPU Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; background-color: #f8fafc; } </style>
</head>
<body class="text-slate-800 flex flex-col min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-12 flex justify-center items-center">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-3xl border border-slate-100">
            <h1 class="text-3xl font-bold text-slate-800 mb-6 text-center">สมัครสมาชิก</h1>
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-md text-sm font-medium <?= $messageType === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'; ?>">
                    <?= htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="space-y-6">
                <div class="bg-slate-50 p-5 rounded-lg border border-slate-100 space-y-4">
                    <h2 class="text-lg font-semibold text-slate-700 border-b pb-2">ข้อมูลส่วนตัว</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">ชื่อจริง <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">นามสกุล <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">เบอร์โทรศัพท์ <span class="text-red-500">*</span></label>
                            <input type="tel" name="phone" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">อีเมล <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">รหัสผ่าน <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required minlength="6" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">ยืนยันรหัสผ่าน <span class="text-red-500">*</span></label>
                            <input type="password" name="confirm_password" required minlength="6" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 p-5 rounded-lg border border-slate-100 space-y-4">
                    <h2 class="text-lg font-semibold text-slate-700 border-b pb-2">ที่อยู่ของลูกค้า</h2>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">รายละเอียดส่วนแรก (เลขที่, อาคาร, หมู่, ซอย, ถนน) <span class="text-red-500">*</span></label>
                        <input type="text" name="address_detail" required placeholder="เช่น 123/45 หมู่ 1 ซ.สุขุมวิท 10 ถ.สุขุมวิท" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">จังหวัด <span class="text-red-500">*</span></label>
                            <select name="province_code" id="province" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                                <option value="">-- เลือกจังหวัด --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">อำเภอ/เขต <span class="text-red-500">*</span></label>
                            <select name="district_code" id="district" required disabled class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-slate-100">
                                <option value="">-- เลือกอำเภอ --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">ตำบล/แขวง <span class="text-red-500">*</span></label>
                            <select name="subdistrict_code" id="subdistrict" required disabled class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-slate-100">
                                <option value="">-- เลือกตำบล --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">รหัสไปรษณีย์ <span class="text-red-500">*</span></label>
                            <input type="text" name="zipcode" id="zipcode" readonly required class="w-full px-4 py-2 border rounded-lg bg-slate-200 text-slate-600 outline-none cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition transform hover:-translate-y-0.5">
                        ยืนยันการสมัครสมาชิก
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const provinceSelect = document.getElementById('province');
            const districtSelect = document.getElementById('district');
            const subdistrictSelect = document.getElementById('subdistrict');
            const zipcodeInput = document.getElementById('zipcode');

            // 1. โหลดข้อมูลจังหวัดเมื่อเปิดหน้าเว็บ
            fetch('api_location.php?action=get_provinces')
                .then(res => res.json())
                .then(data => {
                    data.forEach(item => {
                        let option = new Option(item.province_name, item.province_code);
                        provinceSelect.add(option);
                    });
                });

            // 2. เมื่อเปลี่ยนจังหวัด -> โหลดข้อมูลอำเภอ
            provinceSelect.addEventListener('change', function() {
                // Reset Dropdown ถัดไป
                districtSelect.innerHTML = '<option value="">-- เลือกอำเภอ --</option>';
                subdistrictSelect.innerHTML = '<option value="">-- เลือกตำบล --</option>';
                zipcodeInput.value = '';
                
                if (this.value) {
                    districtSelect.disabled = false;
                    districtSelect.classList.remove('bg-slate-100');
                    districtSelect.classList.add('bg-white');
                    
                    fetch(`api_location.php?action=get_districts&province_code=${this.value}`)
                        .then(res => res.json())
                        .then(data => {
                            data.forEach(item => {
                                let option = new Option(item.district_name, item.district_code);
                                districtSelect.add(option);
                            });
                        });
                } else {
                    districtSelect.disabled = true;
                    subdistrictSelect.disabled = true;
                }
            });

            // 3. เมื่อเปลี่ยนอำเภอ -> โหลดข้อมูลตำบล
            districtSelect.addEventListener('change', function() {
                subdistrictSelect.innerHTML = '<option value="">-- เลือกตำบล --</option>';
                zipcodeInput.value = '';

                if (this.value) {
                    subdistrictSelect.disabled = false;
                    subdistrictSelect.classList.remove('bg-slate-100');
                    subdistrictSelect.classList.add('bg-white');

                    fetch(`api_location.php?action=get_subdistricts&district_code=${this.value}`)
                        .then(res => res.json())
                        .then(data => {
                            data.forEach(item => {
                                let option = new Option(item.subdistrict_name, item.subdistrict_code);
                                subdistrictSelect.add(option);
                            });
                        });
                } else {
                    subdistrictSelect.disabled = true;
                }
            });

            // 4. เมื่อเปลี่ยนตำบล -> โหลดรหัสไปรษณีย์อัตโนมัติ
            subdistrictSelect.addEventListener('change', function() {
                zipcodeInput.value = '';
                if (this.value) {
                    fetch(`api_location.php?action=get_zipcode&subdistrict_code=${this.value}`)
                        .then(res => res.json())
                        .then(data => {
                            if(data && data.zipcode) {
                                zipcodeInput.value = data.zipcode;
                            }
                        });
                }
            });
        });
    </script>
</body>
</html>