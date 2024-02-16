<?php

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data sent via AJAX
    $itemName = $_POST['itemName'];
    $itemType = $_POST['itemType'];
    $currentPath = $_POST['currentPath'];

    // Validate data if needed

    // Ensure the currentPath is an absolute path
    $currentPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $currentPath;

    // Check if the directory exists, if not, create it
    if (!is_dir($currentPath)) {
        mkdir($currentPath, 0777, true);
    }

    // Create a new item (file or folder)
    $result = createNewItem($currentPath, $itemName, $itemType);

    // Return the result as a JSON response
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
} else {
    // Handle invalid requests or direct access to the file
    http_response_code(403);
    echo 'Forbidden';
}

function createNewItem($currentPath, $itemName, $itemType)
{
    // Validate input and perform necessary checks

    // Construct the full path for the new item
    $fullPath = $currentPath . DIRECTORY_SEPARATOR . $itemName;

    // Create a new folder or file based on the item type
    if ($itemType === 'folder') {
        // Create a new folder
        return mkdir($fullPath, 0777, true);
    } elseif ($itemType === 'file') {
        // Create a new file (you may need to adjust the permissions)
        $fileHandle = fopen($fullPath, 'w+');
        fclose($fileHandle);
        return true;
    }

    // Return false if the item type is neither 'folder' nor 'file'
    return false;
}
?>
