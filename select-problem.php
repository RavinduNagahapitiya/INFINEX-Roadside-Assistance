<?php
$title='Select Service';
require __DIR__.'/../includes/header.php';
require __DIR__.'/../config/database.php';
require_valid_customer($pdo);
$vehicleId=(int)($_GET['vehicle']??0);
$st=$pdo->prepare('SELECT * FROM vehicles WHERE id=? AND customer_id=? LIMIT 1');
$st->execute([$vehicleId,$_SESSION['user_id']]);
$vehicle=$st->fetch();
if(!$vehicle) redirect(BASE_URL.'customer/vehicle-details.php');
$services=$pdo->query("SELECT * FROM service_categories WHERE status='active' ORDER BY id")->fetchAll();
?>
<section class="section-pad">
  <div class="container">
    <div class="infinex-stepper mb-4">
      <span class="done"><b><i class="bi bi-check"></i></b> Vehicle details</span><i class="bi bi-chevron-right"></i>
      <span class="active"><b>2</b> Service</span><i class="bi bi-chevron-right"></i>
      <span><b>3</b> Location</span><i class="bi bi-chevron-right"></i>
      <span><b>4</b> Payment</span>
    </div>
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-5">
      <div><span class="text-warning fw-bold">STEP 2</span><h1 class="fw-bold mt-2 mb-2">What happened to your vehicle?</h1><p class="text-muted mb-0">Choose the issue category that best matches your current problem.</p></div>
      <div class="vehicle-summary-mini"><i class="bi bi-car-front-fill"></i><div><strong><?=e($vehicle['brand'].' '.$vehicle['model'])?></strong><small><?=e($vehicle['registration_number'])?> · <?=e($vehicle['vehicle_type'])?></small></div></div>
    </div>
    <div class="row g-4">
      <?php foreach($services as $s): ?>
      <div class="col-md-6 col-lg-3">
        <a class="text-decoration-none text-dark" href="select-location.php?vehicle=<?=$vehicleId?>&category=<?=$s['id']?>">
          <div class="card service-card p-4 h-100">
            <div class="service-icon mb-3"><i class="bi bi-tools"></i></div>
            <h5 class="fw-bold"><?=e($s['name'])?></h5>
            <p class="text-muted small"><?=e($s['description'])?></p>
            <div class="price text-dark">Rs. <?=number_format($s['access_fee'],0)?></div>
            <small class="text-muted">platform access fee</small>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-4"><a href="vehicle-details.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Edit vehicle details</a></div>
  </div>
</section>
<?php require __DIR__.'/../includes/footer.php'; ?>
