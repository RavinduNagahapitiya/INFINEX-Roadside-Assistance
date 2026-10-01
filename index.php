<?php
$title='Home';
require __DIR__.'/includes/header.php';
require __DIR__.'/config/database.php';
$services=$pdo->query("SELECT * FROM service_categories WHERE status='active' ORDER BY id")->fetchAll();

$serviceMeta = [
    'Mechanical Issues' => ['icon'=>'bi-gear-wide-connected','class'=>'service-blue','short'=>'Engine, transmission, brakes and other mechanical problems.'],
    'Tyre Issues' => ['icon'=>'bi-disc','class'=>'service-green','short'=>'Flat tyres, punctures, replacement and other tyre-related problems.'],
    'Battery Issues' => ['icon'=>'bi-battery-half','class'=>'service-red','short'=>'Dead battery, jump-start, battery replacement and related issues.'],
    'Vehicle Electrical Issues' => ['icon'=>'bi-plug-fill','class'=>'service-yellow','short'=>'Electrical faults, lighting, starter, alternator and other electrical problems.'],
];
?>

<section class="hero-section">
  <div class="hero-glow hero-glow-one"></div>
  <div class="hero-glow hero-glow-two"></div>
  <div class="container position-relative">
    <div class="row align-items-center g-4 g-xl-5">
      <div class="col-lg-6">
        <div class="hero-copy">
          <div class="eyebrow"><span></span> WELCOME TO INFINEX</div>
          <h1>Help on the Road,<br><strong>When You Need It</strong></h1>
          <p class="hero-description">INFINEX connects you with suitable roadside service providers quickly and easily. Get back on the road with confidence.</p>
          <div class="hero-actions">
            <a href="<?= BASE_URL ?>customer/vehicle-details.php" class="btn btn-primary hero-primary"><i class="bi bi-tools me-2"></i>Get Vehicle Assistance</a>
            <a href="#how-it-works" class="btn hero-secondary"><i class="bi bi-play-circle-fill me-2"></i>How It Works</a>
          </div>
          <div class="hero-highlights">
            <div class="highlight-item"><span class="highlight-icon"><i class="bi bi-lightning-charge-fill"></i></span><div><strong>Fast &amp; Reliable</strong><small>Get help quickly</small></div></div>
            <div class="highlight-item"><span class="highlight-icon"><i class="bi bi-shield-check-fill"></i></span><div><strong>Trusted Providers</strong><small>Verified professionals</small></div></div>
            <div class="highlight-item"><span class="highlight-icon"><i class="bi bi-geo-alt-fill"></i></span><div><strong>Location Based</strong><small>Find help near you</small></div></div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-visual-wrap">
          <div class="hero-visual-card">
            <img src="<?= BASE_URL ?>assets/images/infinex-hero-logo.png" class="hero-logo-image" alt="INFINEX Roadside Assistance">
          </div>
          <div class="floating-badge badge-top"><i class="bi bi-shield-check-fill"></i><span><strong>Quick Assistance</strong><small>Built for roadside emergencies</small></span></div>
          <div class="floating-badge badge-bottom"><i class="bi bi-geo-alt-fill"></i><span><strong>Islandwide Locations</strong><small>District &amp; city based matching</small></span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="services" class="services-section">
  <div class="container">
    <div class="section-heading">
      <div class="eyebrow blue"><span></span> OUR SERVICES</div>
      <h2>Vehicle Assistance Services</h2>
      <p>Select the category that best matches your problem.</p>
    </div>
    <div class="row g-4">
      <?php foreach($services as $s):
        $meta=$serviceMeta[$s['name']] ?? ['icon'=>'bi-wrench-adjustable','class'=>'service-blue','short'=>$s['description']];
      ?>
      <div class="col-md-6 col-xl-3">
        <a class="service-card <?= e($meta['class']) ?>" href="<?= BASE_URL ?>customer/vehicle-details.php">
          <div class="service-icon"><i class="bi <?= e($meta['icon']) ?>"></i></div>
          <h3><?= e($s['name']) ?></h3>
          <p><?= e($meta['short']) ?></p>
          <div class="service-bottom"><span>Access Fee: Rs. <?= number_format((float)$s['access_fee']) ?></span><b><i class="bi bi-arrow-right"></i></b></div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="how-it-works" class="how-section">
  <div class="container">
    <div class="section-heading centered">
      <div class="eyebrow blue"><span></span> HOW IT WORKS</div>
      <h2>Roadside help in four simple steps</h2>
      <p>INFINEX keeps the platform process clear from the first request to provider contact.</p>
    </div>
    <div class="row g-4 process-row">
      <div class="col-md-6 col-lg-3"><div class="process-card"><div class="step-number">01</div><div class="process-icon"><i class="bi bi-car-front"></i></div><h3>Vehicle Details</h3><p>Tell us your vehicle type, brand, model, transmission, registration and other useful specifications.</p></div></div>
      <div class="col-md-6 col-lg-3"><div class="process-card"><div class="step-number">02</div><div class="process-icon"><i class="bi bi-tools"></i></div><h3>Select Your Problem</h3><p>Choose the service category and specific issue that best matches your vehicle problem.</p></div></div>
      <div class="col-md-6 col-lg-3"><div class="process-card"><div class="step-number">03</div><div class="process-icon"><i class="bi bi-geo-alt"></i></div><h3>Choose Your Location</h3><p>Search for your exact address or landmark, or use your current GPS location.</p></div></div>
      <div class="col-md-6 col-lg-3"><div class="process-card"><div class="step-number">04</div><div class="process-icon"><i class="bi bi-unlock"></i></div><h3>Pay &amp; Contact</h3><p>Pay the INFINEX access fee to unlock suitable provider details and contact options.</p></div></div>
    </div>
    <div class="fee-note"><i class="bi bi-info-circle-fill"></i><div><strong>Platform fee &amp; provider charges are separate.</strong><br><span>The INFINEX access fee unlocks provider contact details. Any repair or service charge is paid separately to the provider.</span></div></div>
  </div>
</section>

<section id="contact" class="contact-section">
  <div class="container">
    <div class="contact-box">
      <div><div class="eyebrow light"><span></span> NEED ASSISTANCE?</div><h2>Get roadside help when you need it.</h2><p>Start an INFINEX assistance request and tell us what is happening with your vehicle.</p></div>
      <a href="<?= BASE_URL ?>customer/vehicle-details.php" class="btn contact-btn">Start Assistance <i class="bi bi-arrow-right ms-2"></i></a>
    </div>
  </div>
</section>

<?php require __DIR__.'/includes/footer.php'; ?>
