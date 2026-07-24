<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'quiz_id', 'subject_id', 'question_id', 'selected_answer',
        'is_correct', 'score_awarded', 'submitted', 'locked',
        'started_at', 'expires_at', 'submitted_at', 'locked_at',
        'is_auto_expired',
    ];

    protected $casts = [
        'submitted'      => 'boolean',
        'locked'         => 'boolean',
        'is_correct'     => 'boolean',
        'is_auto_expired'=> 'boolean',
        'started_at'     => 'datetime',
        'expires_at'     => 'datetime',
        'submitted_at'   => 'datetime',
        'locked_at'      => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // Scope for user-specific locking
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope for locked attempts
    public function scopeLocked($query)
    {
        return $query->where('locked', true);
    }

    // Scope for submitted attempts
    public function scopeSubmitted($query)
    {
        return $query->where('submitted', true);
    }

    // Scope for open attempts: started but not yet locked
    public function scopeOpen($query)
    {
        return $query->whereNotNull('started_at')->where('locked', false);
    }

    // Scope for server-expired attempts: timer ran out but not yet locked
    public function scopeExpired($query)
    {
        return $query->where('locked', false)
                     ->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
    }

    // Check if attempt is expired
    public function isExpired()
    {
        return $this->expires_at && Carbon::now()->isAfter($this->expires_at);
    }

    // Lock this attempt
    public function lockAttempt()
    {
        $this->update([
            'locked' => true,
            'locked_at' => Carbon::now()
        ]);
    }

    // Submit this attempt
    public function submitAttempt()
    {
        $this->update([
            'submitted' => true,
            'submitted_at' => Carbon::now(),
            'locked' => true,
            'locked_at' => Carbon::now()
        ]);
    }
}