<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);

    if (isset($input['urlInput']) && isset($input['currentPath'])) {
        // Get the current path from the JSON data
        $currentPath = $input['currentPath'];

        // Validate data if needed

        // Ensure the currentPath is an absolute path
        $currentPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $currentPath;

        // Get the URL from the JSON data
        $url = $input['urlInput'];

        // Extract the filename from the URL
        $filename = pathinfo($url, PATHINFO_FILENAME);

        // Determine the target directory and file path
        $targetDir = $currentPath . DIRECTORY_SEPARATOR; // Use the current path as the target directory

        // Use get_headers to retrieve content type (MIME type)
        $headers = get_headers($url, 1);
        $contentType = $headers['Content-Type'];

        // Extract extension from MIME type
        $extension = mimeToExtension($contentType);

        // Append extension to filename
        $filenameWithExtension = $filename . '.' . $extension;

        // Check if the file already exists
        $counter = 1;
        while (file_exists($targetDir . $filenameWithExtension)) {
            $filenameWithExtension = $filename . '_' . $counter . '.' . $extension;
            $counter++;
        }

        // Download the file from the URL and save it to the target directory
        $contents = file_get_contents($url);
        file_put_contents($targetDir . $filenameWithExtension, $contents);

        echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}

// Function to convert MIME type to file extension
function mimeToExtension($mime) {
    $mimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf',
        // Add more MIME types and corresponding extensions as needed
    ];

    return isset($mimeTypes[$mime]) ? $mimeTypes[$mime] : 'unknown';
}
?>
