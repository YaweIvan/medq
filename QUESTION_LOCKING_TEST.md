# Question Locking Test - MedQ

## ✅ FIXED: Question Locking Logic

### How It Works Now:
1. **White Questions**: Available to everyone
2. **Blue Questions**: Questions I have attempted 
3. **Gray Questions**: Questions used/closed by ANYONE (locked for everyone)

### Key Behavior:
- When ANY user attempts a question (manual submit OR timer expiry), the question becomes **gray** for ALL users
- Gray questions cannot be opened by anyone else
- Each question can only be attempted ONCE by ONE person
- Questions are "first come, first served"

## 🎯 Test Scenarios

### Test 1: Question Locking (Multi-User)
**Setup**: 
- User A: john@example.com / password  
- User B: Login as different student

**Steps**:
1. Both users go to same quiz → same subject → same grid
2. User A clicks question #1 → Question opens, timer starts
3. User B refreshes grid → Question #1 should still be WHITE (available)
4. User A completes question (submit OR timer expires) 
5. User B refreshes grid → Question #1 should now be GRAY (locked)
6. User B tries to click gray question #1 → Should not be clickable

### Test 2: Timer Expiry Locking
**Steps**:
1. User A opens question → Timer starts
2. User A waits for timer to expire (no answer selected)
3. Question auto-submits and shows correct answer
4. Return to grid → Question should be GRAY
5. Other users see question as GRAY (locked)

### Test 3: Manual Submit Locking
**Steps**:
1. User A opens question → Timer starts
2. User A selects answer and clicks "Submit"
3. Shows correct/incorrect feedback
4. Return to grid → Question should be GRAY
5. Other users see question as GRAY (locked)

### Test 4: Concurrent Access Prevention
**Steps**:
1. User A opens question → Timer starts
2. User B tries to access same question URL directly
3. User B should be redirected to grid with error message
4. Question remains available to User A until they submit/timeout

## 🎨 Visual Color Guide

### Grid Colors:
- ⬜ **White**: Available (anyone can attempt)
- 🔵 **Blue**: My previous attempts  
- 🔘 **Gray**: Used/locked (attempted by someone, closed to all)

### Legend Shows:
- "Available" (white)
- "My Attempts" (blue) 
- "Used/Closed" (gray)

## 🔧 Technical Implementation

### Database Logic:
- `is_used = false`: Question available
- `is_used = true`: Question locked (after ANY user attempts it)
- `quiz_attempts` table: Records who attempted what

### Controller Checks:
1. **showQuestion()**: Blocks access to `is_used = true` questions
2. **submitAnswer()**: Sets `is_used = true` after attempt
3. **questionGrid()**: Shows proper colors based on status

### Frontend Logic:
- Gray questions have `cursor: not-allowed` 
- Gray questions have no onclick handler
- Proper visual feedback for all states

## ✅ Expected Results

### Single User Flow:
1. See white questions in grid
2. Click white question → Opens with timer
3. Submit/timeout → Return to grid
4. Question now shows as BLUE (my attempt)
5. Question also shows as GRAY for other users

### Multi-User Flow:
1. User A attempts question → Question locks
2. User B sees question turn gray (unavailable)
3. User B must choose different white question
4. Each question can only be attempted once total

**🎉 Questions now properly lock after first attempt and show gray for all users!**