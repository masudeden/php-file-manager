<?php
$folderPath = realpath(__DIR__) . DIRECTORY_SEPARATOR;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['old_file_name']) && isset($_POST['new_file_name'])) {
    $oldFileName = $_POST['old_file_name'];
    $newFileName = $_POST['new_file_name'];
    $oldFilePath = $folderPath . $oldFileName;
    $newFilePath = $folderPath . $newFileName;

    // Check if the new file path already exists
    if (file_exists($newFilePath)) {
        echo 'File with the new name already exists.';
        exit;
    }

    // Check if the old file exists before renaming
    if (file_exists($oldFilePath)) {
        // Rename the file
        if (rename($oldFilePath, $newFilePath)) {
            echo 'File renamed successfully.';
        } else {
            echo 'Error renaming file.';
        }
    } else {
        echo 'Old file not found.';
    }
} else {
    echo 'Invalid request.';
}
?>
