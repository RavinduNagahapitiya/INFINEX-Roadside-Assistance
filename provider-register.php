<?php
// Public provider registration has been intentionally disabled.
// Providers are created and managed by an INFINEX administrator.
require __DIR__.'/../config/config.php';
require_once __DIR__.'/../includes/functions.php';
redirect(BASE_URL.'auth/admin-login.php?provider_registration=disabled');
