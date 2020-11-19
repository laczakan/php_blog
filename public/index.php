<?php

/**
 * Add PSR-4 autoloader.
 * It's for autoloading classes from file paths by the namespace\class\name.
 */

require 'autoloader.php';

// Create a new bootstrap object
$bootstrap = new App\Bootstrap();

// Run application
$bootstrap->run();
