<?php

// Check REMOTE_ADDR and server variables
echo json_encode([
    'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'NOT SET',
    'REMOTE_ADDR_type' => gettype($_SERVER['REMOTE_ADDR'] ?? null),
    'memory_limit' => ini_get('memory_limit'),
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'NOT SET',
], JSON_PRETTY_PRINT);
