<?php
$title='Vehicle Details';
require __DIR__.'/../includes/header.php';
require __DIR__.'/../config/database.php';
require_valid_customer($pdo);
$customerId=(int)$_SESSION['user_id'];
$error='';
$vehicleTypes=$pdo->query("SELECT name FROM vehicle_types WHERE status='active' AND name<>'All Vehicle Types' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
$transmissions=['Manual','Automatic','CVT','Semi-Automatic','Other'];
$fuelTypes=['Petrol','Diesel','Hybrid','Electric','CNG','Other'];

$form=[
    'vehicle_type'=>'', 'brand'=>'', 'model'=>'', 'manufacture_year'=>'',
    'transmission'=>'', 'fuel_type'=>'', 'registration_number'=>'',
    'color'=>'', 'engine_capacity'=>'', 'notes'=>''
];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    foreach ($form as $key => $value) $form[$key]=trim((string)($_POST[$key]??''));
    $form['registration_number']=strtoupper(preg_replace('/\s+/', ' ', $form['registration_number']));

    $year=$form['manufacture_year']!=='' ? (int)$form['manufacture_year'] : null;
    $engine=$form['engine_capacity']!=='' ? (float)$form['engine_capacity'] : null;

    if (!in_array($form['vehicle_type'],$vehicleTypes,true)) $error='Please select a valid vehicle type.';
    elseif ($form['brand']==='' || strlen($form['brand'])>80) $error='Please enter a valid vehicle brand.';
    elseif ($form['model']==='' || strlen($form['model'])>80) $error='Please enter a valid vehicle model.';
    elseif ($year!==null && ($year<1950 || $year>(int)date('Y')+1)) $error='Please enter a valid manufacture year.';
    elseif (!in_array($form['transmission'],$transmissions,true)) $error='Please select the transmission type.';
    elseif (!in_array($form['fuel_type'],$fuelTypes,true)) $error='Please select the fuel type.';
    elseif ($form['registration_number']==='' || strlen($form['registration_number'])>30) $error='Please enter the vehicle registration number.';
    elseif ($engine!==null && ($engine<=0 || $engine>100000)) $error='Please enter a valid engine capacity.';
    else {
        $existing=$pdo->prepare('SELECT id FROM vehicles WHERE customer_id=? AND registration_number=? LIMIT 1');
        $existing->execute([$customerId,$form['registration_number']]);
        $vehicleId=$existing->fetchColumn();

        if ($vehicleId) {
            $st=$pdo->prepare('UPDATE vehicles SET vehicle_type=?,brand=?,model=?,manufacture_year=?,transmission=?,fuel_type=?,color=?,engine_capacity=?,notes=? WHERE id=? AND customer_id=?');
            $st->execute([$form['vehicle_type'],$form['brand'],$form['model'],$year,$form['transmission'],$form['fuel_type'],$form['color']?:null,$engine,$form['notes']?:null,$vehicleId,$customerId]);
        } else {
            $st=$pdo->prepare('INSERT INTO vehicles (customer_id,vehicle_type,brand,model,manufacture_year,transmission,fuel_type,registration_number,color,engine_capacity,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $st->execute([$customerId,$form['vehicle_type'],$form['brand'],$form['model'],$year,$form['transmission'],$form['fuel_type'],$form['registration_number'],$form['color']?:null,$engine,$form['notes']?:null]);
            $vehicleId=$pdo->lastInsertId();
        }
        redirect(BASE_URL.'customer/select-problem.php?vehicle='.(int)$vehicleId);
    }
}
?>
<section class="section-pad vehicle-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-xl-9 col-lg-10">
        <div class="mb-4">
          <div class="infinex-stepper mb-3">
            <span class="active"><b>1</b> Vehicle details</span><i class="bi bi-chevron-right"></i>
            <span><b>2</b> Service</span><i class="bi bi-chevron-right"></i>
            <span><b>3</b> Location</span><i class="bi bi-chevron-right"></i>
            <span><b>4</b> Payment</span>
          </div>
          <span class="text-warning fw-bold">STEP 1</span>
          <h1 class="fw-bold mt-2 mb-2">Tell us about your vehicle</h1>
          <p class="text-muted mb-0">Enter the vehicle details first. INFINEX will use these details to understand which assistance is appropriate before you select the problem category.</p>
        </div>

        <div class="card border-0 shadow-sm rounded-4 vehicle-form-card">
          <div class="card-body p-4 p-lg-5">
            <?php if($error): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?=e($error)?></div><?php endif; ?>

            <form method="post" id="vehicleForm" novalidate>
              <div class="vehicle-section-title"><span><i class="bi bi-car-front-fill"></i></span><div><h5>Vehicle identification</h5><p>These are required to start an assistance request.</p></div></div>
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Vehicle type <span class="text-danger">*</span></label>
                  <select name="vehicle_type" class="form-select" required>
                    <option value="">Select vehicle type</option>
                    <?php foreach($vehicleTypes as $type): ?><option value="<?=e($type)?>" <?=$form['vehicle_type']===$type?'selected':''?>><?=e($type)?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Registration number <span class="text-danger">*</span></label>
                  <input type="text" name="registration_number" class="form-control text-uppercase" value="<?=e($form['registration_number'])?>" placeholder="e.g. WP CAA-1234" maxlength="30" required>
                  <div class="form-text">Enter the registration exactly as shown on the vehicle.</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Brand / Make <span class="text-danger">*</span></label>
                  <input type="text" name="brand" class="form-control" value="<?=e($form['brand'])?>" placeholder="e.g. Toyota" maxlength="80" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Model <span class="text-danger">*</span></label>
                  <input type="text" name="model" class="form-control" value="<?=e($form['model'])?>" placeholder="e.g. Prius" maxlength="80" required>
                </div>
              </div>

              <div class="vehicle-section-title"><span><i class="bi bi-gear-wide-connected"></i></span><div><h5>Vehicle specifications</h5><p>Helpful information for the service provider.</p></div></div>
              <div class="row g-3 mb-4">
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Transmission <span class="text-danger">*</span></label>
                  <select name="transmission" class="form-select" required>
                    <option value="">Select transmission</option>
                    <?php foreach($transmissions as $item): ?><option value="<?=e($item)?>" <?=$form['transmission']===$item?'selected':''?>><?=e($item)?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Fuel type <span class="text-danger">*</span></label>
                  <select name="fuel_type" class="form-select" required>
                    <option value="">Select fuel type</option>
                    <?php foreach($fuelTypes as $item): ?><option value="<?=e($item)?>" <?=$form['fuel_type']===$item?'selected':''?>><?=e($item)?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Manufacture year</label>
                  <input type="number" name="manufacture_year" class="form-control" value="<?=e($form['manufacture_year'])?>" min="1950" max="<?=date('Y')+1?>" placeholder="e.g. 2021">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Vehicle colour</label>
                  <input type="text" name="color" class="form-control" value="<?=e($form['color'])?>" maxlength="40" placeholder="e.g. White">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Engine capacity (cc)</label>
                  <input type="number" name="engine_capacity" class="form-control" value="<?=e($form['engine_capacity'])?>" min="1" max="100000" step="1" placeholder="e.g. 1500">
                </div>
              </div>

              <div class="vehicle-section-title"><span><i class="bi bi-card-text"></i></span><div><h5>Additional information</h5><p>Optional details that may help the provider.</p></div></div>
              <div class="mb-4">
                <label class="form-label fw-semibold">Additional vehicle notes</label>
                <textarea name="notes" class="form-control" rows="3" maxlength="500" placeholder="Optional: modified vehicle, special equipment, unusual symptoms, or anything the provider should know."><?=e($form['notes'])?></textarea>
              </div>

              <div class="vehicle-info-note mb-4"><i class="bi bi-shield-check"></i><div><strong>Your vehicle details stay with your INFINEX account.</strong><small>They are attached to the assistance request so the provider can understand the vehicle before contacting you.</small></div></div>
              <button class="btn btn-primary btn-lg w-100 vehicle-continue" type="submit"><span>Continue to service selection</span><i class="bi bi-arrow-right"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
document.getElementById('vehicleForm').addEventListener('submit',function(e){
  if(!this.checkValidity()){e.preventDefault();e.stopPropagation();this.classList.add('was-validated');}
});
</script>
<?php require __DIR__.'/../includes/footer.php'; ?>
