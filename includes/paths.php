<?php
// Define absolute paths
define('ROOT_DIR', dirname(__DIR__));
define('INCLUDE_DIR', ROOT_DIR . '/includes');

// Detect protocol and base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$base_url = $protocol . $_SERVER['HTTP_HOST'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', ROOT_DIR);

// Define application constants
define('APP_URL', rtrim($base_url, '/'));
define('APP_NAME', 'Device Management System');