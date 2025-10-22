# MedQ Complete Flow Summary

## 🔄 System Flow Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     ADMIN WORKFLOW                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. Login → Admin Dashboard                                 │
│     ↓                                                       │
│  2. Approve Students (if needed)                            │
│     ↓                                                       │
│  3. Create Quiz                                             │
│     • Enter quiz title                                      │
│     • Select participants (students)                        │
│     • Add subjects                                          │
│     • Upload Excel/CSV files with questions                 │
│     • Save as Active or Draft                               │
│     ↓                                                       │
│  4. Quiz appears in "All Quizzes" page                      │
│     • Shows participant count                               │
│     • Shows question count                                  │
│     • Shows active/inactive status                          │
│     ↓                                                       │
│  5. Monitor & Manage                                        │
│     • View statistics                                       │
│     • Check leaderboards                                    │
│     • Edit/Delete quizzes                                   │
│     • Toggle active status                                  │
│                                                             │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                   STUDENT WORKFLOW                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. Register → Wait for Admin Approval                      │
│     ↓                                                       │
│  2. Login → Student Dashboard                               │
│     • See only assigned active quizzes                      │
│     ↓                                                       │
│  3. Select Quiz → View Subjects                             │
│     • See all subjects in the quiz                          │
│     • See available question count per subject              │
│     ↓                                                       │
│  4. Select Subject → View Question Grid                     │
│     • Grid shows all questions (numbered)                   │
│     • White = Available                                     │
│     • Green = Already attempted by you                      │
│     • Gray = Locked/used by others                          │
│     ↓                                                       │
│  5. Click Question Number → Answer Question                 │
│     • Read question and options                             │
│     • Select answer (A, B, C, or D)                         │
│     • Submit answer                                         │
│     ↓                                                       │
│  6. Get Instant Feedback                                    │
│     • Green border = Correct                                │
│     • Red border = Wrong (correct answer highlighted)       │
│     ↓                                                       │
│  7. Return to Grid                                          │
│     • Attempted question now shows green                    │
│     • Question is locked for other students                 │
│     • Can attempt up to 5 questions per subject             │
│     ↓                                                       │
│  8. View Statistics                                         │
│     • Track personal performance                            │
│     • See accuracy and attempts                             │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## 📊 Data Flow

### Quiz Creation Flow
```
Admin Input → Form Validation → File Upload → File Processing → Database Storage

1. Admin fills form:
   - Quiz title
   - Participant selection
   - Subject names
   - Excel/CSV files

2. JavaScript validates:
   - Title not empty
   - At least one participant selected
   - Subject names filled
   - Files uploaded

3. Form submits via AJAX:
   - POST to /admin/quizzes
   - FormData with files

4. Server processes:
   - Validates request
   - Creates quiz record
   - Attaches participants
   - Processes each Excel/CSV file
   - Creates subject records
   - Creates question records
   - Uses database transaction (all or nothing)

5. Response:
   - Success: Redirect to quiz list
   - Error: Show error message
```

### Question Answering Flow
```
Student Click → Load Question → Submit Answer → Process → Feedback → Update

1. Student clicks question number in grid
   - GET /quizzer/question/{id}

2. Server checks:
   - Student has access to quiz
   - Question not already attempted
   - Subject attempt limit not reached (5 max)

3. Question displayed:
   - Question text
   - Four options (A, B, C, D)
   - Submit button

4. Student selects and submits:
   - POST /quizzer/submit-answer
   - JSON: {question_id, selected_answer}

5. Server processes:
   - Validates answer format
   - Checks if correct
   - Creates quiz_attempt record
   - Marks question as used (is_used = true)
   - Returns result

6. Client shows feedback:
   - Correct: Green border
   - Wrong: Red border + highlight correct answer
   - Auto-redirect to grid after 1.5s

7. Grid updates:
   - Attempted question shows green
   - Question locked for others
   - Attempt count incremented
```

## 🗄️ Database Structure

### Key Tables & Relationships

```
users
├── id
├── name
├── email
├── password
├── role (admin/quizzer)
└── is_approved

quizzes
├── id
├── title
├── description
└── is_active

subjects
├── id
└── name

questions
├── id
├── quiz_id (→ quizzes)
├── subject_id (→ subjects)
├── question_text
├── option_a
├── option_b
├── option_c
├── option_d
├── correct_answer
└── is_used

quiz_user (pivot)
├── quiz_id (→ quizzes)
└── user_id (→ users)

quiz_attempts
├── id
├── user_id (→ users)
├── quiz_id (→ quizzes)
├── question_id (→ questions)
├── selected_answer
└── is_correct
```

