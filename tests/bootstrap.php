<?php
// Ensure we are at project root
define('APPROOT', __DIR__ . '/..');

// Composer autoload
require_once APPROOT . '/vendor/autoload.php';

// Minimal config constants for tests (override if env provides)
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'veterinaria_reino_animal_test');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8');
if (!defined('APP_URL')) define('APP_URL', 'http://localhost/Veterinaria_CS_G4/public');


