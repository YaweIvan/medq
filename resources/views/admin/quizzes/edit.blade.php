<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-dark">Edit Quiz: {{ $quiz->title }}</h2>
                <a href="{{ route('admin.quizzes.select-edit') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Quizzes
                </a>
            </div>
            
            <form id="editQuizForm">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <h5 class="mb-3">Quiz Details</h5>
                    <div class="mb-3">
                        <label class="form-label">Quiz Title <span class="text-danger">*</span></label>
                        <input type="text" id="quizTitle" class="form-control" value="{{ $quiz->title }}" required>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="mb-3">Assigned Participants ({{ $quiz->users->count() }})</h5>
                    <button type="button" class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#manageUsersModal">
                        <i class="fas fa-users me-2"></i>Manage Users
                    </button>
                    <button type="button" class="btn btn-success btn-sm mb-3 ms-2" onclick="saveUserAssignments()" id="saveAssignmentsBtn" style="display: none;">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                    
                    <div class="row" id="assignedUsersList">
                        @foreach($quiz->users as $user)
                            <div class="col-md-3 mb-2" data-user-id="{{ $user->id }}">
                                <span class="badge bg-primary d-flex justify-content-between align-items-center">
                                    {{ $user->name }}
                                    <i class="fas fa-times-circle ms-2" style="cursor: pointer;" onclick="removeUserFromList({{ $user->id }})"></i>
                                </span>
                            </div>
                        @endforeach
                    </div>
                    
                    @if($quiz->users->count() == 0)
                        <p class="text-muted" id="noUsersMessage">No users assigned to this quiz yet.</p>
                    @endif
                </div>

                <div class="mb-4">
                    <h5 class="mb-3">Quiz Subjects & Questions</h5>
                    @php
                        $subjects = $quiz->questions->groupBy('subject.name');
                    @endphp
                    @foreach($subjects as $subjectName => $questions)
                        @php
                            $subjectId = $questions->first()->subject_id;
                            $currentTime = $questions->first()->time_per_question ?? 60;
                            $maxQuestions = $questions->first()->subject->max_questions ?? 5;
                            $marksPerQuestion = $questions->first()->subject->marks_per_question ?? 1;
                        @endphp
                        <div class="subject-info mb-3 p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1">{{ $subjectName }}</h6>
                                    <p class="mb-0 text-muted">
                                        {{ $questions->count() }} questions • {{ $currentTime }} seconds per question
                                        • Max: {{ $maxQuestions }} questions per attempt • {{ $marksPerQuestion }} mark(s) per question
                                    </p>
                                </div>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" data-bs-target="#editTimeModal{{ $subjectId }}">
                                        <i class="fas fa-clock me-1"></i>Edit Settings
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteSubject({{ $quiz->id }}, {{ $subjectId }}, '{{ addslashes($subjectName) }}')">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Edit Time Modal -->
                        <div class="modal fade" id="editTimeModal{{ $subjectId }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="fas fa-cog me-2"></i>Edit Settings for {{ $subjectName }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Time per Question (seconds)</label>
                                            <input type="number" class="form-control" id="timeInput{{ $subjectId }}" 
                                                   value="{{ $currentTime }}" min="10" max="600">
                                            <small class="text-muted">Set the time limit for each question (10-600 seconds)</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Max Questions per Attempt</label>
                                            <input type="number" class="form-control" id="maxQuestionsInput{{ $subjectId }}" 
                                                   value="{{ $maxQuestions }}" min="1" placeholder="5">
                                            <small class="text-muted">Limit how many questions from this subject each student attempts (optional)</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Marks per Question</label>
                                            <input type="number" class="form-control" id="marksInput{{ $subjectId }}" 
                                                   value="{{ $marksPerQuestion }}" min="1" placeholder="1">
                                            <small class="text-muted">Set score value for each correct answer in this subject</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-primary" 
                                                onclick="updateSubjectSettings({{ $quiz->id }}, {{ $subjectId }}, '{{ $subjectName }}')">
                                            <i class="fas fa-save me-2"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mb-4">
                    <h5 class="mb-3">Add New Subject</h5>
                    <div id="subjectsContainer">
                        <div class="subject-item mb-3 p-3 border rounded">
                            <div class="mb-3">
                                <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control subject-name" placeholder="e.g., Anatomy">
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Time per Question (seconds) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control time-per-question" placeholder="60" value="60" min="10" max="600">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Marks per Question</label>
                                    <input type="number" class="form-control marks-per-question" placeholder="1" min="1" value="1">
                                    <small class="text-muted">Score per correct answer</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Max Questions per Attempt</label>
                                    <input type="number" class="form-control max-questions" placeholder="5" min="1" value="5">
                                    <small class="text-muted">Optional: Limit questions per student</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Questions File <span class="text-danger">*</span></label>
                                <input type="file" class="form-control subject-file" accept=".xlsx,.xls,.csv">
                                <small class="text-muted">Drag & drop .xlsx or .csv file or click to browse</small>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSubject()">
                        <i class="fas fa-plus me-2"></i>Add Another Subject
                    </button>
                    <button type="button" class="btn btn-sm btn-success ms-2" onclick="uploadNewSubjects()">
                        <i class="fas fa-upload me-2"></i>Upload Subjects
                    </button>
                    
                    <div class="alert alert-info mt-3">
                        <strong><i class="fas fa-info-circle me-2"></i>Excel/CSV File Format Requirements</strong>
                        <p class="mb-0 mt-2">Columns: Number, Question, Option A, Option B, Option C, Option D, Option E (optional), Correct Answer</p>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="isActive" {{ $quiz->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">
                            Quiz is Active
                        </label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Quiz
                    </button>
                    <button type="button" class="btn btn-warning" onclick="resetQuiz()">
                        <i class="fas fa-redo"></i> Reset Quiz
                    </button>
                    <a href="{{ route('admin.quizzes.select-edit') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
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
                        <i class="fas fa-users me-2"></i>Manage Quiz Users
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="search-box">
                        <input type="text" id="userSearch" class="form-control" placeholder="Search users by name or email...">
                    </div>

                    <div class="d-flex align-items-center justify-content-between px-1 py-2 border-bottom mb-2">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="selectAllUsers" onchange="toggleSelectAll(this.checked)">
                            <label class="form-check-label fw-semibold" for="selectAllUsers">Select All</label>
                        </div>
                        <small class="text-muted" id="selectedCount"></small>
                    </div>

                    <div class="user-list" id="userList">
                        <p class="text-center text-muted">Loading users...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveUserAssignments()">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let assignedUsers = @json($quiz->users->pluck('id')->toArray());
    let allUsers = [];

    // Load all users on page load so X button works immediately
    document.addEventListener('DOMContentLoaded', async function() {
        await loadUsers();
    });

    // Reload users when modal opens (in case users were added/removed)
    document.getElementById('manageUsersModal').addEventListener('show.bs.modal', async function() {
        await loadUsers();
    });

    async function loadUsers() {
        try {
            const response = await fetch('{{ route("admin.users.api") }}');
            const data = await response.json();
            allUsers = data.users || [];
            
            console.log('Loaded users:', allUsers); // Debug
            
            if (allUsers.length === 0 && document.getElementById('manageUsersModal').classList.contains('show')) {
                document.getElementById('userList').innerHTML = '<p class="text-muted text-center">No approved quizzer users found in the system</p>';
            } else if (document.getElementById('manageUsersModal').classList.contains('show')) {
                renderUserList();
            }
        } catch (error) {
            console.error('Error loading users:', error);
            if (document.getElementById('manageUsersModal').classList.contains('show')) {
                document.getElementById('userList').innerHTML = '<p class="text-danger">Error loading users. Please try again.</p>';
            }
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
            updateSelectAllState();
            return;
        }
        
        userList.innerHTML = filteredUsers.map(user => {
            const isAssigned = assignedUsers.includes(user.id);
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
        updateSelectAllState();
    }

    function updateSelectAllState() {
        const visibleCheckboxes = document.querySelectorAll('#userList .form-check-input');
        const allChecked = visibleCheckboxes.length > 0 && [...visibleCheckboxes].every(cb => cb.checked);
        const someChecked = [...visibleCheckboxes].some(cb => cb.checked);
        const selectAll = document.getElementById('selectAllUsers');
        if (selectAll) {
            selectAll.checked = allChecked;
            selectAll.indeterminate = someChecked && !allChecked;
        }
        const count = document.getElementById('selectedCount');
        if (count) count.textContent = assignedUsers.length + ' selected';
    }

    function toggleSelectAll(checked) {
        const visibleCards = document.querySelectorAll('#userList .user-card');
        visibleCards.forEach(card => {
            const userId = parseInt(card.dataset.userId);
            const cb = card.querySelector('.form-check-input');
            if (checked) {
                if (!assignedUsers.includes(userId)) {
                    assignedUsers.push(userId);
                }
                card.classList.add('assigned');
                if (cb) cb.checked = true;
            } else {
                const idx = assignedUsers.indexOf(userId);
                if (idx > -1) assignedUsers.splice(idx, 1);
                card.classList.remove('assigned');
                if (cb) cb.checked = false;
            }
        });
        updateAssignedUsersList();
        document.getElementById('saveAssignmentsBtn').style.display = 'inline-block';
        const count = document.getElementById('selectedCount');
        if (count) count.textContent = assignedUsers.length + ' selected';
    }

    function toggleUser(userId, userName) {
        const index = assignedUsers.indexOf(userId);
        if (index > -1) {
            // User is assigned - remove them
            assignedUsers.splice(index, 1);
            console.log('Unassigned user:', userId, userName); // Debug
        } else {
            // User is not assigned - add them
            assignedUsers.push(userId);
            console.log('Assigned user:', userId, userName); // Debug
        }
        console.log('Current assigned users:', assignedUsers); // Debug
        updateAssignedUsersList();
        
        // Show save button when changes are made
        document.getElementById('saveAssignmentsBtn').style.display = 'inline-block';
        
        // Update the card styling
        const card = document.querySelector(`.user-card[data-user-id="${userId}"]`);
        if (card) {
            if (assignedUsers.includes(userId)) {
                card.classList.add('assigned');
            } else {
                card.classList.remove('assigned');
            }
        }
        updateSelectAllState();
    }

    function updateAssignedUsersList() {
        const container = document.getElementById('assignedUsersList');
        const noUsersMsg = document.getElementById('noUsersMessage');
        
        if (assignedUsers.length === 0) {
            container.innerHTML = '';
            if (noUsersMsg) noUsersMsg.style.display = 'block';
            return;
        }
        
        if (noUsersMsg) noUsersMsg.style.display = 'none';
        
        const assignedUsersData = allUsers.filter(user => assignedUsers.includes(user.id));
        container.innerHTML = assignedUsersData.map(user => `
            <div class="col-md-3 mb-2" data-user-id="${user.id}">
                <span class="badge bg-primary d-flex justify-content-between align-items-center">
                    ${user.name}
                    <i class="fas fa-times-circle ms-2" style="cursor: pointer;" onclick="removeUserFromList(${user.id})"></i>
                </span>
            </div>
        `).join('');
        
        // Update count
        document.querySelector('h5.mb-3').textContent = `Assigned Participants (${assignedUsers.length})`;
    }

    function removeUserFromList(userId) {
        console.log('Removing user:', userId); // Debug
        
        // Remove from array
        const index = assignedUsers.indexOf(userId);
        if (index > -1) {
            assignedUsers.splice(index, 1);
            console.log('Updated assignedUsers:', assignedUsers); // Debug
            
            // Update the UI immediately
            updateAssignedUsersList();
            
            // Show save button
            document.getElementById('saveAssignmentsBtn').style.display = 'inline-block';
            
            // If modal is open, update the checkboxes there too
            if (document.getElementById('manageUsersModal').classList.contains('show')) {
                renderUserList(document.getElementById('userSearch').value);
            }
        }
    }

    async function saveUserAssignments() {
        try {
            console.log('Saving user assignments:', assignedUsers); // Debug
            
            const response = await fetch('{{ route("admin.quizzes.update-users", $quiz->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                },
                body: JSON.stringify({
                    user_ids: assignedUsers // Send empty array if no users assigned
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Hide save button
                document.getElementById('saveAssignmentsBtn').style.display = 'none';
                
                alert(`User assignments updated! ${data.assigned_count} user(s) assigned.`);
                if (window.bootstrap?.Modal) {
                    bootstrap.Modal.getInstance(document.getElementById('manageUsersModal'))?.hide();
                }
                
                // Reload current edit page
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                alert('Error updating user assignments. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating user assignments. Please try again.');
        }
    }

    // Search functionality
    document.getElementById('userSearch').addEventListener('input', function(e) {
        renderUserList(e.target.value);
    });

    document.getElementById('editQuizForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('title', document.getElementById('quizTitle').value);
        formData.append('is_active', document.getElementById('isActive').checked ? '1' : '0');
        
        try {
            const response = await fetch('{{ route("admin.quizzes.update", $quiz->id) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                }
            });
            
            if (response.ok) {
                alert('Quiz updated successfully!');
                window.location.reload();
            } else {
                alert('Error updating quiz. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating quiz. Please try again.');
        }
    });

    async function resetQuiz() {
        if (!confirm('Are you sure you want to reset "{{ $quiz->title }}"?\n\nThis will:\n• Mark all questions as unused (is_used = false)\n• Delete all quiz attempts\n• Allow users to retake all questions\n\nThis action cannot be undone!')) {
            return;
        }
        
        try {
            const response = await fetch('{{ route("admin.quizzes.reset", $quiz->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                alert(`Quiz reset successfully!\n\n• ${data.questions_reset} questions marked as unused\n• ${data.attempts_deleted} attempts deleted`);
                window.location.reload();
            } else {
                const error = await response.json();
                alert('Error resetting quiz: ' + (error.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error resetting quiz. Please try again.');
        }
    }

    function addSubject() {
        const container = document.getElementById('subjectsContainer');
        const newSubject = document.createElement('div');
        newSubject.className = 'subject-item mb-3 p-3 border rounded';
        newSubject.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control subject-name" placeholder="e.g., Anatomy">
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Time per Question (seconds) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control time-per-question" placeholder="60" value="60" min="10" max="600">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Marks per Question</label>
                    <input type="number" class="form-control marks-per-question" placeholder="1" min="1" value="1">
                    <small class="text-muted">Score per correct answer</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Max Questions per Attempt</label>
                    <input type="number" class="form-control max-questions" placeholder="5" min="1" value="5">
                    <small class="text-muted">Optional: Limit questions per student</small>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Questions File <span class="text-danger">*</span></label>
                <input type="file" class="form-control subject-file" accept=".xlsx,.xls,.csv">
                <small class="text-muted">Drag & drop .xlsx or .csv file or click to browse</small>
            </div>
            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">
                <i class="fas fa-trash"></i> Remove
            </button>
        `;
        container.appendChild(newSubject);
    }

    async function uploadNewSubjects() {
        const subjects = [];
        const subjectItems = document.querySelectorAll('.subject-item');
        
        for (let item of subjectItems) {
            const name = item.querySelector('.subject-name').value.trim();
            const timePerQuestion = item.querySelector('.time-per-question').value || 60;
            const marksPerQuestion = item.querySelector('.marks-per-question').value || 1;
            const maxQuestions = item.querySelector('.max-questions').value || 5;
            const file = item.querySelector('.subject-file').files[0];
            
            if (name && file) {
                subjects.push({ name, timePerQuestion, marksPerQuestion, maxQuestions, file });
            }
        }
        
        if (subjects.length === 0) {
            alert('Please add at least one subject with a file');
            return;
        }
        
        const formData = new FormData();
        formData.append('quiz_id', '{{ $quiz->id }}');
        
        subjects.forEach((subject, index) => {
            formData.append(`subjects[${index}][name]`, subject.name);
            formData.append(`subjects[${index}][time_per_question]`, subject.timePerQuestion);
            formData.append(`subjects[${index}][marks_per_question]`, subject.marksPerQuestion);
            formData.append(`subjects[${index}][max_questions]`, subject.maxQuestions);
            formData.append(`subjects[${index}][file]`, subject.file);
        });
        
        try {
            const response = await fetch('{{ route("admin.quizzes.add-subjects", $quiz->id) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error uploading subjects. Please try again.');
        }
    }

    async function updateSubjectSettings(quizId, subjectId, subjectName) {
        const newTime = document.getElementById(`timeInput${subjectId}`).value;
        const maxQuestions = document.getElementById(`maxQuestionsInput${subjectId}`).value;
        const marksPerQuestion = document.getElementById(`marksInput${subjectId}`).value || 1;
        
        if (newTime < 10 || newTime > 600) {
            alert('Time must be between 10 and 600 seconds');
            return;
        }

        if (marksPerQuestion < 1) {
            alert('Marks per question must be at least 1');
            return;
        }
        
        try {
            const response = await fetch(`/admin/quizzes/${quizId}/subjects/${subjectId}/update-settings`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    time_per_question: newTime,
                    marks_per_question: marksPerQuestion,
                    max_questions: maxQuestions || 5
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Hide modal
                if (window.bootstrap?.Modal) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById(`editTimeModal${subjectId}`));
                    if (modal) modal.hide();
                }
                
                alert(`Settings updated successfully for "${subjectName}"!\n${data.updated_count} question(s) updated.`);
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating settings. Please try again.');
        }
    }

    async function deleteSubject(quizId, subjectId, subjectName) {
        if (!confirm(`Are you sure you want to delete "${subjectName}" and all its questions from this quiz?`)) {
            return;
        }
        
        try {
            const response = await fetch(`/admin/quizzes/${quizId}/subjects/${subjectId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error deleting subject. Please try again.');
        }
    }
    </script>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>