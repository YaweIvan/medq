<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        .sortable-ghost {
            opacity: 0.4;
            transform: rotate(2deg);
        }
        
        .sortable-drag {
            transform: rotate(5deg);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
            z-index: 1000;
        }
        
        .subject-card {
            transition: all 0.3s ease;
            cursor: move;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            background: white;
        }
        
        .subject-card:hover {
            border-color: #007bff;
            box-shadow: 0 4px 15px rgba(0,123,255,0.1);
            transform: translateY(-2px);
        }
        
        .drag-handle {
            color: #6c757d;
            font-size: 1.2rem;
            margin-right: 0.5rem;
        }
        
        .subject-card:hover .drag-handle {
            color: #007bff;
        }
        
        .badge-counter {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }
        
        .file-replace-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            display: none;
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    @include('components.topnav')
    @include('components.sidebar')

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center text-white">
            <div class="spinner-border mb-3" style="width: 3rem; height: 3rem;"></div>
            <h5>Processing...</h5>
        </div>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Manage Subjects</h2>
                    <p class="text-muted mb-0">Drag and drop to reorder subjects</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary" onclick="resetOrder()">
                        <i class="fas fa-undo"></i> Reset to Default Order
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Subjects List -->
            <div id="subjects-container">
                @forelse($subjects as $subject)
                    <div class="subject-card" data-subject-id="{{ $subject->id }}">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center flex-grow-1">
                                <i class="fas fa-grip-vertical drag-handle"></i>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1 fw-bold text-dark">{{ $subject->name }}</h5>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="badge bg-primary badge-counter">
                                            {{ $subject->questions_count }} Question{{ $subject->questions_count !== 1 ? 's' : '' }}
                                        </span>
                                        <span class="badge bg-secondary badge-counter">
                                            Max: {{ $subject->max_questions ?? 5 }}
                                        </span>
                                        <span class="badge bg-info badge-counter">
                                            {{ $subject->marks_per_question ?? 1 }} Mark{{ ($subject->marks_per_question ?? 1) !== 1 ? 's' : '' }}
                                        </span>
                                        <small class="text-muted ms-2">
                                            Order: {{ $subject->sort_order ?? 'Auto' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-sm" onclick="toggleFileReplace({{ $subject->id }})">
                                    <i class="fas fa-file-upload"></i> Replace File
                                </button>
                            </div>
                        </div>
                        
                        <!-- File Replacement Section -->
                        <div class="file-replace-section" id="file-section-{{ $subject->id }}">
                            <h6><i class="fas fa-upload"></i> Replace Excel File for "{{ $subject->name }}"</h6>
                            <form id="replace-form-{{ $subject->id }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Select Quiz</label>
                                        <select class="form-select" name="quiz_id" required>
                                            <option value="">Choose quiz...</option>
                                            <!-- Will be populated via AJAX -->
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Excel/CSV File</label>
                                        <input type="file" class="form-control" name="excel_file" accept=".xlsx,.xls,.csv" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Time/Question (sec)</label>
                                        <input type="number" class="form-control" name="time_per_question" value="60" min="10" max="600">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="d-flex gap-2 w-100">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload"></i> Replace
                                            </button>
                                            <button type="button" class="btn btn-secondary" onclick="toggleFileReplace({{ $subject->id }})">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No subjects found. Create a quiz with subjects first.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        let sortable;
        
        document.addEventListener('DOMContentLoaded', function() {
            initSortable();
            loadQuizzes();
        });
        
        function initSortable() {
            const container = document.getElementById('subjects-container');
            if (!container) return;
            
            sortable = new Sortable(container, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    if (evt.oldIndex !== evt.newIndex) {
                        saveOrder();
                    }
                }
            });
        }
        
        async function saveOrder() {
            const subjects = document.querySelectorAll('.subject-card');
            const subjectIds = Array.from(subjects).map(el => parseInt(el.dataset.subjectId));
            
            showLoading(true);
            
            try {
                const response = await fetch('{{ route("admin.subjects.update-order") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        subject_ids: subjectIds
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Subject order updated successfully', 'success');
                    updateOrderLabels();
                } else {
                    throw new Error(data.message || 'Failed to update order');
                }
            } catch (error) {
                showNotification('Failed to update order: ' + error.message, 'danger');
                location.reload(); // Reload to restore original order
            } finally {
                showLoading(false);
            }
        }
        
        function updateOrderLabels() {
            const subjects = document.querySelectorAll('.subject-card');
            subjects.forEach((card, index) => {
                const orderLabel = card.querySelector('small');
                if (orderLabel) {
                    orderLabel.textContent = `Order: ${index + 1}`;
                }
            });
        }
        
        function resetOrder() {
            if (confirm('Reset all subjects to default order (by creation date)?')) {
                location.reload();
            }
        }
        
        function toggleFileReplace(subjectId) {
            const section = document.getElementById(`file-section-${subjectId}`);
            if (section.style.display === 'none' || !section.style.display) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        }
        
        async function loadQuizzes() {
            // Disable file replacement until we have quiz options
            const selects = document.querySelectorAll('select[name="quiz_id"]');
            selects.forEach(select => {
                select.innerHTML = '<option value="1">Default Quiz</option>';
            });
        }
        
        // Handle file replacement form submissions
        document.addEventListener('submit', async function(e) {
            if (e.target.matches('[id^="replace-form-"]')) {
                e.preventDefault();
                
                const form = e.target;
                const subjectId = form.id.split('-').pop();
                const formData = new FormData(form);
                
                showLoading(true);
                
                try {
                    const response = await fetch(`/admin/subjects/${subjectId}/replace-file`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showNotification(data.message, 'success');
                        toggleFileReplace(subjectId);
                        form.reset();
                        // Update question count
                        location.reload();
                    } else {
                        throw new Error(data.message || 'File replacement failed');
                    }
                } catch (error) {
                    showNotification('File replacement failed: ' + error.message, 'danger');
                } finally {
                    showLoading(false);
                }
            }
        });
        
        function showLoading(show) {
            const overlay = document.getElementById('loadingOverlay');
            overlay.style.display = show ? 'flex' : 'none';
        }
        
        function showNotification(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container-fluid');
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>