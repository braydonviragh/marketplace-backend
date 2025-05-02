<?php
// Ultra-simple health check file - no Laravel, no dependencies
header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

echo 'check complete';
exit(0); // Exit with success status 