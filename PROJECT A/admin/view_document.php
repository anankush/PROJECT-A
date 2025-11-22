<?php
session_start();
require_once '../config/db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check admin session
if (!isset($_SESSION['admin_id']) || !isset($_GET['file'])) {
    http_response_code(403);
    exit('Access denied');
}

// Improved file path handling with subfolder support
$requested_file = $_GET['file']; // Get the full path including subfolder
$uploads_base = __DIR__ . '/../uploads/'; // Base uploads directory

// Clean the path to prevent directory traversal
$requested_file = str_replace('\\', '/', $requested_file); // Normalize slashes
$requested_file = str_replace('../', '', $requested_file); // Remove parent directory references

$file_path = $uploads_base . $requested_file;

// Debug logging
error_log("Attempting to access file: " . $file_path);
error_log("Upload base directory: " . $uploads_base);

// Validate the path is within uploads directory
if (strpos(realpath($file_path), realpath($uploads_base)) !== 0) {
    error_log("Security: Invalid file path requested: " . $_GET['file']);
    http_response_code(403);
    exit('Invalid file path');
}

// Check if file exists
if (!file_exists($file_path)) {
    error_log("File not found: " . $file_path);
    http_response_code(404);
    exit("File not found: " . htmlspecialchars($requested_file));
}

// Get and validate mime type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_path);
finfo_close($finfo);

$allowed_types = [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/gif',
    'application/pdf'
];

if (!in_array($mime_type, $allowed_types)) {
    error_log("Invalid file type: " . $mime_type);
    http_response_code(403);
    exit('Invalid file type');
}

// Output file with correct headers
header('Content-Type: ' . $mime_type);
header('Content-Disposition: inline; filename="' . $requested_file . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($file_path);
exit();
