# MedQ Troubleshooting Guide

## 🔍 Common Issues & Solutions

### 1. Quiz Creation Issues

#### ❌ "No questions were imported"
**Causes:**
- Excel/CSV file format incorrect
- Empty cells in data rows
- Wrong answer format (not A, B, C, or D)
- File corrupted

**Solutions:**
```bash
# Download the sample template
# Go to: http://127.0.0.1:8000/admin/quizzes/create
# Click "Download Sample Template"

# Or use this format:
Number,Question,Option A,Option B,Option C,Option D,Correct Answer
1,Sample question?,Answer A,Answer B,Answer C,Answer D,A
```

**Checklist:**
- [ ] File has exactly 7 columns
- [ ] First row is headers
- [ ] No empty cells in data rows
- [ ] Correct Answer column has only A, B, C, or D
- [ ] File is .xlsx, .xls, or .csv format

---

#### ❌ "Please select at least one participant"
**Cause:** No students selected

**Solution:**
1. Click the search box under "Select Participants"
2. Check "Select All" or individual students
3. Verify selected users appear as tags above search box

---

#### ❌ Excel upload button not working
**Cause:** JavaScript error or file input issue

**Solutions:**
1. Check browser console (F12) for errors
2. Try CSV format instead of Excel
3. Clear browser cache
4. Try different browser

---

### 2. Student Access Issues

#### ❌ Student can't see any quizzes
**Causes:**
- Student not approved
- No quizzes assigned to student
- All quizzes are inactive

**Solutions:**
```bash
# Check student approval
1. Login as admin
2. Go to Approvals page
3. Find student and click "Approve"

# Check quiz assignment
1. Login as admin
2. Go to Quiz Management
3. Edit quiz
4. Verify student is in participant list

# Check quiz status
1. Login as admin
2. Go to Quiz Management
3. Verify quiz has green "Active" badge
4. If not, click toggle button to activate
```

---

#### ❌ "Not authorized for this quiz"
**Cause:** Student not assigned to quiz

**Solution:**
1. Admin: Edit quiz
2. Add student to participants
3. Save quiz
4. Student: Refresh dashboard

---

### 3. Question Display Issues

#### ❌ Can't click questions in grid
**Causes:**
- Question already attempted (green)
- Question locked by others (gray)
- Reached 5-question limit for subject

**Solutions:**
- Green boxes = You already answered these
- Gray boxes = Locked/used by other students
- Only white boxes are clickable
- Check attempt count at top (X/5 attempted)

---

#### ❌ "Maximum 5 questions per subject reached"
**Cause:** Already attempted 5 questions in this subject

**Solution:**
- This is by design (limit is 5 per subject)
- Go back and select different subject
- Each subject has its own 5-question limit

---

#### ❌ Question page shows error
**Causes:**
- Question already attempted
- Question doesn't exist
- Not authorized

**Solutions:**
1. Go back to grid
2. Try different question
3. Check if question is white (available)
4. Refresh page

---

### 4. Answer Submission Issues

#### ❌ Submit button disabled
**Cause:** No answer selected

**Solution:**
- Click on one of the options (A, B, C, or D)
- Option should highlight in blue
- Submit button will enable

---

#### ❌ Answer doesn't submit
**Causes:**
- Network error
- Session expired
- CSRF token invalid

**Solutions:**
```bash
# Check browser console (F12)
# Look for errors

# Clear cache
php artisan cache:clear
php artisan config:clear

# Refresh page and try again
```

---

### 5. Database Issues

#### ❌ "SQLSTATE[HY000] [1049] Unknown database"
**Cause:** Database doesn't exist

**Solution:**
```bash
# Create database
mysql -u root -p
CREATE DATABASE medq;
exit;

# Run migrations
php artisan migrate
php artisan db:seed
```

---

#### ❌ "SQLSTATE[HY000] [2002] Connection refused"
**Cause:** MySQL not running

**Solution:**
```bash
# Start XAMPP
# Start MySQL service
# Or from command line:
net start mysql
```

---

### 6. File Upload Issues

#### ❌ "The file failed to upload"
**Causes:**
- File too large
- Wrong file type
- Upload directory not writable

**Solutions:**
```bash
# Check PHP settings
php -i | grep upload_max_filesize
php -i | grep post_max_size

# Increase limits in php.ini
upload_max_filesize = 10M
post_max_size = 10M

# Check storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

---

#### ❌ Excel file not processing
**Cause:** ZipArchive not available or file corrupted

**Solutions:**
1. Convert Excel to CSV
2. Use CSV format instead
3. Check if file opens in Excel
4. Try re-saving file

---

### 7. Authentication Issues

#### ❌ "These credentials do not match our records"
**Causes:**
- Wrong email/password
- User doesn't exist
- Database not seeded

**Solutions:**
```bash
# Reset database and seed
php artisan migrate:fresh --seed

