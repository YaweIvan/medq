# MedQ — Folder Structure & Architecture

## Tech Stack

- **Framework:** Laravel 11 (PHP)
- **Database:** MySQL (via XAMPP)
- **Frontend:** Blade templates + Tailwind CSS + Vite
- **Sessions:** Stored in the database
- **Queues/Cache:** Database-backed

---

## Full Folder Map

```
MedQ/
├── .env                          ← App config, DB creds, secret keys
├── artisan                       ← Laravel CLI entry point
├── composer.json                 ← PHP dependencies
├── package.json                  ← JS/CSS build dependencies
├── vite.config.js                ← Asset bundler config
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php               ← Base controller
│   │   │   ├── AuthController.php           ← Login, register, setup page
│   │   │   ├── AdminController.php          ← All admin functionality (~973 lines)
│   │   │   ├── QuizzerController.php        ← Student quiz-taking flow
│   │   │   ├── QuizzerApiController.php     ← Student-facing JSON APIs
│   │   │   └── StatisticsApiController.php  ← Stats/analytics JSON APIs
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php          ← Blocks non-admins from /admin/*
│   │       ├── QuizzerMiddleware.php        ← Blocks unapproved/non-students
│   │       └── PreventSessionRegeneration.php
│   ├── Models/
│   │   ├── User.php          ← Auth user (roles: admin / student)
│   │   ├── Quiz.php          ← A quiz (title, description, is_active)
│   │   ├── Subject.php       ← A subject inside a quiz (max_questions, marks_per_question)
│   │   ├── Question.php      ← MCQ question (options A–E, timer, subject link)
│   │   ├── QuizAttempt.php   ← One student's answer to one question
│   │   ├── QuizSound.php     ← Uploaded sound effects (correct, wrong, timer, etc.)
│   │   └── Setting.php       ← Key-value app-wide settings (DB-backed)
│   └── Providers/
│       └── AppServiceProvider.php
│
├── bootstrap/
│   ├── app.php               ← Registers middleware (admin, quizzer)
│   └── providers.php
│
├── config/                   ← Laravel config files (db, cache, mail, etc.)
│
├── database/
│   ├── migrations/           ← 23 migration files (full schema history)
│   └── seeders/
│       └── DatabaseSeeder.php
│
├── public/
│   ├── index.php             ← Web entry point
│   ├── sounds/               ← Uploaded audio files served directly
│   ├── css/ images/          ← Static assets
│   └── storage → ../storage/app/public  (symlink)
│
├── resources/
│   ├── css/ js/              ← Source assets (compiled by Vite)
│   └── views/
│       ├── splash.blade.php          ← Landing page
│       ├── welcome.blade.php
│       ├── layouts/                  ← Shared page layouts
│       ├── components/               ← Reusable Blade components
│       ├── auth/
│       │   ├── login.blade.php
│       │   ├── register.blade.php
│       │   ├── forgot.blade.php
│       │   └── setup.blade.php       ← Hidden admin reset page (/setup)
│       ├── admin/
│       │   ├── dashboard.blade.php
│       │   ├── approvals.blade.php
│       │   ├── statistics.blade.php
│       │   ├── leaderboard.blade.php
│       │   ├── quiz_leaderboard.blade.php
│       │   ├── quiz_analysis.blade.php
│       │   ├── subject_analysis.blade.php
│       │   ├── student_quiz_details.blade.php
│       │   ├── student_subject_review.blade.php
│       │   ├── sounds.blade.php
│       │   ├── settings.blade.php
│       │   ├── tutorial.blade.php
│       │   └── quizzes/
│       │       ├── index.blade.php
│       │       ├── create.blade.php
│       │       ├── edit.blade.php
│       │       └── select_edit.blade.php
│       └── quizzer/
│           ├── dashboard.blade.php
│           ├── quiz_grid.blade.php
│           ├── quiz_subjects.blade.php
│           ├── questions.blade.php      ← Question list/grid for a subject
│           ├── question.blade.php       ← Single question answering screen
│           ├── statistics.blade.php
│           ├── quiz_review.blade.php
│           ├── subject_review.blade.php
│           └── tutorial.blade.php
│
├── routes/
│   └── web.php               ← All routes (public, admin, quizzer, APIs)
│
└── storage/
    ├── app/                  ← File uploads
    ├── framework/            ← Cache, sessions, views
    └── logs/                 ← Laravel logs
```

