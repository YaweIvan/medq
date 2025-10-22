# MedQ Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Step 1: Setup (2 minutes)
```bash
cd c:\xampp\htdocs\MedQ
php artisan migrate:fresh --seed
php artisan serve
```

### Step 2: Login as Admin (1 minute)
- Open: http://127.0.0.1:8000
- Click "Admin Login"
- Email: `admin@medq.com`
- Password: `password`

### Step 3: Create Your First Quiz (2 minutes)
1. Click "Quiz Management" in sidebar
2. Click "Create New Quiz"
3. Enter quiz title: "My First Quiz"
4. Click "Select All" to select all students
5. Enter subject name: "General Medicine"
6. Click "Download Sample Template" button
7. Upload the downloaded CSV file
8. Click "Save and Activate Quiz"

### Step 4: Test as Student (1 minute)
1. Logout (top right)
2. Login with:
   - Email: `john@example.com`
   - Password: `password`
3. Click "Start Quiz" on your quiz
4. Click "View Questions Grid"
5. Click any question number
6. Select an answer and submit

## ✅ That's it! Your quiz platform is working!

---

## 📝 Create Your Own Questions

### Option 1: Use the Template
1. Download template from quiz creation page
2. Edit in Excel or any spreadsheet app
3. Save as CSV or XLSX
4. Upload when creating quiz

### Option 2: Create from Scratch
Create a CSV file with this format:

```csv
Number,Question,Option A,Option B,Option C,Option D,Correct Answer
1,Your question here?,Answer A,Answer B,Answer C,Answer D,A
2,Another question?,Answer A,Answer B,Answer C,Answer D,B
```

**Rules:**
- First row = headers (don't change)
- Correct Answer = A, B, C, or D only
- No empty cells
- Save as .csv or .xlsx

---

## 🎯 Key Features to Try

### For Admins:
- ✅ Approve/reject student registrations
- ✅ Create multiple quizzes
- ✅ Assign specific students to quizzes
- ✅ Upload questions via Excel/CSV
- ✅ Activate/deactivate quizzes
- ✅ View statistics and leaderboards

### For Students:
- ✅ Take assigned quizzes
- ✅ Choose from multiple subjects
- ✅ See question grid (50 questions max per subject)
- ✅ Get instant feedback (correct/incorrect)
- ✅ Track personal statistics
- ✅ Maximum 5 questions per subject

---

## 🔧 Troubleshooting

### Quiz not showing for student?
- Check if quiz is "Active" (green badge)
- Check if student is assigned to quiz
- Check if student account is approved

### Excel upload not working?
- Try CSV format instead
- Check file has exactly 7 columns
- Ensure "Correct Answer" column has only A, B, C, or D
- Download and use the sample template

### Can't click questions in grid?
- Gray = locked/used by others
- Green = you already attempted
- White = available (click these!)
- You can only attempt 5 per subject

---

## 📚 More Help

- Full documentation: See `README.md`
- Detailed testing guide: See `TESTING_GUIDE.md`
- Sample template: `public/sample_quiz_template.csv`

---

## 🎓 Default Test Accounts

**Admin:**
- Email: admin@medq.com
- Password: password

**Students:**
- john@example.com / password
- jane@example.com / password
- bob@example.com / password

---

## 💡 Pro Tips

1. **Start Small**: Create a quiz with 5-10 questions first
2. **Test Flow**: Login as both admin and student to see both sides
3. **Use Template**: Always start with the sample template
4. **Check Format**: Correct Answer column must be A, B, C, or D (uppercase)
5. **Active Status**: Remember to activate quiz for students to see it

---

## 🎉 You're Ready!

Your MedQ platform is now fully functional. Create quizzes, assign students, and start testing medical knowledge!

Need more features? Check the full README.md for advanced options.
