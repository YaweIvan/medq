<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Quiz - MedQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .user-card {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .user-card:hover {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }
        
        .user-card.assigned {
            background-color: #dcfce7;
            border-color: #10b981;
        }
        
        .user-card .form-check {
            margin: 0;
            width: 100%;
        }
        
        .user-card .form-check-input {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .user-card .form-check-label {
            cursor: pointer;
        }
        
        .user-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .search-box {
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
            padding-bottom: 1rem;
        }
    </style>
</head>
<body>
    @include('components.topnav')
    @include('components.sidebar')

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="fw-bold text-dark mb-4">Create a New Quiz</h2>
            
            <form id="quizForm" enctype="multipart/form-data">
                @csrf
                
                <!-- Section 1: Quiz Details -->
                <div class="mb-5">
                    <h5 class="mb-3">Quiz Details</h5>
                    <div class="mb-3">
                        <label class="form-label">Quiz Title <span class="text-danger">*</span></label>
                        <input type="text" id="quizTitle" class="form-control" placeholder='e.g., "Week 1 Cardiology Quiz"' required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <!-- Section 2: Select Participants -->
                <div class="mb-5">
                    <h5 class="mb-3">Select Participants</h5>
                    <button type="button" class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#manageUsersModal">
                        <i class="fas fa-users me-2"></i>Manage Users
                    </button>
                    
                    <div class="row" id="assignedUsersList">
                        <!-- Selected users will appear here -->
                    </div>
                    
                    <p class="text-muted" id="noUsersMessage">No users selected yet. Click "Manage Users" to add participants.</p>
                </div>

                <!-- Section 3: Quiz Subjects & Questions -->
                <div class="mb-5">
                    <h5 class="mb-3">Quiz Subjects & Questions</h5>
                    <div id="subjectsContainer">
                        <div class="subject-block" data-index="0">
                            <div class="row">
                                <div class="col-md-2">
                                    <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control subject-name" placeholder="e.g., Anatomy" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Time per Question (sec) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control time-per-question" placeholder="60" value="60" min="10" max="600" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Max Questions <span class="text-muted">(default: 5)</span></label>
                                    <input type="number" class="form-control max-questions" placeholder="5" min="1" value="5">
                                    <small class="text-muted">Leave empty for all</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Questions File <span class="text-danger">*</span></label>
                                    <div class="file-upload-area" onclick="triggerFileInput(this)">
                                        <input type="file" class="file-input" accept=".xlsx,.xls,.csv" style="display: none;" required>
                                        <div class="upload-content">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                            <p class="mb-0">Drag & drop .xlsx or .csv file or click to browse</p>
                                        </div>
                                        <div class="file-selected" style="display: none;">
                                            <i class="fas fa-file-excel text-success"></i>
                                            <span class="file-name"></span>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeFile(this)">Remove</button>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary mt-3" onclick="addSubject()">
                        <i class="fas fa-plus"></i> Add Another Subject
                    </button>
                    
                    <div class="alert alert-info mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="alert-heading mb-0"><i class="fas fa-info-circle"></i> Excel/CSV File Format Requirements</h6>
                            <a href="{{ route('admin.download.template') }}" class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i> Download Sample Template
                            </a>
                        </div>
                        <p class="mb-2 fw-bold">Your file MUST have 7 or 8 columns in this order:</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered bg-white mb-2">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Column A</th><th>Column B</th><th>Column C</th><th>Column D</th><th>Column E</th><th>Column F</th><th>Column G</th><th>Column H</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="fw-bold bg-light">
                                        <td>Number</td><td>Question</td><td>Option A</td><td>Option B</td><td>Option C</td><td>Option D</td><td>Option E</td><td>Answer</td>
                                    </tr>
                                    <tr>
                                        <td>1</td><td>What is the normal heart rate?</td><td>60-100 bpm</td><td>40-60 bpm</td><td>100-120 bpm</td><td>120-140 bpm</td><td><em>(leave empty)</em></td><td>A</td>
                                    </tr>
                                    <tr>
                                        <td>2</td><td>Which organ produces insulin?</td><td>Liver</td><td>Pancreas</td><td>Kidney</td><td>Spleen</td><td>Heart</td><td>B</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <strong class="text-success">✓ REQUIRED:</strong>
                                <ul class="small mb-0">
                                    <li>7 or 8 columns (A to G or A to H)</li>
                                    <li>First row = headers</li>
                                    <li>Answer column: A, B, C, D, or E</li>
                                    <li>Option E is optional (can be empty)</li>
                                    <li>File format: .xlsx or .csv</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <strong class="text-danger">✗ AVOID:</strong>
                                <ul class="small mb-0">
                                    <li>More than 8 columns</li>
                                    <li>Empty rows between questions</li>
                                    <li>Numbers/symbols in Answer column</li>
                                    <li>Merged cells or formatting</li>
                                    <li>Answer E without Option E text</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="sticky-bottom bg-white p-3 border-top">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" data-action="activate">
                            <i class="fas fa-check"></i> Save and Activate Quiz
                        </button>
                        <button type="submit" class="btn btn-secondary" data-action="draft">
                            <i class="fas fa-save"></i> Save as Draft
                        </button>
                        <a href="{{ route('admin.quizzes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Manage Users Modal -->
    <div class="modal fade" id="manageUsersModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-users me-2"></i>Select Quiz Participants
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="search-box">
                        <input type="text" id="userSearch" class="form-control" placeholder="Search users by name or email...">
                    </div>
                    
                    <div class="user-list" id="userList">
                        <p class="text-center text-muted">Loading users...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <style>
    .participant-selector {
        position: relative;
    }
    .participant-selector .dropdown-menu {
        z-index: 9999;
    }
    .selected-users {
        min-height: 40px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    .user-tag {
        background: var(--primary);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .user-tag .remove {
        cursor: pointer;
        font-weight: bold;
    }
    .subject-block {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
        position: relative;
    }
    .subject-block .remove-subject {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
    }
    .file-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 0.375rem;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .file-upload-area:hover {
        border-color: var(--primary);
        background-color: #f8f9fa;
    }
    .file-upload-area.dragover {
        border-color: var(--primary);
        background-color: #e3f2fd;
    }
    </style>

    <script>
    let allUsers = [];
    let selectedUserIds = [];
    let subjectIndex = 1;

    // Load users when modal opens
    document.getElementById('manageUsersModal').addEventListener('show.bs.modal', async function() {
        await loadUsers();
    });

    // Load users on page load
    document.addEventListener('DOMContentLoaded', function() {
        setupEventListeners();
    });

    async function loadUsers() {
        try {
            const response = await fetch('{{ route("admin.users.api") }}');
            const data = await response.json();
            allUsers = data.users || [];
            
            if (allUsers.length === 0) {
                document.getElementById('userList').innerHTML = '<p class="text-muted text-center">No approved quizzer users found in the system</p>';
            } else {
                renderUserList();
            }
        } catch (error) {
            console.error('Error loading users:', error);
            document.getElementById('userList').innerHTML = '<p class="text-danger">Error loading users. Please try again.</p>';
        }
    }

    function renderUserList(searchTerm = '') {
        const userList = document.getElementById('userList');
        
        let filteredUsers = allUsers;
        if (searchTerm) {
            filteredUsers = allUsers.filter(user => 
                user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                user.email.toLowerCase().includes(searchTerm.toLowerCase())
            );
        }
        
        if (filteredUsers.length === 0) {
            userList.innerHTML = '<p class="text-muted text-center">No users match your search</p>';
            return;
        }
        
        userList.innerHTML = filteredUsers.map(user => {
            const isAssigned = selectedUserIds.includes(user.id);
            const approvalBadge = user.is_approved ? '' : '<span class="badge bg-warning text-dark ms-2">Pending</span>';
            return `
                <div class="user-card ${isAssigned ? 'assigned' : ''}" data-user-id="${user.id}">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input me-3" type="checkbox" 
                               id="user_${user.id}" 
                               ${isAssigned ? 'checked' : ''}
                               onchange="toggleUser(${user.id}, '${user.name.replace(/'/g, "\\'")}')">
                        <label class="form-check-label flex-grow-1" for="user_${user.id}">
                            <strong>${user.name}</strong> ${approvalBadge}
                            <div class="text-muted small">${user.email}</div>
                        </label>
                    </div>
                </div>
            `;
        }).join('');
    }

    function toggleUser(userId, userName) {
        const index = selectedUserIds.indexOf(userId);
        if (index > -1) {
            selectedUserIds.splice(index, 1);
        } else {
            selectedUserIds.push(userId);
        }
        updateAssignedUsersList();
        
        // Update the card styling
        const card = document.querySelector(`.user-card[data-user-id="${userId}"]`);
        if (card) {
            if (selectedUserIds.includes(userId)) {
                card.classList.add('assigned');
            } else {
                card.classList.remove('assigned');
            }
        }
    }

    function updateAssignedUsersList() {
        const container = document.getElementById('assignedUsersList');
        const noUsersMsg = document.getElementById('noUsersMessage');
        
        if (selectedUserIds.length === 0) {
            container.innerHTML = '';
            if (noUsersMsg) noUsersMsg.style.display = 'block';
            return;
        }
        
        if (noUsersMsg) noUsersMsg.style.display = 'none';
        
        const assignedUsersData = allUsers.filter(user => selectedUserIds.includes(user.id));
        container.innerHTML = assignedUsersData.map(user => `
            <div class="col-md-3 mb-2" data-user-id="${user.id}">
                <span class="badge bg-primary d-flex justify-content-between align-items-center">
                    ${user.name}
                    <i class="fas fa-times-circle ms-2" style="cursor: pointer;" onclick="removeUserFromList(${user.id})"></i>
                </span>
            </div>
        `).join('');
    }

    function removeUserFromList(userId) {
        const index = selectedUserIds.indexOf(userId);
        if (index > -1) {
            selectedUserIds.splice(index, 1);
            updateAssignedUsersList();
            if (document.getElementById('manageUsersModal').classList.contains('show')) {
                renderUserList(document.getElementById('userSearch').value);
            }
        }
    }

    // Search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('userSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                renderUserList(e.target.value);
            });
        }
    });

    function setupEventListeners() {
        // Form submission
        document.getElementById('quizForm').addEventListener('submit', handleSubmit);
    }

    function addSubject() {
        const container = document.getElementById('subjectsContainer');
        const newSubject = document.createElement('div');
        newSubject.className = 'subject-block';
        newSubject.setAttribute('data-index', subjectIndex);
        newSubject.innerHTML = `
            <button type="button" class="btn btn-sm btn-outline-danger remove-subject" onclick="removeSubject(this)">
                <i class="fas fa-trash"></i>
            </button>
            <div class="row">
                <div class="col-md-2">
                    <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control subject-name" placeholder="e.g., Anatomy" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Time per Question (sec) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control time-per-question" placeholder="60" value="60" min="10" max="600" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Max Questions <span class="text-muted">(default: 5)</span></label>
                    <input type="number" class="form-control max-questions" placeholder="5" min="1" value="5">
                    <small class="text-muted">Leave empty for all</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Questions File <span class="text-danger">*</span></label>
                    <div class="file-upload-area" onclick="triggerFileInput(this)">
                        <input type="file" class="file-input" accept=".xlsx,.xls,.csv" style="display: none;" required>
                        <div class="upload-content">
                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                            <p class="mb-0">Drag & drop .xlsx or .csv file or click to browse</p>
                        </div>
                        <div class="file-selected" style="display: none;">
                            <i class="fas fa-file-excel text-success"></i>
                            <span class="file-name"></span>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeFile(this)">Remove</button>
                        </div>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        `;
        container.appendChild(newSubject);
        subjectIndex++;
    }

    function removeSubject(btn) {
        btn.closest('.subject-block').remove();
    }

    function triggerFileInput(uploadArea) {
        const fileInput = uploadArea.querySelector('.file-input');
        fileInput.click();
        
        fileInput.onchange = function() {
            if (this.files[0]) {
                showSelectedFile(uploadArea, this.files[0]);
            }
        };
    }

    function showSelectedFile(uploadArea, file) {
        const uploadContent = uploadArea.querySelector('.upload-content');
        const fileSelected = uploadArea.querySelector('.file-selected');
        const fileName = fileSelected.querySelector('.file-name');
        
        uploadContent.style.display = 'none';
        fileName.textContent = file.name;
        fileSelected.style.display = 'block';
    }

    function removeFile(btn) {
        const uploadArea = btn.closest('.file-upload-area');
        const fileInput = uploadArea.querySelector('.file-input');
        const uploadContent = uploadArea.querySelector('.upload-content');
        const fileSelected = uploadArea.querySelector('.file-selected');
        
        fileInput.value = '';
        uploadContent.style.display = 'block';
        fileSelected.style.display = 'none';
    }

    async function handleSubmit(e) {
        e.preventDefault();
        
        if (!validateForm()) return;
        
        // Disable submit buttons and show loading
        const submitButtons = document.querySelectorAll('button[type="submit"]');
        submitButtons.forEach(btn => {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Quiz...';
        });
        
        const formData = new FormData();
        const isActive = e.submitter.dataset.action === 'activate';
        
        // Add basic data
        formData.append('title', document.getElementById('quizTitle').value);
        formData.append('participant_ids', JSON.stringify(selectedUserIds));
        formData.append('is_active', isActive ? '1' : '0');
        
        // Add subjects
        const subjects = document.querySelectorAll('.subject-block');
        subjects.forEach((subject, index) => {
            const name = subject.querySelector('.subject-name').value;
            const timePerQuestion = subject.querySelector('.time-per-question').value;
            const maxQuestions = subject.querySelector('.max-questions').value || 5;
            const file = subject.querySelector('.file-input').files[0];
            
            formData.append(`subjects[${index}][name]`, name);
            formData.append(`subjects[${index}][time_per_question]`, timePerQuestion);
            formData.append(`subjects[${index}][max_questions]`, maxQuestions);
            formData.append(`subjects[${index}][file]`, file);
        });
        
        try {
            const response = await fetch('{{ route("admin.quizzes.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                }
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                // Show success message
                const successDiv = document.createElement('div');
                successDiv.className = 'alert alert-success alert-dismissible fade show';
                successDiv.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>${result.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container-fluid').insertBefore(successDiv, document.querySelector('h2'));
                
                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = '{{ route("admin.quizzes.index") }}';
                }, 2000);
            } else {
                const errorMsg = result.message || result.error || 'Unknown error occurred';
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger alert-dismissible fade show';
                errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-circle me-2"></i><strong>Error:</strong> ${errorMsg}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container-fluid').insertBefore(errorDiv, document.querySelector('h2'));
                console.error('Server response:', result);
            }
        } catch (error) {
            console.error('Error:', error);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger alert-dismissible fade show';
            errorDiv.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i><strong>Error:</strong> Failed to create quiz. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-fluid').insertBefore(errorDiv, document.querySelector('h2'));
        } finally {
            // Re-enable submit buttons
            const submitButtons = document.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(btn => {
                btn.disabled = false;
                if (btn.dataset.action === 'activate') {
                    btn.innerHTML = '<i class="fas fa-check"></i> Save and Activate Quiz';
                } else {
                    btn.innerHTML = '<i class="fas fa-save"></i> Save as Draft';
                }
            });
        }
    }

    function validateForm() {
        let isValid = true;
        
        // Validate title
        const title = document.getElementById('quizTitle');
        if (!title.value.trim()) {
            showError(title, 'Quiz title is required');
            isValid = false;
        }
        
        // Validate participants
        if (selectedUserIds.length === 0) {
            alert('Please select at least one participant');
            isValid = false;
        }
        
        // Validate subjects
        const subjects = document.querySelectorAll('.subject-block');
        subjects.forEach(subject => {
            const name = subject.querySelector('.subject-name');
            const file = subject.querySelector('.file-input');
            
            if (!name.value.trim()) {
                showError(name, 'Subject name is required');
                isValid = false;
            }
            
            if (!file.files[0]) {
                showError(file, 'Please upload a file');
                isValid = false;
            }
        });
        
        return isValid;
    }

    function showError(element, message) {
        element.classList.add('is-invalid');
        const feedback = element.parentNode.querySelector('.invalid-feedback');
        if (feedback) feedback.textContent = message;
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>