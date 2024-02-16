<?php
// upload_file.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && isset($_POST['currentPath'])) {
    $currentPath = $_POST['currentPath'];

    // Validate data if needed

    // Ensure the currentPath is an absolute path
    $currentPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $currentPath;

    // Validate and sanitize the current path to prevent directory traversal attacks
    $currentPath = realpath($currentPath); // Get the canonicalized absolute pathname

    // Set the target directory as the current directory
    $targetDir = $currentPath;

    $originalFileName = basename($_FILES['file']['name']);
    $targetFile = $targetDir . DIRECTORY_SEPARATOR . $originalFileName;

    // Check if the file already exists
    if (file_exists($targetFile)) {
        // Generate a unique identifier (timestamp) and append it to the original filename
        $timestamp = time();
        $newFileName = pathinfo($originalFileName, PATHINFO_FILENAME) . '_' . $timestamp . '.' . pathinfo($originalFileName, PATHINFO_EXTENSION);

        // Update the target file with the new filename
        $targetFile = $targetDir . DIRECTORY_SEPARATOR . $newFileName;
    }

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        echo 'File uploaded successfully.';
    } else {
        echo 'Error uploading file.';
    }
} else {
    echo 'Invalid request.';
}
?>
