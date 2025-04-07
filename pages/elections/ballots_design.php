<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ballot Designer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
        }

        .card {
            border-radius: 0.5rem;
        }

        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
        }

        .card-body {
            padding: 2rem;
        }

        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
        }

        .btn-outline-secondary:hover {
            background-color: #e9ecef;
            color: #212529;
        }
.ballot-canvas {
    background-color: #f8f9fa;
    min-height: 800px;
    padding: 2rem;
}

.ballot-paper {
    background-color: white;
    padding: 3rem;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    border-radius: 8px;
    position: relative;
}

.design-element {
    transition: all 0.2s ease;
}

.design-element:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.position-section {
    border-left: 4px solid #4e73df;
    padding: 1.5rem;
    margin-bottom: 2rem;
    background: #f8f9fa;
    border-radius: 6px;
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
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s ease;
}

.candidate-item:hover {
    background: white;
    border-color: #4e73df;
    box-shadow: 0 2px 8px rgba(78, 115, 223, 0.1);
}

.template-card {
    border: 2px solid transparent;
    transition: all 0.2s ease;
    cursor: pointer;
}

.template-card:hover {
    border-color: #4e73df;
}

.draggable-element {
    cursor: move;
    transition: all 0.2s ease;
}

.draggable-element:hover {
    transform: scale(1.02);
}

#ballotCanvas {
    border: 2px dashed #dee2e6;
    min-height: 800px;
}

