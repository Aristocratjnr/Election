<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ballot Designer - <?= htmlspecialchars($election['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3f37c9;
            --secondary-color: #f8f9fc;
            --accent-color: #4cc9f0;
            --success-color: #4dd4ac;
            --warning-color: #ffd166;
            --danger-color: #ef476f;
            --gray-dark: #212529;
            --gray-medium: #6c757d;
            --gray-light: #e9ecef;
        }
        
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
            line-height: 1.6;
        }
        
        .designer-container {
            display: flex;
            min-height: calc(100vh - 56px);
        }
        
        .toolbox {
            width: 280px;
            background: white;
            border-right: 1px solid #dee2e6;
            padding: 1rem;
            overflow-y: auto;
        }
        
        .canvas-container {
            flex: 1;
            padding: 1rem;
            overflow: auto;
            background-color: #f0f2f5;
        }
        
        .ballot-canvas {
            background-color: white;
            min-height: 1000px;
            padding: 3rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            margin: 0 auto;
            max-width: 800px;
            position: relative;
        }
        
        .design-element {
            transition: all 0.2s ease;
            cursor: pointer;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            background: white;
        }
        
        .design-element:hover {
            background-color: var(--primary-light);
            color: white;
            border-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }
        
        .draggable-element {
            position: absolute;
            cursor: move;
            transition: all 0.2s ease;
            z-index: 10;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
            border: 1px solid #dee2e6;
            min-width: 200px;
        }
        
        .draggable-element:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            z-index: 20;
        }
        
        .element-toolbar {
            position: absolute;
            top: -30px;
            right: 0;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
            padding: 0.25rem;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .draggable-element:hover .element-toolbar {
            opacity: 1;
        }
        
        .position-section {
            border-left: 4px solid var(--primary-color);
            padding: 1.5rem;
            margin-bottom: 2rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
            position: relative;
        }
        
        .candidates-list {
            display: grid;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .candidate-item {
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s ease;
            background: white;
        }
        
        .candidate-item:hover {
            border-color: var(--primary-color);
            box-shadow: 0 0.125rem 0.25rem rgba(78, 115, 223, 0.1);
        }
        
        .template-card {
            border: 2px solid transparent;
            transition: all 0.2s ease;
            cursor: pointer;
            border-radius: 0.5rem;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        .template-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .template-card.active {
            border-color: var(--primary-color);
            box-shadow: 0 0.25rem 0.5rem rgba(78, 115, 223, 0.2);
        }
        
        .template-preview {
            height: 100px;
            background-size: cover;
            background-position: center;
        }
        
        .toolbox-section {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .toolbox-section h6 {
            font-weight: 600;
            color: var(--gray-dark);
            margin-bottom: 0.75rem;
        }
        
        .ballot-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .ballot-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
        }
        
        .resize-handle {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--primary-color);
            bottom: 0;
            right: 0;
            cursor: nwse-resize;
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .draggable-element:hover .resize-handle {
            opacity: 1;
        }
        
        .element-settings {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: white;
            box-shadow: -0.25rem 0 0.5rem rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            transition: right 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .element-settings.active {
            right: 0;
        }
        
        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .settings-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        
        .settings-overlay.active {
            opacity: 1;
            pointer-events: all;
        }
        
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1100;
        }
        
        @media (max-width: 992px) {
            .designer-container {
                flex-direction: column;
            }
            
            .toolbox {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #dee2e6;
            }
            
            .element-toolbar {
                top: -40px;
                right: -10px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-file-earmark-text me-2"></i>Ballot Designer
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><?= htmlspecialchars($election['name']) ?></span>
                <button class="btn btn-sm btn-light me-2" id="previewBallot">
                    <i class="bi bi-eye me-1"></i> Preview
                </button>
                <button class="btn btn-sm btn-light" id="saveDesign">
                    <i class="bi bi-save me-1"></i> Save
                </button>
            </div>
        </div>
    </nav>
    
    <div class="designer-container">
        <div class="toolbox">
            <div class="toolbox-section">
                <h6><i class="bi bi-puzzle me-2"></i>Design Elements</h6>
                <div class="d-grid gap-2">
                    <div class="design-element" data-type="header">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-type-h1 me-2"></i>
                            <span>Header</span>
                        </div>
                    </div>
                    <div class="design-element" data-type="text">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-text-paragraph me-2"></i>
                            <span>Text Block</span>
                        </div>
                    </div>
                    <div class="design-element" data-type="position">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-award me-2"></i>
                            <span>Position</span>
                        </div>
                    </div>
                    <div class="design-element" data-type="image">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-image me-2"></i>
                            <span>Image</span>
                        </div>
                    </div>
                    <div class="design-element" data-type="divider">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-dash-lg me-2"></i>
                            <span>Divider</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="toolbox-section">
                <h6><i class="bi bi-collection me-2"></i>Templates</h6>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="template-card" data-template="default">
                            <div class="template-preview" style="background-color: #f8f9fa;"></div>
                            <div class="p-2 text-center">
                                <small>Default</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="template-card" data-template="modern">
                            <div class="template-preview" style="background-color: #4361ee;"></div>
                            <div class="p-2 text-center">
                                <small>Modern</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="template-card" data-template="traditional">
                            <div class="template-preview" style="background-color: #f8edeb;"></div>
                            <div class="p-2 text-center">
                                <small>Traditional</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="template-card" data-template="compact">
                            <div class="template-preview" style="background-color: #ffffff; border: 1px solid #dee2e6;"></div>
                            <div class="p-2 text-center">
                                <small>Compact</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="toolbox-section">
                <h6><i class="bi bi-sliders me-2"></i>Canvas Options</h6>
                <div class="form-group mb-3">
                    <label class="form-label">Background Color</label>
                    <input type="color" class="form-control form-control-color" id="canvasBgColor" value="#ffffff">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Paper Size</label>
                    <select class="form-select" id="paperSize">
                        <option value="letter">Letter (8.5" x 11")</option>
                        <option value="legal">Legal (8.5" x 14")</option>
                        <option value="a4">A4 (210mm x 297mm)</option>
                    </select>
                </div>
                <button class="btn btn-sm btn-outline-primary w-100" id="resetCanvas">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Canvas
                </button>
            </div>
        </div>
        
        <div class="canvas-container">
            <div id="ballotCanvas" class="ballot-canvas">
                <!-- Default ballot content -->
                <div class="ballot-header">
                    <h2 contenteditable="true"><?= htmlspecialchars($election['name']) ?></h2>
                    <p class="text-muted" contenteditable="true">Official Ballot</p>
                </div>
                
                <?php
                // Get positions and candidates
                $positions = $conn->query("
                    SELECT p.* FROM positions p 
                    WHERE p.electionID = $election_id
                    ORDER BY p.order_num
                ");
                
                while ($position = $positions->fetch_assoc()):
                    $candidates = $conn->query("
                        SELECT * FROM candidates 
                        WHERE positionID = {$position['positionID']}
                        ORDER BY last_name, first_name
                    ");
                ?>
                <div class="position-section">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 contenteditable="true"><?= htmlspecialchars($position['name']) ?></h4>
                        <small class="text-muted">Vote for <?= $position['max_votes'] ?> candidate(s)</small>
                    </div>
                    <p class="text-muted mb-3" contenteditable="true"><?= htmlspecialchars($position['description']) ?></p>
                    
                    <div class="candidates-list">
                        <?php while ($candidate = $candidates->fetch_assoc()): ?>
                        <div class="candidate-item">
                            <input type="checkbox" id="candidate_<?= $candidate['candidateID'] ?>" 
                                   name="position_<?= $position['positionID'] ?>[]" 
                                   value="<?= $candidate['candidateID'] ?>">
                            <label for="candidate_<?= $candidate['candidateID'] ?>" contenteditable="true">
                                <?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?>
                            </label>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <div class="ballot-footer">
                    <p class="text-muted" contenteditable="true">Thank you for voting!</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Element Settings Panel -->
    <div class="settings-overlay" id="settingsOverlay"></div>
    <div class="element-settings" id="elementSettings">
        <div class="settings-header">
            <h5 class="mb-0">Element Settings</h5>
            <button class="btn-close" id="closeSettings"></button>
        </div>
        <div id="settingsContent">
            <!-- Settings content will be loaded here -->
        </div>
    </div>
    
    <!-- Toast Notifications -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('ballotCanvas');
        let currentTemplate = 'default';
        let selectedElement = null;
        let elements = [];
        
        // Initialize interact.js for drag and drop
        interact('.design-element').draggable({
            inertia: true,
            modifiers: [
                interact.modifiers.restrictRect({
                    restriction: 'parent',
                    endOnly: true
                })
            ],
            listeners: {
                start(event) {
                    event.target.classList.add('dragging');
                },
                move(event) {
                    const target = event.target;
                    const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                    const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
                    
                    target.style.transform = `translate(${x}px, ${y}px)`;
                    target.setAttribute('data-x', x);
                    target.setAttribute('data-y', y);
                },
                end(event) {
                    event.target.classList.remove('dragging');
                    const type = event.target.getAttribute('data-type');
                    addElementToCanvas(type, event.clientX, event.clientY);
                }
            }
        });
        
        // Make canvas a dropzone
        interact(canvas).dropzone({
            accept: '.design-element',
            overlap: 0.75,
            ondropactivate: function(event) {
                event.target.classList.add('drop-active');
            },
            ondragenter: function(event) {
                event.relatedTarget.classList.add('can-drop');
            },
            ondragleave: function(event) {
                event.relatedTarget.classList.remove('can-drop');
            },
            ondrop: function(event) {
                event.relatedTarget.classList.remove('can-drop');
            },
            ondropdeactivate: function(event) {
                event.target.classList.remove('drop-active');
            }
        });
        
        // Template selection
        document.querySelectorAll('.template-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.template-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                currentTemplate = this.getAttribute('data-template');
                applyTemplate(currentTemplate);
            });
        });
        
        // Apply default template
        document.querySelector('.template-card[data-template="default"]').classList.add('active');
        
        // Element creation
        function addElementToCanvas(type, x, y) {
            const element = createElement(type);
            const rect = canvas.getBoundingClientRect();
            
            element.style.position = 'absolute';
            element.style.left = `${x - rect.left - 100}px`;
            element.style.top = `${y - rect.top - 50}px`;
            
            canvas.appendChild(element);
            makeElementDraggable(element);
            addElementControls(element);
            
            // Add to elements array
            elements.push({
                id: Date.now().toString(),
                type: type,
                element: element,
                content: element.querySelector('.element-content').innerHTML,
                position: {
                    x: element.style.left,
                    y: element.style.top
                }
            });
        }
        
        function createElement(type) {
            const element = document.createElement('div');
            element.className = `draggable-element ${type}-element animate__animated animate__fadeIn`;
            element.setAttribute('data-type', type);
            
            let content = '';
            switch(type) {
                case 'header':
                    content = `
                        <div class="element-content p-3">
                            <h3 contenteditable="true">New Header</h3>
                            <p class="text-muted" contenteditable="true">Subheader text</p>
                        </div>
                    `;
                    break;
                case 'text':
                    content = `
                        <div class="element-content p-3">
                            <div contenteditable="true">New text block. Click to edit.</div>
                        </div>
                    `;
                    break;
                case 'position':
                    content = `
                        <div class="element-content">
                            <div class="position-section">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h4 contenteditable="true">Position Title</h4>
                                    <small class="text-muted">Vote for 1 candidate(s)</small>
                                </div>
                                <p class="text-muted mb-3" contenteditable="true">Position description</p>
                                <div class="candidates-list">
                                    <div class="candidate-item">
                                        <input type="checkbox" id="candidate_new_1" name="position_new[]">
                                        <label for="candidate_new_1" contenteditable="true">Candidate Name</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    break;
                case 'image':
                    content = `
                        <div class="element-content p-2">
                            <img src="https://via.placeholder.com/300x150?text=Click+to+upload" 
                                 style="max-width: 100%; height: auto; cursor: pointer;" 
                                 class="img-upload">
                            <input type="file" class="d-none" accept="image/*">
                        </div>
                    `;
                    break;
                case 'divider':
                    content = `
                        <div class="element-content">
                            <hr style="border-top: 2px dashed #dee2e6; margin: 1rem 0;">
                        </div>
                    `;
                    break;
            }
            
            element.innerHTML = `
                ${content}
                <div class="element-toolbar">
                    <button class="btn btn-sm btn-outline-secondary edit-element me-1">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-element">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="resize-handle"></div>
            `;
            
            return element;
        }
        
        function makeElementDraggable(element) {
            interact(element).draggable({
                inertia: true,
                modifiers: [
                    interact.modifiers.restrictRect({
                        restriction: 'parent',
                        endOnly: true
                    })
                ],
                listeners: {
                    start(event) {
                        event.target.classList.add('dragging');
                        selectedElement = event.target;
                        showElementSettings(selectedElement);
                    },
                    move(event) {
                        const target = event.target;
                        const x = (parseFloat(target.style.left) || 0) + event.dx;
                        const y = (parseFloat(target.style.top) || 0) + event.dy;
                        
                        target.style.left = `${x}px`;
                        target.style.top = `${y}px`;
                        
                        // Update position in elements array
                        const elementId = target.getAttribute('data-id');
                        if (elementId) {
                            const elementIndex = elements.findIndex(el => el.id === elementId);
                            if (elementIndex !== -1) {
                                elements[elementIndex].position = { x: `${x}px`, y: `${y}px` };
                            }
                        }
                    },
                    end(event) {
                        event.target.classList.remove('dragging');
                    }
                }
            });
            
            // Add resizing
            interact(element).resizable({
                edges: { right: true, bottom: true },
                listeners: {
                    move(event) {
                        const target = event.target;
                        target.style.width = `${event.rect.width}px`;
                        target.style.height = `${event.rect.height}px`;
                    }
                }
            });
        }
        
        function addElementControls(element) {
            // Delete button
            element.querySelector('.delete-element').addEventListener('click', (e) => {
                e.stopPropagation();
                element.classList.add('animate__fadeOut');
                setTimeout(() => {
                    element.remove();
                    // Remove from elements array
                    const elementId = element.getAttribute('data-id');
                    if (elementId) {
                        elements = elements.filter(el => el.id !== elementId);
                    }
                }, 300);
            });
            
            // Edit button
            element.querySelector('.edit-element').addEventListener('click', (e) => {
                e.stopPropagation();
                selectedElement = element;
                showElementSettings(element);
            });
            
            // Click on element
            element.addEventListener('click', (e) => {
                if (e.target === element || e.target.closest('.element-content')) {
                    selectedElement = element;
                    showElementSettings(element);
                }
            });
            
            // Image upload handling
            const imgUpload = element.querySelector('.img-upload');
            if (imgUpload) {
                const fileInput = element.querySelector('input[type="file"]');
                imgUpload.addEventListener('click', () => fileInput.click());
                fileInput.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (event) => {
                            imgUpload.src = event.target.result;
                            // Update in elements array
                            const elementId = element.getAttribute('data-id');
                            if (elementId) {
                                const elementIndex = elements.findIndex(el => el.id === elementId);
                                if (elementIndex !== -1) {
                                    elements[elementIndex].content = element.querySelector('.element-content').innerHTML;
                                }
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Assign unique ID if not exists
            if (!element.getAttribute('data-id')) {
                const id = Date.now().toString();
                element.setAttribute('data-id', id);
            }
        }
        
        function showElementSettings(element) {
            const type = element.getAttribute('data-type');
            const settingsPanel = document.getElementById('elementSettings');
            const overlay = document.getElementById('settingsOverlay');
            const settingsContent = document.getElementById('settingsContent');
            
            let settingsHTML = '';
            
            switch(type) {
                case 'header':
                    settingsHTML = `
                        <div class="mb-3">
                            <label class="form-label">Header Text</label>
                            <input type="text" class="form-control" value="${element.querySelector('h3').textContent}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subheader Text</label>
                            <input type="text" class="form-control" value="${element.querySelector('p').textContent}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Text Color</label>
                            <input type="color" class="form-control form-control-color" value="#000000">
                        </div>
                    `;
                    break;
                case 'text':
                    settingsHTML = `
                        <div class="mb-3">
                            <label class="form-label">Text Content</label>
                            <textarea class="form-control" rows="5">${element.querySelector('.element-content > div').textContent}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Text Alignment</label>
                            <select class="form-select">
                                <option value="left">Left</option>
                                <option value="center">Center</option>
                                <option value="right">Right</option>
                                <option value="justify">Justify</option>
                            </select>
                        </div>
                    `;
                    break;
                case 'position':
                    settingsHTML = `
                        <div class="mb-3">
                            <label class="form-label">Position Title</label>
                            <input type="text" class="form-control" value="${element.querySelector('h4').textContent}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Votes</label>
                            <input type="number" class="form-control" value="1" min="1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" rows="3">${element.querySelector('p.text-muted').textContent}</textarea>
                        </div>
                    `;
                    break;
                case 'image':
                    settingsHTML = `
                        <div class="mb-3">
                            <label class="form-label">Image Source</label>
                            <input type="file" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alternative Text</label>
                            <input type="text" class="form-control" placeholder="Description for accessibility">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image Size</label>
                            <select class="form-select">
                                <option value="auto">Auto</option>
                                <option value="100">100% Width</option>
                                <option value="75">75% Width</option>
                                <option value="50">50% Width</option>
                                <option value="25">25% Width</option>
                            </select>
                        </div>
                    `;
                    break;
                default:
                    settingsHTML = `<p>No settings available for this element type.</p>`;
            }
            
            settingsContent.innerHTML = settingsHTML;
            settingsPanel.classList.add('active');
            overlay.classList.add('active');
            
            // Close settings when clicking overlay
            overlay.addEventListener('click', () => {
                settingsPanel.classList.remove('active');
                overlay.classList.remove('active');
            });
            
            // Close button
            document.getElementById('closeSettings').addEventListener('click', () => {
                settingsPanel.classList.remove('active');
                overlay.classList.remove('active');
            });
        }
        
        function applyTemplate(template) {
            canvas.className = `ballot-canvas ${template}-template`;
            
            // Add template-specific styling
            switch(template) {
                case 'modern':
                    canvas.style.fontFamily = 'Poppins, sans-serif';
                    canvas.querySelectorAll('h1, h2, h3, h4').forEach(h => {
                        h.style.color = 'var(--primary-color)';
                    });
                    break;
                case 'traditional':
                    canvas.style.fontFamily = 'Georgia, serif';
                    canvas.style.backgroundColor = '#f9f5f2';
                    break;
                case 'compact':
                    canvas.style.padding = '1.5rem';
                    canvas.querySelectorAll('.position-section').forEach(section => {
                        section.style.padding = '1rem';
                        section.style.marginBottom = '1rem';
                    });
                    break;
                default:
                    // Reset to default
                    canvas.style.fontFamily = '';
                    canvas.style.backgroundColor = '';
                    canvas.style.padding = '3rem';
                    canvas.querySelectorAll('h1, h2, h3, h4').forEach(h => {
                        h.style.color = '';
                    });
                    canvas.querySelectorAll('.position-section').forEach(section => {
                        section.style.padding = '1.5rem';
                        section.style.marginBottom = '2rem';
                    });
            }
            
            currentTemplate = template;
        }
        
        // Canvas options
        document.getElementById('canvasBgColor').addEventListener('change', function() {
            canvas.style.backgroundColor = this.value;
        });
        
        document.getElementById('paperSize').addEventListener('change', function() {
            switch(this.value) {
                case 'legal':
                    canvas.style.minHeight = '1200px';
                    break;
                case 'a4':
                    canvas.style.minHeight = '1100px';
                    break;
                default:
                    canvas.style.minHeight = '1000px';
            }
        });
        
        document.getElementById('resetCanvas').addEventListener('click', function() {
            if (confirm('Are you sure you want to reset the canvas? This will remove all custom elements.')) {
                // Remove all draggable elements
                document.querySelectorAll('.draggable-element').forEach(el => el.remove());
                // Reset elements array
                elements = [];
                // Reset template
                applyTemplate('default');
                document.querySelector('.template-card[data-template="default"]').click();
                // Reset background
                document.getElementById('canvasBgColor').value = '#ffffff';
                canvas.style.backgroundColor = '#ffffff';
                // Reset paper size
                document.getElementById('paperSize').value = 'letter';
                canvas.style.minHeight = '1000px';
                
                showToast('Canvas has been reset', 'success');
            }
        });
        
        // Save functionality
        document.getElementById('saveDesign').addEventListener('click', function() {
            const designData = {
                template: currentTemplate,
                backgroundColor: canvas.style.backgroundColor,
                paperSize: document.getElementById('paperSize').value,
                elements: elements.map(element => ({
                    id: element.id,
                    type: element.type,
                    content: element.element.querySelector('.element-content').innerHTML,
                    position: element.position,
                    styles: window.getComputedStyle(element.element)
                }))
            };
            
            // In a real application, you would send this to your server
            console.log('Saving design:', designData);
            
            // Simulate API call
            setTimeout(() => {
                showToast('Design saved successfully!', 'success');
            }, 1000);
        });
        
        // Preview functionality
        document.getElementById('previewBallot').addEventListener('click', function() {
            const previewWindow = window.open('', 'Preview');
            previewWindow.document.write(`
                <html>
                <head>
                    <title>Ballot Preview - <?= htmlspecialchars($election['name']) ?></title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body {
                            font-family: 'Poppins', sans-serif;
                            background-color: #f8f9fa;
                            padding: 2rem;
                        }
                        .ballot-paper {
                            background-color: white;
                            padding: 3rem;
                            max-width: 800px;
                            margin: 0 auto;
                            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
                        }
                    </style>
                </head>
                <body>
                    <div class="ballot-paper">
                        ${canvas.innerHTML}
                    </div>
                </body>
                </html>
            `);
            previewWindow.document.close();
        });
        
        // Toast notification function
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast show align-items-center text-white bg-${type} border-0`;
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            toastContainer.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }
        
        // Initialize with some default elements
        setTimeout(() => {
            // Add a header if none exists
            if (!document.querySelector('.header-element')) {
                addElementToCanvas('header', 300, 100);
            }
        }, 500);
    });
    </script>
</body>
</html>