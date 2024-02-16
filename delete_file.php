<?php
$folderPath = __DIR__;
$backupPath = __DIR__.'/backups/';

if (isset($_POST['file_name'])) {
    $fileName = $_POST['file_name'];
    $filePath = $folderPath . $fileName;

    // Check the file type
    $fileType = filetype($filePath);

    // Check if the file or directory exists before attempting to delete it
    if ($fileType === 'file' || $fileType === 'dir') {
        // If it's a directory, recursively delete its contents and then the directory
        if ($fileType === 'dir') {
            backupAndDeleteDirectory($filePath);
        } else {
            // If it's a file, create a backup copy and then delete the file
            backupAndDeleteFile($filePath);
        }
    } else {
        echo 'File or directory not found.';
    }
} else {
    echo 'Invalid request.';
}

// Function to recursively delete a directory and its contents after creating a backup copy
function backupAndDeleteDirectory($dir) {
    global $backupPath;

    if (!is_dir($dir)) {
        return false;
    }

    // Create a backup copy of the directory
    $timestamp = time();
    $backupDir = $backupPath . $timestamp . '_' . basename($dir);
    copyDirectory($dir, $backupDir);

    // Delete the original directory and its contents
    if (deleteDirectory($dir)) {
        echo 'Directory deleted successfully, and a backup copy created.';
    } else {
        echo 'Error deleting directory.';
    }
}

// Function to create a backup copy of a directory and its contents
function copyDirectory($source, $destination) {
    if (!is_dir($destination)) {
        mkdir($destination, 0777, true);
    }

    $files = scandir($source);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $srcFile = $source . '/' . $file;
            $destFile = $destination . '/' . $file;

            is_dir($srcFile) ? copyDirectory($srcFile, $destFile) : copy($srcFile, $destFile);
        }
    }
}

// Function to create a backup copy of a file and then delete it
function backupAndDeleteFile($file) {
    global $backupPath;

    // Create a backup copy of the file
    $timestamp = time();
    $backupFile = $backupPath . $timestamp . '_' . basename($file);
    if (copy($file, $backupFile)) {
        // Delete the original file
        if (unlink($file)) {
            echo 'File deleted successfully, and a backup copy created.';
        } else {
            echo 'Error deleting file.';
        }
    } else {
        echo 'Error creating backup copy.';
    }
}

// Function to recursively delete a directory and its contents
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }

    $files = array_diff(scandir($dir), array('.', '..'));

    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }

    return rmdir($dir);
}
?>
