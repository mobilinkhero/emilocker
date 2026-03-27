<?php

/**
 * Laravel Application Entry Point (Root Directory)
 * 
 * This file exists because the web server document root is set to the project root
 * instead of the public directory. It forwards all requests to public/index.php.
 */

// Forward to the actual Laravel entry point
require __DIR__.'/public/index.php';
