<?php
$basePath = __DIR__;

if (isset($_GET['download'])) {
    $downloadPath = $basePath . '/' . $_GET['download'];

    if (is_dir($downloadPath)) {
        // Download the folder as a ZIP file
        downloadFolderAsZip($downloadPath);
    } elseif (file_exists($downloadPath)) {
        // Download the individual file
        downloadFile($downloadPath);
    } else {
        // Handle the case where the requested path doesn't exist
        http_response_code(404);
        echo "File or folder not found";
        exit;
    }
} else {
    // Handle invalid or missing download parameter
    http_response_code(400);
    echo "Invalid or missing 'download' parameter";
    exit;
}


// Function to download a folder as a ZIP file
function downloadFolderAsZip($folderPath) {
    $zipFilename = basename($folderPath) . '_' . time() . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($zipFilename, ZipArchive::CREATE) === true) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folderPath),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            // Get the relative path using the correct function
            $relativePath = ltrim(str_replace($folderPath, '', $item), DIRECTORY_SEPARATOR);

            if ($item->isDir()) {
                // Add the directory itself to the ZIP archive
                $zip->addEmptyDir($relativePath);
            } elseif ($item->isFile()) {
                // Add the file to the ZIP archive
                $zip->addFile($item->getPathname(), $relativePath);
            }
        }

        $zip->close();

        // Download the ZIP file
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($zipFilename));

        ob_clean();
        flush();
        readfile($zipFilename);

        // Remove the temporary ZIP file
        unlink($zipFilename);
        exit();
    } else {
        // Handle the case where ZIP creation fails
        http_response_code(500);
        echo 'Error creating ZIP archive';
        exit;
    }
}


// Function to download an individual file
function downloadFile($filePath) {
    if (file_exists($filePath)) {
        header('Content-Type: ' . mime_content_type($filePath));
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));

        ob_clean();
        flush();
        readfile($filePath);
        exit();
    } else {
        // Handle the case where the file does not exist
        http_response_code(404);
        echo 'File not found';
        exit;
    }
}


?>
