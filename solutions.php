<?php

/**
 * Plugin Name: Solutions
 * Description: A WordPress CPT for solutions.
 * Version: 2.0.1
 * Author: James Boynton
 */

namespace Xzito\Solutions;

$autoload_path = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoload_path)) {
  require_once($autoload_path);
}

new Solutions();