# Default credentials:
# Admin: admin@medq.com / password
# Student: john@example.com / password
```

---

#### ❌ Redirected to login after logging in
**Causes:**
- Session not working
- Middleware issue
- Role mismatch

**Solutions:**
```bash
# Clear sessions
php artisan cache:clear
php artisan config:clear

# Check .env
SESSION_DRIVER=file

# Restart server
php artisan serve
```

---

### 8. UI/Display Issues

#### ❌ Styles not loading
**Cause:** CSS file not found

**Solution:**
```bash
# Check if file exists
ls public/css/style.css

# Clear browser cache
# Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
```

---

#### ❌ Sidebar not showing
**Cause:** Component file missing

**Solution:**
```bash
# Check if files exist
ls resources/views/components/sidebar.blade.php
ls resources/views/components/quizzer_sidebar.blade.php
ls resources/views/components/topnav.blade.php
```

---

### 9. Performance Issues

#### ❌ Page loads slowly
**Solutions:**
```bash
# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear old cache first
php artisan cache:clear
```

---

#### ❌ Database queries slow
**Solutions:**
```bash
# Add indexes (already in migrations)
# Check query logs
php artisan migrate:status

# Optimize database
php artisan db:seed --class=DatabaseSeeder
```

---

## 🔧 Debug Commands

### Check Application Status
```bash
# Check PHP version
php -v

# Check Laravel version
php artisan --version

# Check database connection
php artisan migrate:status

# Check routes
php artisan route:list

# Check config
php artisan config:show
```

### Clear Everything
```bash
# Nuclear option - clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan clear-compiled
composer dump-autoload
```

### Reset Database
```bash
# Complete reset
php artisan migrate:fresh --seed

# Just reseed
php artisan db:seed --class=MedQSeeder
```

### Check Logs
```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# View last 50 lines
tail -n 50 storage/logs/laravel.log

# Clear logs
> storage/logs/laravel.log
```

---

## 🆘 Emergency Fixes

### Complete Reset
```bash
# 1. Stop server (Ctrl+C)

# 2. Clear everything
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Reset database
php artisan migrate:fresh --seed

# 4. Restart server
php artisan serve
```

### File Permissions (Linux/Mac)
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

### Reinstall Dependencies
```bash
# Remove vendor
rm -rf vendor

# Reinstall
composer install

# Regenerate autoload
composer dump-autoload
```

---

## 📞 Getting Help

### Before Asking for Help

1. **Check browser console** (F12 → Console tab)
2. **Check Laravel logs** (`storage/logs/laravel.log`)
3. **Try clearing cache** (see commands above)
4. **Try different browser**
5. **Check this troubleshooting guide**

### Information to Provide

When reporting issues, include:
- Error message (exact text)
- Browser console errors
- Laravel log errors
- Steps to reproduce
- What you expected vs what happened
- Browser and version
- PHP version (`php -v`)
- Laravel version (`php artisan --version`)

### Useful Debug Info
```bash
# Get system info
php -v
php artisan --version
php artisan migrate:status
php artisan route:list | grep quiz
```

---

## ✅ Health Check

Run this checklist to verify everything works:

```bash
# 1. Database
php artisan migrate:status
# Should show all migrations run

# 2. Seeder
php artisan db:seed --class=MedQSeeder
# Should complete without errors

# 3. Routes
php artisan route:list
# Should show all routes

# 4. Server
php artisan serve
# Should start on http://127.0.0.1:8000

# 5. Login
# Visit http://127.0.0.1:8000
# Login as admin@medq.com / password
# Should reach admin dashboard

# 6. Create Quiz
# Go to Quiz Management → Create
# Should load form

# 7. Student Login
# Login as john@example.com / password
# Should see dashboard with quizzes
```

---

## 🎯 Quick Fixes

| Problem | Quick Fix |
|---------|-----------|
| Can't login | `php artisan migrate:fresh --seed` |
| No quizzes showing | Check quiz is active & student assigned |
| Can't upload file | Try CSV instead of Excel |
| Questions not importing | Check file format (7 columns) |
| Can't click questions | Only white boxes are clickable |
| Submit button disabled | Select an answer first |
| Page not loading | Clear cache & restart server |
| Styles broken | Hard refresh browser (Ctrl+Shift+R) |
| Database error | Check MySQL is running |
| Session expired | Clear cache & re-login |

---

## 📚 Additional Resources

- **README.md** - Full documentation
- **QUICK_START.md** - 5-minute setup guide
- **TESTING_GUIDE.md** - Complete testing instructions
- **FLOW_SUMMARY.md** - System architecture
- **sample_quiz_template.csv** - Question format example

---

**Still stuck?** Check the Laravel logs at `storage/logs/laravel.log` for detailed error messages.
