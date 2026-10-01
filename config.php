<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) session_start();

define('APP_NAME', 'INFINEX');
define('BASE_URL', '/INFINEX/');

// Add your Google Maps Platform browser key here for the interactive location picker.
// Restrict the key to your local site (for example http://localhost/*) and only the
// Maps JavaScript API / Places API as appropriate. Leave blank to use manual address entry.
define('GOOGLE_MAPS_API_KEY', '');
