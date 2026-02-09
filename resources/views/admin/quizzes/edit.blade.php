<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - MedQ</title>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-dark">Edit Quiz: {{ $quiz->title }}</h2>
                <a href="{{ route('admin.quizzes.index') }}" class="btn btn-secondary">
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
                        <div class="subject-info mb-3 p-3 border rounded">
                            <h6 class="fw-bold">{{ $subjectName }}</h6>
                            <p class="mb-0 text-muted">{{ $questions->count() }} questions</p>
                        </div>
                    @endforeach
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
                bootstrap.Modal.getInstance(document.getElementById('manageUsersModal'))?.hide();
                
                // Redirect to quizzes index
                setTimeout(() => {
                    window.location.href = '{{ route("admin.quizzes.index") }}';
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
                window.location.href = '{{ route("admin.quizzes.index") }}';
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
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>