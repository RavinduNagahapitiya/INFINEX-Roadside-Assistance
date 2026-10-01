<?php
/**
 * Ensures the standard Sri Lankan province/district reference data exists.
 * Read-only location endpoints use this so an existing INFINEX database can
 * recover missing location reference rows without requiring a full DB reset.
 */
function ensure_sri_lanka_locations(PDO $pdo): void {
    $provinces = [
        1=>'Western', 2=>'Central', 3=>'Southern', 4=>'Northern', 5=>'Eastern',
        6=>'North Western', 7=>'North Central', 8=>'Uva', 9=>'Sabaragamuwa'
    ];
    $districts = [
        1=>[1,'Colombo'], 2=>[1,'Gampaha'], 3=>[1,'Kalutara'],
        4=>[2,'Kandy'], 5=>[2,'Matale'], 6=>[2,'Nuwara Eliya'],
        7=>[3,'Galle'], 8=>[3,'Matara'], 9=>[3,'Hambantota'],
        10=>[4,'Jaffna'], 11=>[4,'Kilinochchi'], 12=>[4,'Mannar'], 13=>[4,'Vavuniya'], 14=>[4,'Mullaitivu'],
        15=>[5,'Batticaloa'], 16=>[5,'Ampara'], 17=>[5,'Trincomalee'],
        18=>[6,'Kurunegala'], 19=>[6,'Puttalam'],
        20=>[7,'Anuradhapura'], 21=>[7,'Polonnaruwa'],
        22=>[8,'Badulla'], 23=>[8,'Monaragala'],
        24=>[9,'Ratnapura'], 25=>[9,'Kegalle']
    ];

    $pdo->beginTransaction();
    try {
        $p = $pdo->prepare('INSERT INTO provinces (id,name) VALUES (?,?) ON DUPLICATE KEY UPDATE name=VALUES(name)');
        foreach ($provinces as $id=>$name) $p->execute([$id,$name]);

        $d = $pdo->prepare('INSERT INTO districts (id,province_id,name) VALUES (?,?,?) ON DUPLICATE KEY UPDATE province_id=VALUES(province_id), name=VALUES(name)');
        foreach ($districts as $id=>$row) $d->execute([$id,$row[0],$row[1]]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function ensure_sri_lanka_cities(PDO $pdo, int $districtId): void {
    if ($districtId < 1) return;
    $file = __DIR__ . '/../database/city_area_data.php';
    if (!is_file($file)) return;
    $data = require $file;
    $names = $data[$districtId] ?? [];
    if (!$names) return;
    $insert = $pdo->prepare('INSERT IGNORE INTO cities (district_id,name) VALUES (?,?)');
    foreach ($names as $name) $insert->execute([$districtId,$name]);
}
