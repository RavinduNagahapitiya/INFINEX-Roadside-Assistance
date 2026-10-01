<?php
declare(strict_types=1);
require __DIR__.'/../config/config.php';
require __DIR__.'/../config/database.php';
require_once __DIR__.'/../includes/location_bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
try {
    ensure_sri_lanka_locations($pdo);
    $provinceId=(int)($_GET['province_id']??0);
    if($provinceId<=0){echo json_encode([]);exit;}
    $st=$pdo->prepare('SELECT id,name FROM districts WHERE province_id=? ORDER BY name');
    $st->execute([$provinceId]);
    echo json_encode($st->fetchAll(), JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Unable to load districts.'], JSON_UNESCAPED_UNICODE);
}
