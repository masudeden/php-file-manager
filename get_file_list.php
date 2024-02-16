<?php
$baseFolderPath = __DIR__;

// Get the subfolder path if provided
$subfolderPath = isset($_GET['subfolder']) ? $_GET['subfolder'] : '';
$folderPath = $baseFolderPath . '/' . $subfolderPath;
if ($subfolderPath === '') {
    $subfolderPath = null;
}

// Get the list of files and directories in the folder
$items = scandir($folderPath);

// Mapping between file extensions and icon classes
$fileTypeIcons = [
    'jpg' => 'far fa-image',
    'jpeg' => 'far fa-image',
    'png' => 'fas fa-file-image',
    'gif' => 'fas fa-file-image',
    'pdf' => 'fas fa-file-pdf',
    'txt' => 'fas fa-file-alt',
    'docx' => 'fas fa-file-word',
    'doc' => 'fas fa-file-word',
    // Add more file types as needed
];

// Display the list of files and directories in a table
echo '<!-- Add this button to your HTML -->';
echo '<table class="table table-bordered table-hover table-striped bg-white dataTable" id="fileListTable">';
echo '<thead class="thead-white">';
echo '<tr>';
echo '<th scope="col">File Name</th>';
echo '<th scope="col">Type</th>';
echo '<th scope="col">Size</th>';
echo '<th scope="col">Modified</th>';
echo '<th scope="col">Uploaded</th>';
echo '<th scope="col">Actions</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($items as $item) {
    if ($item != '.' && $item != '..') {
        $itemPath = $folderPath . '/' . $item;

        if (is_dir($itemPath)) {
            // It's a directory
            $folderSize = calculateFolderSize($itemPath);
            echo '<tr class="folder" data-folder="' . $subfolderPath . '/' . $item . '">';
            echo '<td><i class="fas fa-folder"></i> ' . $item . '</td>';
            echo '<td>Folder</td>';
            echo '<td>' . formatBytes($folderSize) . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', filemtime($itemPath)) . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', filectime($itemPath)) . '</td>';
            echo '<td>';
            echo '<div class="btn-group" role="group">';
            echo '<a class="btn btn-secondary btn-sm" href="download.php?download='. $item . '"><i class="fas fa-download fa-sm"></i></a>';
            echo '<button class="btn btn-danger btn-sm delete-btn" data-file="' . $subfolderPath . '/' . $item . '"><i class="fas fa-trash fa-sm"></i></button>';
            echo '<button class="btn btn-info btn-sm edit-btn" data-file="' . $subfolderPath . '/' . $item . '"><i class="fas fa-edit fa-sm"></i></button>';
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        } else {
            // It's a file
            $fileExtension = pathinfo($item, PATHINFO_EXTENSION);
            $iconClass = isset($fileTypeIcons[$fileExtension]) ? $fileTypeIcons[$fileExtension] : 'fas fa-file';

            echo '<tr>';
            echo '<td> <i class="' . $iconClass . '"></i> ' . $item . '</td>';

            echo '<td>' . strtoupper($fileExtension) . '</td>';
            echo '<td>' . formatBytes(filesize($itemPath)) . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', filemtime($itemPath)) . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', filectime($itemPath)) . '</td>';
            echo '<td>';
            echo '<div class="btn-group" role="group">';
            echo '<button class="btn btn-secondary btn-sm download-btn" data-file="' . $subfolderPath . '/' . $item . '"><i class="fas fa-download fa-sm"></i></button>';
            echo '<button class="btn btn-danger btn-sm delete-btn" data-file="' . $subfolderPath . '/' . $item . '"><i class="fas fa-trash fa-sm"></i></button>';
            echo '<button class="btn btn-info btn-sm edit-btn" data-file="' . $subfolderPath . '/' . $item . '"><i class="fas fa-edit fa-sm"></i></button>';
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        }
    }
}

echo '</tbody>';
echo '</table>';

// Function to calculate the total size of a folder
function calculateFolderSize($folderPath) {
    $totalSize = 0;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folderPath));

    foreach ($files as $file) {
        if ($file->isFile()) {
            $totalSize += $file->getSize();
        }
    }

    return $totalSize;
}

// Function to format file size
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

?>