---

## Database Schema (Entity Relationships)

```
users
  id, name, email, password, role (admin|student),
  is_approved, student_id

quiz_user  (pivot)
  quiz_id → quizzes.id
  user_id → users.id

quizzes
  id, title, description, is_active

subjects
  id, name, max_questions, marks_per_question

questions
  id, quiz_id → quizzes.id
  subject_id → subjects.id
  question, option_a, option_b, option_c, option_d, option_e
  correct_answer, time_per_question, is_used

quiz_attempts
  id, user_id → users.id
  quiz_id → quizzes.id
  question_id → questions.id
  selected_answer (nullable), is_correct

quiz_sounds
  id, sound_type (correct|wrong|timer|warning|finish)
  file_name, file_path, mime_type, file_size, uploaded_by

settings
  key (PK, string), value
```

---

## How the App Works — End-to-End Flow

### 1. Two User Roles

| Role      | Access                           | Entry Path                                                 |
| --------- | -------------------------------- | ---------------------------------------------------------- |
| `admin`   | Full control panel at `/admin/*` | Gated by `AdminMiddleware`                                 |
| `student` | Quiz-taking at `/quizzer/*`      | Gated by `QuizzerMiddleware` (also enforces `is_approved`) |

---

### 2. Registration & Approval Flow

1. Student visits `/register` → creates account (`role = student`, `is_approved = false`)
2. Admin sees pending users at `/admin/approvals` → approves or rejects
3. Once approved, student can log in and access their assigned quizzes

---

### 3. Admin Workflow

- **Create a quiz** → assign subjects to it → bulk-upload questions via CSV
- **Assign students** to a quiz via the quiz edit page
- **Toggle quiz active/inactive** — students only see active quizzes
- **Monitor results:** quiz analysis per subject, per student, leaderboard/rankings, exportable to Excel
- **Manage sounds:** upload audio files per event type (correct answer, wrong, timer warning, finish)
- **Settings page:** app-wide key/value configuration stored in the `settings` table

---

### 4. Student (Quizzer) Workflow

1. **Dashboard** → sees all assigned active quizzes
2. Clicks a quiz → sees **subjects** that belong to it
3. Picks a subject → sees a **question grid** (colour-coded: answered / skipped / unanswered)
4. Answers each MCQ (up to 5 options A–E); each question can have its own countdown timer
5. Answers are saved per-question to `quiz_attempts` immediately on submit
6. After finishing → **review mode** shows correct/wrong answers per subject
7. **Statistics page** shows personal performance charts and per-quiz rankings

---

### 5. API Layer

- `StatisticsApiController` serves JSON data consumed by Chart.js on admin and student pages:
    - Quiz stats, participation rates, leaderboard data, subject performance
- `QuizzerApiController` provides a `check-updates` ping so the student UI can detect if a quiz has been changed or reset without requiring a full page reload

---

### 6. Special Routes

| Route              | Purpose                                                                                                                |
| ------------------ | ---------------------------------------------------------------------------------------------------------------------- |
| `/setup`           | Hidden emergency page protected by `SETUP_SECRET_KEY` in `.env` — resets or creates an admin account without DB access |
| `/clear-all-cache` | Utility route for cache-busting on shared/restricted hosting                                                           |

---

### 7. Sounds System

Admin uploads `.mp3`/`.wav` files tagged to event types. The student quiz page (`question.blade.php`) plays the matching sound at the right moment:

| Event                    | Sound Type          |
| ------------------------ | ------------------- |
| Correct answer submitted | `correct`           |
| Wrong answer submitted   | `wrong`             |
| Timer running low        | `timer` / `warning` |
| Quiz completed           | `finish`            |

---

## Summary

MedQ is a **Laravel-based medical MCQ examination platform** where an admin creates quizzes with subjects and questions, approves student registrations, and monitors detailed analytics — while students complete timed, sound-augmented quizzes and review their performance with full per-subject and per-question breakdowns.
