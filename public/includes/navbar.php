<?php
// ตรวจสอบก่อนว่ามีการประกาศ session_start() หรือยังเพื่อป้องกัน Warning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="bg-slate-900 text-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <a href="index.php" class="text-2xl font-bold tracking-wider text-emerald-400">
            GPU<span class="text-white">STORE</span>
        </a>
        
        <div class="space-x-6 hidden md:flex items-center">
            <a href="index.php" class="hover:text-emerald-400 transition">หน้าแรก</a>
            <a href="#" class="hover:text-emerald-400 transition">สินค้าทั้งหมด</a>
            <a href="#" class="hover:text-emerald-400 transition">เช็คสถานะสินค้า</a>
            
            <a href="#" class="bg-emerald-500 hover:bg-emerald-600 px-4 py-2 rounded-md font-medium transition flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                ตะกร้าสินค้า (0)
            </a>
            
            <div class="border-l border-slate-700 pl-6 space-x-4 flex items-center">
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <span class="text-slate-300 text-sm">
    สวัสดีคุณ, <a href="profile.php" class="text-emerald-400 underline font-bold hover:text-emerald-300"><?= htmlspecialchars($_SESSION['customer_first_name']) ?></a>
</span>
                    <a href="logout.php" class="bg-rose-600 hover:bg-rose-700 px-3 py-1.5 rounded-md text-sm font-medium transition">
                        ออกจากระบบ
                    </a>
                <?php else: ?>
                    <a href="login.php" class="hover:text-emerald-400 transition">เข้าสู่ระบบ</a>
                    <a href="register.php" class="bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-md font-medium transition">สมัครสมาชิก</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>