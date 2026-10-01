<?php
declare(strict_types=1);
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/location_bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
try {
    ensure_sri_lanka_locations($pdo);
    $district=(int)($_GET['district_id'] ?? 0);
    if ($district < 1) { echo json_encode([]); exit; }
    ensure_sri_lanka_cities($pdo,$district);
    $st=$pdo->prepare('SELECT id,name,postcode,latitude,longitude FROM cities WHERE district_id=? ORDER BY name');
    $st->execute([$district]);
    echo json_encode($st->fetchAll(), JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Unable to load towns/areas.'], JSON_UNESCAPED_UNICODE);
}
