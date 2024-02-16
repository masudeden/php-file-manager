      // Initialize Dropzone
      Dropzone.autoDiscover = false; // Disable auto-initialization
$(document).ready(function () {


    var urlParams = new URLSearchParams(window.location.search);
    var initialSubfolder = urlParams.get('subfolder');
    setInitialState(initialSubfolder);

    if (initialSubfolder) {
        loadFileList(initialSubfolder);
    } else {
        loadFileList();
    }

    function setInitialState(subfolder) {
        if (subfolder) {
            history.replaceState({ subfolder: subfolder }, "File Manager", "?" + $.param({ subfolder: subfolder }));
        } else {
            history.replaceState({ subfolder: '' }, "File Manager", "?" + $.param({ subfolder: '' }));
        }
    }

    function loadFileList(subfolder = '') {
        subfolder = subfolder.replace(/\/+/g, '/');
        if (subfolder !== '' && subfolder.slice(-1) === '/') {
            subfolder = subfolder.slice(0, -1);
        }

        console.log('Subfolder:', subfolder);

        var stateObject = { subfolder: subfolder };
        var title = "File Manager";
        var newUrl = "?" + $.param(stateObject);
        history.pushState(stateObject, title, newUrl);

        $.ajax({
            url: 'get_file_list.php',
            type: 'GET',
            data: { subfolder: subfolder },
            beforeSend: function () {
                $('#loadingSpinner').show();
            },
            success: function (response) {
                $('#fileListContainer').html(response);

                if ($.fn.DataTable.isDataTable('#fileListTable')) {
                    $('#fileListTable').DataTable().destroy();
                }

                var fileListTable = $('#fileListTable').DataTable({
                    responsive: true,
                    searching: true,
                    lengthMenu: [10, 15, 20],
                    ordering: true,
                    columnDefs: [
                        { orderable: false, "target": [5] }
                    ],
                    dom: 'frtip',
                    buttons: []
                });
                    // Use your custom search box
                $('#search-addon').on('keyup', function () {
                    fileListTable.search($(this).val()).draw();
                });



                $(document).on('click', '.folder', function () {
                    var clickedFolder = $(this).data('folder');
                    loadFileList(clickedFolder);
                });

                updateBreadcrumb(subfolder);
            },
            error: function (error) {
                console.log('Error fetching file list:', error);
            },
            complete: function () {
                $('#loadingSpinner').hide();
            }
        });
    }

 function deleteFile(fileName) {
        $.ajax({
            url: 'delete_file.php',
            type: 'POST',
            data: { file_name: fileName },
            success: function (response) {
                location.reload();
            },
            error: function (error) {
                console.log('Error deleting file:', error);
            }
        });
    } 


    function editFile(oldFileName, newFileName) {
        $.ajax({
            url: 'edit_file.php',
            type: 'POST',
            data: { old_file_name: oldFileName, new_file_name: newFileName },
            success: function (response) {
                window.location.reload();
            },
            error: function (error) {
                console.log('Error editing file:', error);
            }
        });
    }


    // Show Create New Item Modal
    $('#createNewItem').on('show.bs.modal', function (e) {
        // Reset the form when the modal is shown
        $('#createNewItemForm')[0].reset();

        // Get the current path and display it in the modal
        var currentPath = getCurrentPath();
        $('#currentPath').text(currentPath);

        // Set the current path as a data attribute in the form
        $('#createNewItemForm').data('currentPath', currentPath);
    });
    
    // Create New Item Modal
    $('#createNewItemForm').submit(function (e) {
        e.preventDefault();

        var itemName = $('#itemName').val();
        var itemType = $('#itemType').val();
        var currentPath = $(this).data('currentPath');

        // AJAX request to create a new item on the server
        $.ajax({
            type: 'POST',
            url: 'create_item.php', // Update with your backend file
            data: { itemName: itemName, itemType: itemType, currentPath: currentPath },
            success: function (response) {
                // Handle success, e.g., update file list or display a success message
                console.log(response);

                // Close the modal
                $('#createNewItem').modal('hide');

                // You may need to refresh the file list or perform other actions here
                window.location.reload();

            },
            error: function (error) {
                // Handle error, e.g., display an error message
                console.error('Error creating item:', error.responseText);
            }
        });
    });
    
        // Function to get the current folder path dynamically from the URL
    function getCurrentPath() {
        // Example: Assuming the path is in the format "/path/to/current/folder/subfolder?subfolder=%2Fuploads%2Fmamnun"
        var currentPath = window.location.pathname;

        // Remove the last segment (file name) from the path
        currentPath = currentPath.replace(/\/[^\/]+$/, '');

        // Extract the subfolder from the query parameter
        var subfolderParam = new URLSearchParams(window.location.search).get('subfolder');
        if (subfolderParam) {
            currentPath += subfolderParam;
        }

        // Optionally, you can remove leading and trailing slashes
        currentPath = currentPath.replace(/^\/|\/$/g, '');

        return currentPath;
    }

       
    function updateBreadcrumb(subfolder) {
        var breadcrumbFolder = $('#breadcrumb-folder');
        var folders = subfolder ? subfolder.split('/').filter(Boolean) : [];

        breadcrumbFolder.empty();

        if (folders.length > 0) {
            breadcrumbFolder.append('<li class="breadcrumb-item"><a href="#" class="folder-breadcrumb" data-folder=""><i class="fa fa-home" aria-hidden="true"></i> root</a></li>');

            var folderPath = '';
            folders.forEach(function (folder, index) {
                folderPath += folder + '/';
                var isActive = index === folders.length - 1;
                var breadcrumbItem = isActive ? '<li class="breadcrumb-item active">' : '<li class="breadcrumb-item">';
                var breadcrumbLink = isActive ? folder : '<a href="#" class="folder-breadcrumb" data-folder="' + folderPath + '">' + folder + '</a>';
                breadcrumbFolder.append(breadcrumbItem + breadcrumbLink + '</li>');
            });
        } else {
            breadcrumbFolder.append('<li class="breadcrumb-item"><a href="index2.html" class="folder-breadcrumb" data-folder=""><i class="fa fa-home" aria-hidden="true"></i></a> root</li>');
        }

        $('.folder-breadcrumb').on('click', function () {
            var clickedFolder = $(this).data('folder');
            loadFileList(clickedFolder);
        });
    }


    $(document).on('click', '.delete-btn', function () {
        var fileName = $(this).data('file');
        console.log('Delete button clicked for file:', fileName);
        if (confirm('Are you sure you want to delete ' + fileName + '?')) {
            deleteFile(fileName);
        }
    });
    

    $(document).on('click', '.edit-btn', function () {
        var fileName = $(this).data('file');
        console.log('Edit button clicked for file:', fileName);
        var newFileName = prompt('Enter a new name for the file:', fileName);
        if (newFileName) {
            editFile(fileName, newFileName);
        }
    });

    $(document).on('click', '.download-btn', function() {
        var filePath = $(this).data('file');
        
        // Redirect to the download link
        window.location.href = 'download.php?download=' + encodeURIComponent(filePath);
    });
    
       
    
// Add an event listener for modal hide event
$('#uploadModal').on('hide.bs.modal', function () {
    // Reload the page to refresh the file manager
    location.reload();
});


    var myDropzone = new Dropzone('#dropzoneForm', {
        paramName: 'file',
        maxFilesize: 5,
        acceptedFiles: 'image/*,application/pdf',
        dictDefaultMessage: 'Drop files here or click to upload',
        autoProcessQueue: false,
        init: function () {
            var submitButton = document.querySelector('#submitUpload');
            var clearButton = document.querySelector('#clearUpload');
            var uploadFilesTab = $('#uploadFilesTab');
            var uploadFromUrlTab = $('#uploadFromUrlTab');
            var urlInput = $('#urlInput');
    
            this.on('addedfile', function () {
                submitButton.disabled = false;
            });
    
            this.on('sending', function (file, xhr, formData) {
                var currentPath = getCurrentPath();
                formData.append('currentPath', currentPath);
            });
    
            submitButton.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
    
                if (uploadFilesTab.hasClass('active')) {
                    myDropzone.processQueue();
                } else if (uploadFromUrlTab.hasClass('active')) {
                    var url = urlInput.val().trim();
                    console.log('URL submitted:', url);
    
                    if (url !== '') {
                        $.ajax({
                            url: 'upload_from_url.php',
                            method: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify({ urlInput: url, currentPath: getCurrentPath() }),
                            success: function (response) {
                                console.log('Response from server:', response);
                                if (response.status === 'success') {
                                    alert('Success: ' + response.message);
                                    
                                    // Optionally, you can perform additional actions after a successful upload
                                } else {
                                    alert('Error: ' + response.message);
                                    // Optionally, you can handle errors here
                                }
                            },
                            error: function (error) {
                                console.error('Error handling URL:', error);
                                alert('Error: An unexpected error occurred.');
                                // Optionally, you can handle errors here
                            }
                        });
                    }
                }
            });
    
            clearButton.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
    
                if (uploadFilesTab.hasClass('active')) {
                    myDropzone.removeAllFiles();
                    submitButton.disabled = true;
                } else if (uploadFromUrlTab.hasClass('active')) {
                    urlInput.val('');
                }
            });
    
            uploadFilesTab.on('click', function () {
                myDropzone.removeAllFiles();
                submitButton.disabled = true;
                myDropzone.options.url = 'upload_file.php';
            });
    
            uploadFromUrlTab.on('click', function () {
                myDropzone.removeAllFiles();
                submitButton.disabled = false;
                urlInput.val('');
            });
        }
    });
    

});