## 🔐 Access Control

### Admin Routes
- Middleware: `auth`, `admin`
- Can access: All admin/* routes
- Cannot access: quizzer/* routes

### Student Routes
- Middleware: `auth`, `quizzer`
- Can access: All quizzer/* routes
- Cannot access: admin/* routes
- Additional checks:
  - Must be assigned to quiz
  - Quiz must be active
  - Question not already attempted
  - Subject limit not exceeded (5 max)

## 📁 File Processing

### Supported Formats
1. **Excel (.xlsx, .xls)**
   - Uses PHP ZipArchive
   - Parses XML from Excel file
   - Extracts shared strings
   - Reads worksheet data
   - Falls back to CSV if fails

2. **CSV (.csv)**
   - Auto-detects delimiter (comma, semicolon, tab)
   - Reads line by line
   - Validates data format
   - Creates questions

### Validation Rules
- Must have exactly 7 columns
- First row = headers (skipped)
- Columns: Number, Question, A, B, C, D, Answer
- Answer must be A, B, C, or D
- No empty cells in required fields
- Transaction ensures all-or-nothing import

## 🎯 Key Features

### Question Locking
- When student attempts question → `is_used = true`
- Other students can't attempt locked questions
- Prevents duplicate attempts
- Ensures fair distribution

### Attempt Limiting
- Maximum 5 questions per subject per student
- Counted in quiz_attempts table
- Checked before showing questions
- Enforced in controller logic

### Instant Feedback
- Answer checked immediately
- Visual feedback (colors)
- Correct answer revealed if wrong
- No page reload needed (AJAX)

### Randomization
- Questions can be randomized
- Admin can reset question locks
- Allows quiz reuse

## 🔧 Technical Stack

### Backend
- **Framework**: Laravel 11
- **Language**: PHP 8.2+
- **Database**: MySQL
- **File Processing**: Native PHP (ZipArchive, fgetcsv)

### Frontend
- **CSS Framework**: Bootstrap 5
- **Icons**: Font Awesome 6
- **JavaScript**: Vanilla JS (no jQuery)
- **AJAX**: Fetch API

### Key Libraries
- Laravel Authentication
- Laravel Eloquent ORM
- Laravel Migrations
- Laravel Seeders

## 📝 Important Files

### Controllers
- `AdminController.php` - All admin functions
- `QuizzerController.php` - All student functions
- `AuthController.php` - Login/register

### Views
- `admin/quizzes/create.blade.php` - Quiz creation form
- `admin/quizzes/index.blade.php` - Quiz list
- `quizzer/dashboard.blade.php` - Student dashboard
- `quizzer/quiz_subjects.blade.php` - Subject selection
- `quizzer/quiz_grid.blade.php` - Question grid
- `quizzer/question.blade.php` - Question display

### Routes
- `web.php` - All application routes

### Database
- `migrations/` - Database schema
- `seeders/MedQSeeder.php` - Sample data

## 🚀 Deployment Checklist

- [ ] Database configured (.env)
- [ ] Migrations run
- [ ] Seeder run (for testing)
- [ ] Storage permissions set
- [ ] APP_KEY generated
- [ ] Server started
- [ ] Admin account created
- [ ] Test quiz created
- [ ] Student can access quiz
- [ ] Questions display correctly
- [ ] Answers submit successfully
- [ ] Feedback shows correctly

## 💡 Best Practices

1. **Always use transactions** for quiz creation
2. **Validate file format** before processing
3. **Check permissions** before showing data
4. **Lock questions** after use
5. **Limit attempts** per subject
6. **Provide instant feedback** to students
7. **Log errors** for debugging
8. **Use AJAX** for better UX
9. **Show loading states** during operations
10. **Clear error messages** for users

---

## 🎓 Summary

MedQ is a complete quiz platform with:
- ✅ Role-based access (Admin/Student)
- ✅ Excel/CSV question import
- ✅ Subject-based organization
- ✅ Question locking mechanism
- ✅ Attempt limiting (5 per subject)
- ✅ Instant feedback system
- ✅ Statistics tracking
- ✅ Responsive design
- ✅ Modern UI/UX

The system is production-ready and fully functional!
