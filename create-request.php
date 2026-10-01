<?php
require __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/functions.php';
require __DIR__.'/../config/database.php';
require_valid_customer($pdo);
if($_SERVER['REQUEST_METHOD']!=='POST') redirect(BASE_URL.'customer/vehicle-details.php');
$vehicleId=(int)($_POST['vehicle_id']??0);$categoryId=(int)($_POST['category_id']??0);$subproblemId=($_POST['subproblem_id']??'')!==''?(int)$_POST['subproblem_id']:null;$provinceId=null;$districtId=null;$cityId=null;$address=trim($_POST['address']??'');$latitude=($_POST['latitude']??'')!==''?(float)$_POST['latitude']:null;$longitude=($_POST['longitude']??'')!==''?(float)$_POST['longitude']:null;$accuracy=($_POST['location_accuracy']??'')!==''?(float)$_POST['location_accuracy']:null;
if($vehicleId<=0||$categoryId<=0||$address===''||$latitude===null||$longitude===null)exit('Please select your exact current breakdown location on the map or by GPS.');
$check=$pdo->prepare('SELECT id FROM vehicles WHERE id=? AND customer_id=? LIMIT 1');$check->execute([$vehicleId,$_SESSION['user_id']]);if(!$check->fetchColumn())exit('Invalid vehicle selection.');
$check=$pdo->prepare("SELECT id FROM service_categories WHERE id=? AND status='active' LIMIT 1");$check->execute([$categoryId]);if(!$check->fetchColumn())exit('Invalid service selection.');
if($subproblemId!==null){$check=$pdo->prepare("SELECT id FROM service_subproblems WHERE id=? AND category_id=? AND status='active' LIMIT 1");$check->execute([$subproblemId,$categoryId]);if(!$check->fetchColumn())$subproblemId=null;}
$st=$pdo->prepare("INSERT INTO assistance_requests (customer_id,vehicle_id,category_id,subproblem_id,province_id,district_id,city_id,address,latitude,longitude,location_accuracy,status) VALUES(?,?,?,?,?,?,?,?,?,?,?, 'payment_pending')");$st->execute([$_SESSION['user_id'],$vehicleId,$categoryId,$subproblemId,$provinceId,$districtId,$cityId,$address,$latitude,$longitude,$accuracy]);$id=$pdo->lastInsertId();redirect(BASE_URL.'customer/payment.php?request='.$id);
