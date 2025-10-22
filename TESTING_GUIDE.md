# MedQ Testing Guide

## Complete Flow Testing

### 1. Setup & Database
```bash
# Navigate to project
cd c:\xampp\htdocs\MedQ

# Run migrations
php artisan migrate:fresh

# Seed database with sample data
php artisan db:seed

# Start server
php artisan serve
```

### 2. Admin Flow - Creating a Quiz

#### Step 1: Login as Admin
- Go to: http://127.0.0.1:8000/login
- Email: `admin@medq.com`
- Password: `password`

#### Step 2: Approve Students (if needed)
- Navigate to: Admin Dashboard → Approvals
- Approve any pending student registrations
- Default seeded students should already be approved

#### Step 3: Create a Quiz
- Navigate to: Admin Dashboard → Quiz Management → Create New Quiz
- Or directly: http://127.0.0.1:8000/admin/quizzes/create

**Fill in the form:**
1. **Quiz Title**: Enter a title (e.g., "Week 1 Medical Quiz")
2. **Select Participants**: 
   - Click the search box
   - Check "Select All" or select individual students
   - Selected users appear as tags above the search box
3. **Add Subjects & Questions**:
   - Subject Name: Enter subject (e.g., "Anatomy")
   - Upload File: Click to upload .xlsx or .csv file
   - Click "Add Another Subject" to add more subjects

**Excel/CSV File Format:**
```
Number | Question | Option A | Option B | Option C | Option D | Correct Answer
1 | What is the normal heart rate? | 60-100 bpm | 40-60 bpm | 100-120 bpm | 120-140 bpm | A
2 | Which organ produces insulin? | Liver | Pancreas | Kidney | Spleen | B
```

**Download Sample Template:**
- Click "Download Sample Template" button on the create page
- Use this as a reference for your file format

4. **Save Quiz**:
   - Click "Save and Activate Quiz" (quiz will be active immediately)
   - OR "Save as Draft" (quiz will be inactive)

#### Step 4: Verify Quiz Created
- Navigate to: Admin Dashboard → Quiz Management
- You should see your newly created quiz in the list
- Check:
  - Quiz title
  - Number of assigned users
  - Number of questions
  - Status (Active/Inactive)

### 3. Student Flow - Taking a Quiz

#### Step 1: Login as Student
- Logout from admin account
- Go to: http://127.0.0.1:8000/login
- Email: `john@example.com` (or any approved student)
- Password: `password`

#### Step 2: View Available Quizzes
- You should see the dashboard with active quizzes
- Only quizzes you're assigned to will appear
- Only active quizzes are shown

#### Step 3: Select a Quiz
- Click "Start Quiz" on any quiz card
- You'll see a list of subjects in that quiz

#### Step 4: Select a Subject
- Click "View Questions Grid" on any subject
- You'll see a grid of available questions (numbered)

**Grid Legend:**
- White boxes = Available questions
- Green boxes = Questions you've attempted
- Gray boxes = Locked/used questions

#### Step 5: Answer Questions
- Click on any available (white) question number
- Read the question and options
- Click on your answer choice (A, B, C, or D)
- Click "Submit Answer"
- You'll see immediate feedback:
  - Green border = Correct answer
  - Red border = Wrong answer (correct answer will be highlighted in green)
- After 1.5 seconds, you'll be redirected back to the grid

#### Step 6: Continue or Switch Subjects
- You can attempt up to 5 questions per subject
- Once you've attempted 5 questions in a subject, you can't attempt more
- Go back to subjects view to select another subject
- Questions you attempt become locked for other students

### 4. Admin Monitoring

#### View Statistics
- Navigate to: Admin Dashboard → Statistics
- See quiz performance metrics

#### View Leaderboard
- Navigate to: Admin Dashboard → Leaderboard
- See top-performing students

#### Manage Quizzes
- Navigate to: Admin Dashboard → Quiz Management
- Actions available:
  - Edit quiz (change title, status)
  - Delete quiz
  - Toggle Active/Inactive status

### 5. Common Issues & Solutions

#### Issue: No questions imported
**Solution:**
- Check Excel/CSV file format (must have exactly 7 columns)
- Ensure correct answer column contains only A, B, C, or D
- No empty cells in data rows
- First row should be headers

#### Issue: Student can't see quiz
**Solution:**
- Check if quiz is active (Admin → Quiz Management)
- Check if student is assigned to the quiz
- Check if student account is approved (Admin → Approvals)

#### Issue: Can't click on question in grid
**Solution:**
- Gray boxes are locked/used questions
- Green boxes are already attempted by you
- You can only attempt 5 questions per subject
- Only white boxes are clickable

#### Issue: Excel file upload fails
**Solution:**
- Try converting to CSV format
- Ensure file is not corrupted
- Check file size (should be reasonable)
- Verify column structure matches template

### 6. Sample Test Data

**Sample CSV Content:**
```csv
Number,Question,Option A,Option B,Option C,Option D,Correct Answer
1,What is the largest organ in the human body?,Heart,Liver,Skin,Brain,C
2,How many bones are in the adult human body?,206,208,210,212,A
3,What is the normal body temperature?,36.5-37.5°C,35-36°C,38-39°C,37-38°C,A
4,Which blood type is the universal donor?,A+,B+,O-,AB+,C
5,What is the powerhouse of the cell?,Nucleus,Mitochondria,Ribosome,Golgi,B
```

### 7. Testing Checklist

- [ ] Admin can login
- [ ] Admin can approve students
- [ ] Admin can create quiz with Excel upload
- [ ] Admin can create quiz with CSV upload
- [ ] Quiz appears in Quiz Management page
- [ ] Quiz shows correct participant count
- [ ] Quiz shows correct question count
- [ ] Admin can toggle quiz active/inactive
- [ ] Student can login
- [ ] Student sees assigned active quizzes
- [ ] Student can view quiz subjects
- [ ] Student can view question grid
- [ ] Student can click and answer questions
- [ ] Student sees correct/incorrect feedback
- [ ] Student is redirected after answering
- [ ] Attempted questions show as green in grid
- [ ] Student can't attempt more than 5 per subject
- [ ] Questions lock after being used
- [ ] Statistics page shows data
- [ ] Leaderboard shows rankings

### 8. Quick Test Commands

```bash
# Reset database and reseed
php artisan migrate:fresh --seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Check logs for errors
tail -f storage/logs/laravel.log
```

### 9. File Locations

- **Admin Controllers**: `app/Http/Controllers/AdminController.php`
- **Student Controllers**: `app/Http/Controllers/QuizzerController.php`
- **Quiz Creation View**: `resources/views/admin/quizzes/create.blade.php`
- **Quiz List View**: `resources/views/admin/quizzes/index.blade.php`
- **Student Dashboard**: `resources/views/quizzer/dashboard.blade.php`
- **Question Grid**: `resources/views/quizzer/quiz_grid.blade.php`
- **Question View**: `resources/views/quizzer/question.blade.php`

### 10. API Endpoints

- `GET /admin/users/api` - Get approved students (for participant selection)
- `POST /admin/quizzes` - Create new quiz
- `POST /admin/quizzes/{id}/toggle` - Toggle quiz status
- `DELETE /admin/quizzes/{id}` - Delete quiz
- `POST /quizzer/submit-answer` - Submit answer
- `GET /admin/download-template` - Download CSV template

---

## Need Help?

If you encounter any issues:
1. Check the browser console for JavaScript errors
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify database connections in `.env`
4. Ensure all migrations have run
5. Clear all caches
