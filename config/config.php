<?php
// ฟังก์ชันสำหรับอ่านและโหลดไฟล์ .env เข้าสู่ระบบ $_ENV
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // ข้ามบรรทัดที่เป็น Comment
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // แยกคีย์และค่าด้วยเครื่องหมาย =
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
    return true;
}

// เรียกใช้งานฟังก์ชันโดยชี้ไปที่ไฟล์ .env (ปรับ Path ตามความเหมาะสม)
loadEnv(__DIR__ . '/../.env');

// กำหนดค่าตัวแปรสำหรับการเชื่อมต่อ
$host    = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port    = $_ENV['DB_PORT'] ?? '3306';
$db      = $_ENV['DB_DATABASE'] ?? 'gpu_store';
$user    = $_ENV['DB_USERNAME'] ?? 'root';
$pass    = $_ENV['DB_PASSWORD'] ?? '';
$charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // พร้อมใช้งานตัวแปร $pdo ในการ Query ข้อมูล
} catch (\PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}