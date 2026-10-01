<?php
$title='Select Location';
require __DIR__.'/../includes/header.php';
require_login('customer');
require __DIR__.'/../config/database.php';
$cat=(int)($_GET['category']??0);
$vehicleId=(int)($_GET['vehicle']??0);
$vehicleSt=$pdo->prepare('SELECT * FROM vehicles WHERE id=? AND customer_id=? LIMIT 1');
$vehicleSt->execute([$vehicleId,$_SESSION['user_id']]);
$vehicle=$vehicleSt->fetch();
if(!$vehicle) redirect(BASE_URL.'customer/vehicle-details.php');
$st=$pdo->prepare("SELECT * FROM service_categories WHERE id=? AND status='active'");
$st->execute([$cat]);
$service=$st->fetch();
if(!$service) redirect(BASE_URL.'customer/select-problem.php?vehicle='.$vehicleId);
$subs=$pdo->prepare("SELECT * FROM service_subproblems WHERE category_id=? AND status='active' ORDER BY name");
$subs->execute([$cat]);
?>
<section class="section-pad"><div class="container"><div class="infinex-stepper mb-4"><span class="done"><b><i class="bi bi-check"></i></b> Vehicle details</span><i class="bi bi-chevron-right"></i><span class="done"><b><i class="bi bi-check"></i></b> Service</span><i class="bi bi-chevron-right"></i><span class="active"><b>3</b> Location</span><i class="bi bi-chevron-right"></i><span><b>4</b> Payment</span></div><div class="row justify-content-center"><div class="col-lg-9"><div class="card border-0 shadow-sm p-4 rounded-4">
<span class="text-warning fw-bold">STEP 3</span><h1 class="fw-bold mt-2">Where did the vehicle issue occur?</h1>
<div class="vehicle-summary-strip mb-4"><i class="bi bi-car-front-fill"></i><div><strong><?=e($vehicle['brand'].' '.$vehicle['model'])?></strong><small><?=e($vehicle['registration_number'])?> · <?=e($vehicle['vehicle_type'])?> · <?=e($vehicle['transmission'])?></small></div><a href="vehicle-details.php" class="btn btn-sm btn-outline-secondary ms-auto">Edit</a></div>
<p class="text-muted"><?=e($service['name'])?> · Access fee Rs. <?=number_format($service['access_fee'],0)?></p>
<form action="create-request.php" method="post" id="locationForm">
<input type="hidden" name="category_id" value="<?=$cat?>"><input type="hidden" name="vehicle_id" value="<?=$vehicleId?>">
<label class="form-label">Specific problem</label><select class="form-select mb-3" name="subproblem_id"><option value="">Select a specific problem</option><?php foreach($subs as $s):?><option value="<?=$s['id']?>"><?=e($s['name'])?></option><?php endforeach;?></select>
<div class="location-picker">
<label class="location-label">Your Current Location / Address / Landmark <span class="text-danger">*</span></label>
<div class="location-search-wrapper"><i class="bi bi-geo-alt location-search-icon"></i><input type="text" id="locationSearch" class="location-search" placeholder="Search your exact location, address or landmark..." autocomplete="off"></div>
<div class="location-actions"><button type="button" id="currentLocationBtn" class="current-location-btn"><i class="bi bi-crosshair"></i> Use my current location</button><span id="locationStatus" class="location-status"></span></div>
<div id="locationMap" class="location-map"></div>
<div id="selectedLocation" class="selected-location"><i class="bi bi-geo-alt"></i><div><strong>Location not selected</strong><small>Search for the breakdown site or use your current GPS location.</small></div></div>
<input type="hidden" name="address" id="address"><input type="hidden" name="latitude" id="latitude"><input type="hidden" name="longitude" id="longitude"><input type="hidden" name="location_accuracy" id="location_accuracy">
</div>
<button class="btn btn-dark btn-lg w-100 mt-3" id="continueBtn" disabled>Continue to payment <i class="bi bi-arrow-right"></i></button>
</form></div></div></div></div></section>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let locationMap=null,locationMarker=null,searchTimer=null,searchController=null;
const btn=document.getElementById('continueBtn'),searchInput=document.getElementById('locationSearch'),statusEl=document.getElementById('locationStatus'),addressInput=document.getElementById('address'),latInput=document.getElementById('latitude'),lngInput=document.getElementById('longitude'),accInput=document.getElementById('location_accuracy'),selectedLocation=document.getElementById('selectedLocation');
function updateContinue(){btn.disabled=!(addressInput.value.trim()&&latInput.value&&lngInput.value);}
function escapeHtml(value){return String(value).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));}
function setSelectedLocation(lat,lng,address,accuracy=''){lat=Number(lat);lng=Number(lng);latInput.value=lat.toFixed(8);lngInput.value=lng.toFixed(8);accInput.value=(accuracy!==''&&accuracy!=null)?Number(accuracy).toFixed(2):'';addressInput.value=address||`${lat.toFixed(6)}, ${lng.toFixed(6)}`;searchInput.value=address||'';if(locationMap){locationMap.setView([lat,lng],17);if(!locationMarker){locationMarker=L.marker([lat,lng],{draggable:true}).addTo(locationMap);locationMarker.on('dragend',()=>reverseGeocode(locationMarker.getLatLng().lat,locationMarker.getLatLng().lng));}else locationMarker.setLatLng([lat,lng]);}selectedLocation.innerHTML='<i class="bi bi-geo-alt-fill"></i><div><strong>Breakdown location selected</strong><small>'+escapeHtml(address||'Selected coordinates')+'</small></div>';statusEl.textContent=accuracy?`GPS accuracy ±${Math.round(accuracy)} m`:'Location selected';updateContinue();}
async function reverseGeocode(lat,lng){statusEl.textContent='Finding the address...';try{const r=await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=18&addressdetails=1`);if(!r.ok)throw Error();const d=await r.json();setSelectedLocation(lat,lng,d.display_name||`${lat.toFixed(6)}, ${lng.toFixed(6)}`,'');}catch(e){setSelectedLocation(lat,lng,`${lat.toFixed(6)}, ${lng.toFixed(6)}`,'');statusEl.textContent='Coordinates selected; address lookup unavailable.';}}
function initLocationPicker(){locationMap=L.map('locationMap').setView([7.8731,80.7718],7);L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap contributors'}).addTo(locationMap);locationMap.on('click',e=>reverseGeocode(e.latlng.lat,e.latlng.lng));}
function showSuggestions(results){let box=document.getElementById('locationSuggestions');if(!box){box=document.createElement('div');box.id='locationSuggestions';box.className='location-suggestions';searchInput.parentElement.parentElement.appendChild(box);}box.innerHTML='';results.forEach(x=>{const b=document.createElement('button');b.type='button';b.className='location-suggestion';b.innerHTML='<i class="bi bi-geo-alt"></i><span>'+escapeHtml(x.display_name)+'</span>';b.onclick=()=>{box.style.display='none';setSelectedLocation(x.lat,x.lon,x.display_name,'');};box.appendChild(b)});box.style.display=results.length?'block':'none';}
function hideSearchSuggestions(){const b=document.getElementById('locationSuggestions');if(b)b.style.display='none';}
async function searchLocations(query){const clean=query.trim();if(clean.length<3){hideSearchSuggestions();return;}if(searchController)searchController.abort();searchController=new AbortController();statusEl.textContent='Searching locations...';try{const r=await fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&limit=6&countrycodes=lk&q='+encodeURIComponent(clean),{signal:searchController.signal});if(!r.ok)throw Error();const d=await r.json();showSuggestions(d);statusEl.textContent=d.length?'Select a result or choose a map point.':'No matching location found.';}catch(e){if(e.name!=='AbortError')statusEl.textContent='Location search is temporarily unavailable.';}}
searchInput.addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(()=>searchLocations(searchInput.value),400)});
document.addEventListener('click',e=>{if(!e.target.closest('.location-search-wrapper')&&!e.target.closest('.location-suggestions'))hideSearchSuggestions();});
document.getElementById('currentLocationBtn').addEventListener('click',()=>{if(!navigator.geolocation){statusEl.textContent='Your browser does not support GPS location.';return;}statusEl.textContent='Getting your current GPS location...';navigator.geolocation.getCurrentPosition(async pos=>{const {latitude,longitude,accuracy}=pos.coords;try{const r=await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitude}&lon=${longitude}&zoom=18&addressdetails=1`);const d=await r.json();setSelectedLocation(latitude,longitude,d.display_name||'',accuracy);}catch(e){setSelectedLocation(latitude,longitude,'',accuracy);}},()=>{statusEl.textContent='Location permission was denied or unavailable. You can search or select a point on the map.';},{enableHighAccuracy:true,timeout:20000,maximumAge:0});});
initLocationPicker();updateContinue();
</script>
<?php require __DIR__.'/../includes/footer.php'; ?>