.element-toolbar {
    position: absolute;
    right: -40px;
    top: 0;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.position-section:hover .element-toolbar {
    opacity: 1;
}

@media (max-width: 768px) {
    .ballot-paper {
        padding: 1.5rem;
    }
    
    .element-toolbar {
        right: 0;
        top: -30px;
    }
}
</style>

    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Ballot Designer: <?= htmlspecialchars($election['name']) ?></h5>
            <div>
                <button class="btn btn-sm btn-outline-secondary me-2" id="previewBallot">
                    <i class="bi bi-eye me-1"></i> Preview
                </button>
                <button class="btn btn-sm btn-primary" id="saveDesign">
                    <i class="bi bi-save me-1"></i> Save Design
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Design Elements</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary design-element" data-type="header">
                                    <i class="bi bi-type-h1 me-1"></i> Header
                                </button>
                                <button class="btn btn-outline-primary design-element" data-type="text">
                                    <i class="bi bi-text-paragraph me-1"></i> Text Block
                                </button>
                                <button class="btn btn-outline-primary design-element" data-type="position">
                                    <i class="bi bi-award me-1"></i> Position
                                </button>
                                <button class="btn btn-outline-primary design-element" data-type="image">
                                    <i class="bi bi-image me-1"></i> Image
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Templates</h6>
                        </div>
                        <div class="card-body">
                            <select class="form-select mb-3" id="templateSelect">
                                <option value="">Select Template</option>
                                <option value="default">Default Ballot</option>
                                <option value="modern">Modern Design</option>
                                <option value="traditional">Traditional</option>
                                <option value="compact">Compact</option>
                            </select>
                            <button class="btn btn-sm btn-outline-secondary w-100" id="applyTemplate">
                                Apply Template
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Ballot Canvas</h6>
                        </div>
                        <div class="card-body">
                            <div id="ballotCanvas" class="ballot-canvas">
                                <!-- Ballot design will be rendered here -->
                                <div class="ballot-paper">
                                    <div class="ballot-header text-center mb-4">
                                        <h3><?= htmlspecialchars($election['name']) ?></h3>
                                        <p class="text-muted">Official Ballot</p>
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
                                    <div class="position-section mb-4">
                                        <h5><?= htmlspecialchars($position['name']) ?></h5>
                                        <p class="text-muted"><?= htmlspecialchars($position['description']) ?></p>
                                        <p><small>Vote for <?= $position['max_votes'] ?> candidate(s)</small></p>
                                        
                                        <div class="candidates-list">
                                            <?php while ($candidate = $candidates->fetch_assoc()): ?>
                                            <div class="candidate-item">
                                                <input type="checkbox" id="candidate_<?= $candidate['candidateID'] ?>" 
                                                       name="position_<?= $position['positionID'] ?>[]" 
                                                       value="<?= $candidate['candidateID'] ?>">
                                                <label for="candidate_<?= $candidate['candidateID'] ?>">
                                                    <?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?>
                                                </label>
                                            </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                    
                                    <div class="ballot-footer text-center mt-4">
                                        <p class="text-muted">Thank you for voting!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('ballotCanvas');
    let currentTemplate = 'default';
    let selectedElement = null;

    // Initialize drag and drop
    interact('.design-element').draggable({
        listeners: {
            start(event) {
                event.target.classList.add('dragging');
            },
            end(event) {
                event.target.classList.remove('dragging');
            }
        }
    });

    interact('#ballotCanvas').dropzone({
        accept: '.design-element',
        ondropactivate(event) {
            event.target.classList.add('drop-active');
        },
        ondragenter(event) {
            event.target.classList.add('drop-hover');
        },
        ondragleave(event) {
            event.target.classList.remove('drop-hover');
        },
        ondrop(event) {
            const type = event.relatedTarget.dataset.type;
            addElementToCanvas(type, event.clientX, event.clientY);
        },
        ondropdeactivate(event) {
            event.target.classList.remove('drop-active', 'drop-hover');
        }
    });

    // Template handling
    document.getElementById('applyTemplate').addEventListener('click', () => {
        const template = document.getElementById('templateSelect').value;
        applyTemplate(template);
    });

    // Element creation
    function addElementToCanvas(type, x, y) {
        const element = createElement(type);
        const rect = canvas.getBoundingClientRect();
        element.style.position = 'absolute';
        element.style.left = `${x - rect.left - 50}px`;
        element.style.top = `${y - rect.top - 50}px`;
        canvas.appendChild(element);
        makeElementDraggable(element);
        addElementControls(element);
    }

    function createElement(type) {
        const element = document.createElement('div');
        element.className = `draggable-element ${type}-element`;
        element.innerHTML = `
            <div class="element-content"></div>
            <div class="element-toolbar">
                <button class="btn btn-sm btn-outline-secondary edit-element">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-element">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        switch(type) {
            case 'header':
                element.querySelector('.element-content').innerHTML = `
                    <h3 contenteditable="true">New Header</h3>
                    <p class="text-muted" contenteditable="true">Subheader text</p>
                `;
                break;
            case 'text':
                element.querySelector('.element-content').innerHTML = `
                    <div contenteditable="true" class="p-2">New text block</div>
                `;
                break;
            case 'position':
                element.querySelector('.element-content').innerHTML = `
                    <div class="position-section">
                        <h4 contenteditable="true">Position Title</h4>
                        <div class="candidates-list"></div>
                    </div>
                `;
                break;
        }
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
                move(event) {
                    const target = event.target;
                    const x = (parseFloat(target.style.left) || 0) + event.dx;
                    const y = (parseFloat(target.style.top) || 0) + event.dy;
                    target.style.left = `${x}px`;
                    target.style.top = `${y}px`;
                }
            }
        });
    }

    function addElementControls(element) {
        element.querySelector('.delete-element').addEventListener('click', () => {
            element.remove();
        });

        element.querySelector('.edit-element').addEventListener('click', () => {
            selectedElement = element;
            showElementSettings(element);
        });
    }

    function applyTemplate(template) {
        canvas.className = `ballot-canvas ${template}-template`;
        // Add template-specific styling
        switch(template) {
            case 'modern':
                canvas.style.fontFamily = 'Arial, sans-serif';
                canvas.querySelectorAll('h3').forEach(h => h.style.color = '#2e59d9');
                break;
            case 'traditional':
                canvas.style.fontFamily = 'Times New Roman, serif';
                break;
        }
        currentTemplate = template;
    }

    // Save functionality
    document.getElementById('saveDesign').addEventListener('click', () => {
        const designData = {
            template: currentTemplate,
            elements: Array.from(canvas.children).map(element => ({
                type: element.className.replace('draggable-element ', ''),
                content: element.querySelector('.element-content').innerHTML,
                position: {
                    x: element.style.left,
                    y: element.style.top
                }
            }))
        };

        fetch('save_ballot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                electionId: <?= $election_id ?>,
                design: designData
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showToast('Design saved successfully!', 'success');
            }
        });
    });

    // Preview functionality
    document.getElementById('previewBallot').addEventListener('click', () => {
        const previewWindow = window.open('', 'Preview');
        previewWindow.document.write(`
            <html>
            <head>
                <title>Ballot Preview</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>${document.querySelector('style').innerHTML}</style>
            </head>
            <body>
                ${canvas.parentElement.innerHTML}
            </body>
            </html>
        `);
    });

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toast);
        new bootstrap.Toast(toast).show();
    }
});
</script>
</html>


