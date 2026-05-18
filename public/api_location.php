<?php
// public/api_location.php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

try {

    if ($action === 'get_provinces') {
        $stmt = $pdo->query("SELECT province_code, province_name FROM province ORDER BY province_name ASC");
        echo json_encode($stmt->fetchAll());

    } elseif ($action === 'get_districts' && isset($_GET['province_code'])) {
        $stmt = $pdo->prepare("SELECT district_code, district_name FROM district WHERE province_code = ? ORDER BY district_name ASC");
        $stmt->execute([$_GET['province_code']]);
        echo json_encode($stmt->fetchAll());

    } elseif ($action === 'get_subdistricts' && isset($_GET['district_code'])) {
        $stmt = $pdo->prepare("SELECT subdistrict_code, subdistrict_name FROM subdistrict WHERE district_code = ? ORDER BY subdistrict_name ASC");
        $stmt->execute([$_GET['district_code']]);
        echo json_encode($stmt->fetchAll());

    } elseif ($action === 'get_zipcode' && isset($_GET['subdistrict_code'])) {
        $stmt = $pdo->prepare("SELECT zipcode FROM zipcode WHERE subdistrict_code = ? LIMIT 1");
        $stmt->execute([$_GET['subdistrict_code']]);
        echo json_encode($stmt->fetch());
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>