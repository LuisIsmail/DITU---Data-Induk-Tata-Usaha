<?php
/**
 * Import API (simple passthrough - actual import is done client-side via GS.addRow)
 * This file is a stub since imports use the existing CRUD API.
 * However, we keep it for potential future server-side import needs.
 */
require_once dirname(__DIR__) . '/config.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

requireCSRF();

echo json_encode(['success' => true, 'message' => 'Import API is operational. Use GS.addRow for individual row imports.']);
