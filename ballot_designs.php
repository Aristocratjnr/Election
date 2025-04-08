<?php
session_start();
require_once 'config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ballot Designs - Election System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/spectrum-colorpicker2/dist/spectrum.min.css" rel="stylesheet">
    <style>
        .design-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .design-card:hover {
            transform: translateY(-5px);
        }
        .preview-container {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            min-height: 300px;
        }
        .color-picker {
            width: 100%;
            height: 38px;
            padding: 0.375rem 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Ballot Designs</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#designModal">
                <i class="bi bi-plus-lg"></i> New Design
            </button>
        </div>

        <div class="row" id="designsList">
            <!-- Designs will be loaded here -->
        </div>
    </div>

    <!-- Design Modal -->
    <div class="modal fade" id="designModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ballot Design</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="designForm">
                        <input type="hidden" id="designId" name="id">
                        <input type="hidden" name="action" id="formAction" value="create">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Design Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="header_color" class="form-label">Header Color</label>
                                    <input type="text" class="form-control color-picker" id="header_color" name="header_color" value="#4361ee">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="font_family" class="form-label">Font Family</label>
                                    <select class="form-select" id="font_family" name="font_family">
                                        <option value="Poppins">Poppins</option>
                                        <option value="Roboto">Roboto</option>
                                        <option value="Open Sans">Open Sans</option>
                                        <option value="Montserrat">Montserrat</option>
                                        <option value="Lato">Lato</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="logo_position" class="form-label">Logo Position</label>
                                    <select class="form-select" id="logo_position" name="logo_position">
                                        <option value="left">Left</option>
                                        <option value="center">Center</option>
                                        <option value="right">Right</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="show_footer" name="show_footer" checked>
                                        <label class="form-check-label" for="show_footer">
                                            Show Footer
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Preview</label>
                            <div class="preview-container" id="preview">
                                <!-- Preview will be updated dynamically -->
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveDesign">Save Design</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/spectrum-colorpicker2/dist/spectrum.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize color picker
            $('.color-picker').spectrum({
                type: "component",
                showInput: true,
                showInitial: true
            });

            // Load designs
            loadDesigns();

            // Handle form submission
            $('#saveDesign').click(function() {
                const formData = new FormData($('#designForm')[0]);
                const action = $('#formAction').val();
                
                $.ajax({
                    url: 'ballot_design_operations.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#designModal').modal('hide');
                            loadDesigns();
                            showAlert('success', response.message);
                        } else {
                            showAlert('danger', response.message);
                        }
                    },
                    error: function() {
                        showAlert('danger', 'An error occurred while saving the design');
                    }
                });
            });

            // Update preview when design options change
            $('#designForm input, #designForm select').on('change', function() {
                updatePreview();
            });

            // Handle design card click
            $(document).on('click', '.design-card', function() {
                const id = $(this).data('id');
                loadDesign(id);
            });
        });

        function loadDesigns() {
            $.get('ballot_design_operations.php?action=list', function(response) {
                if (response.success) {
                    const designsList = $('#designsList');
                    designsList.empty();

                    response.data.forEach(design => {
                        designsList.append(`
                            <div class="col-md-4 mb-4">
                                <div class="card design-card" data-id="${design.id}">
                                    <div class="card-body">
                                        <h5 class="card-title">${design.name}</h5>
                                        <div class="preview-container mb-3" style="height: 200px;">
                                            <div class="d-flex justify-content-${design.logo_position} mb-3">
                                                <div style="width: 100px; height: 100px; background-color: ${design.header_color};"></div>
                                            </div>
                                            <p class="text-center" style="font-family: ${design.font_family};">Sample Text</p>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <button class="btn btn-sm btn-outline-primary edit-design" data-id="${design.id}">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-design" data-id="${design.id}">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                }
            });
        }

        function loadDesign(id) {
            $.get(`ballot_design_operations.php?action=get&id=${id}`, function(response) {
                if (response.success) {
                    const design = response.data;
                    $('#designId').val(design.id);
                    $('#formAction').val('update');
                    $('#name').val(design.name);
                    $('.color-picker').spectrum('set', design.header_color);
                    $('#logo_position').val(design.logo_position);
                    $('#font_family').val(design.font_family);
                    $('#show_footer').prop('checked', design.show_footer == 1);
                    updatePreview();
                    $('#designModal').modal('show');
                }
            });
        }

        function updatePreview() {
            const name = $('#name').val() || 'Design Name';
            const headerColor = $('.color-picker').spectrum('get').toHexString();
            const logoPosition = $('#logo_position').val();
            const fontFamily = $('#font_family').val();
            const showFooter = $('#show_footer').is(':checked');

            $('#preview').html(`
                <div class="d-flex justify-content-${logoPosition} mb-3">
                    <div style="width: 100px; height: 100px; background-color: ${headerColor};"></div>
                </div>
                <h4 class="text-center" style="font-family: ${fontFamily};">${name}</h4>
                <p class="text-center" style="font-family: ${fontFamily};">Sample ballot content goes here</p>
                ${showFooter ? '<div class="text-center mt-3">Footer Content</div>' : ''}
            `);
        }

        function showAlert(type, message) {
            const alert = $(`
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            $('.container').prepend(alert);
            setTimeout(() => alert.alert('close'), 5000);
        }

        // Handle delete design
        $(document).on('click', '.delete-design', function(e) {
            e.stopPropagation();
            if (confirm('Are you sure you want to delete this design?')) {
                const id = $(this).data('id');
                $.post('ballot_design_operations.php', {
                    action: 'delete',
                    id: id
                }, function(response) {
                    if (response.success) {
                        loadDesigns();
                        showAlert('success', response.message);
                    } else {
                        showAlert('danger', response.message);
                    }
                });
            }
        });

        // Handle edit design
        $(document).on('click', '.edit-design', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            loadDesign(id);
        });

        // Reset form when modal is closed
        $('#designModal').on('hidden.bs.modal', function() {
            $('#designForm')[0].reset();
            $('#designId').val('');
            $('#formAction').val('create');
            $('.color-picker').spectrum('set', '#4361ee');
            updatePreview();
        });
    </script>
</body>
</html> 