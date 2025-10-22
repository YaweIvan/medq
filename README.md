# MedQ - Medical Quiz Platform

## Overview
MedQ is a comprehensive medical quiz platform built with Laravel that allows administrators to create structured quizzes and manage quizzers (students) taking medical knowledge assessments.

## Features

### Admin Features
- User approval management
- Quiz creation with Excel upload
- Subject-based question organization
- Quiz randomization
- Statistics and leaderboards
- User assignment to quizzes

### Quizzer Features
- Registration and approval workflow
- Subject-based quiz taking (max 5 questions per subject)
- Instant feedback (correct/incorrect)
- Personal statistics tracking
- Question locking mechanism

## System Requirements
- PHP 8.1+
- MySQL 5.7+
- Composer
- XAMPP/WAMP (for local development)

## Installation

1. **Clone/Download the project**
   ```bash
   cd c:\xampp\htdocs\MedQ
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Database Setup**
   - Create a MySQL database named `medq`
   - Update `.env` file with your database credentials

4. **Run migrations and seed data**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start the application**
   ```bash
   php artisan serve
   ```

## Default Login Credentials

### Admin
- Email: `admin@medq.com`
- Password: `password`

### Test Quizzer
- Email: `john@example.com`
- Password: `password`

## Usage Flow

### For Admins
1. Login → Admin Dashboard
2. Approve pending user registrations
3. Create quizzes with Excel uploads
4. Assign users to quizzes
5. Run randomizer for question/user order
6. Monitor statistics and leaderboards

### For Quizzers
1. Register → Wait for approval
2. Login → View assigned quizzes
3. Select subject → Answer questions (max 5 per subject)
4. Receive instant feedback
5. View personal statistics

## Excel/CSV Upload Format
For question uploads, use this column structure:
- Column A: Number
- Column B: Question
- Column C: Option A
- Column D: Option B
- Column E: Option C
- Column F: Option D
- Column G: Correct Answer (A, B, C, or D)

**Download Sample Template:**
- Navigate to Quiz Creation page and click "Download Sample Template"
- Or use the file: `public/sample_quiz_template.csv`

**Supported Formats:**
- Excel (.xlsx, .xls)
- CSV (.csv)

## Key Features Implemented
- ✅ Role-based authentication (Admin/Quizzer)
- ✅ User approval system
- ✅ Quiz creation and management
- ✅ Subject-based question organization
- ✅ Question attempt limiting (5 per subject)
- ✅ Question locking mechanism
- ✅ Instant feedback system
- ✅ Statistics tracking
- ✅ Responsive design

## Technology Stack
- **Backend**: Laravel 11
- **Frontend**: Bootstrap 5, Vanilla JavaScript
- **Database**: MySQL
- **Icons**: Font Awesome
- **Styling**: Custom CSS with modern design

## License
MIT License
